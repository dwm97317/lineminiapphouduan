<?php
/**
 * 完整测试 - 包含图片、尺寸、唛头
 */

define('APP_PATH', __DIR__ . '/source/application/');
require __DIR__ . '/source/thinkphp/start.php';

use app\common\model\Package;

echo "==================== 完整通知测试 ====================\n\n";

$code = '31966asdsadas';

// 查询包裹（包含图片关联）
$data = Package::alias('a')
    ->field('a.*,u.nickName,s.shop_name')
    ->join('user u', 'a.member_id = u.user_id', "LEFT")
    ->join('store_shop s', 'a.storage_id = s.shop_id', "LEFT")
    ->with(['packageimage' => function($query) {
        $query->with('file');
    }])
    ->where(['express_num' => $code])
    ->where('a.is_delete', 0)
    ->find();

if (!$data) {
    die("❌ 包裹不存在\n");
}

echo "【1】包裹基本信息\n";
echo "- ID: {$data['id']}\n";
echo "- 用户ID: {$data['member_id']}\n";
echo "- 仓库: {$data['shop_name']}\n";
echo "- 快递单号: {$data['express_num']}\n";
echo "- 重量: {$data['weight']}kg\n";
echo "- 长: {$data['length']}, 宽: {$data['width']}, 高: {$data['height']}\n";
$sizeStr = '';
if ($data['length'] > 0 && $data['width'] > 0 && $data['height'] > 0) {
    $sizeStr = $data['length'] . 'x' . $data['width'] . 'x' . $data['height'] . 'cm';
}
echo "- 尺寸: " . ($sizeStr ?: '未测量') . "\n";
echo "- 唛头: " . ($data['usermark'] ?: '无') . "\n";

echo "\n【2】图片信息\n";
if (!empty($data['packageimage'])) {
    echo "图片数量: " . count($data['packageimage']) . "\n";
    foreach ($data['packageimage'] as $idx => $img) {
        echo "图片" . ($idx + 1) . ": ";
        if (isset($img['file']['file_path'])) {
            echo $img['file']['file_path'] . "\n";
        } else {
            echo "无路径\n";
        }
    }
} else {
    echo "无图片\n";
}

echo "\n【3】发送LINE通知\n";

// 准备数据
$data['entering_warehouse_time'] = date('Y-m-d H:i:s');
$dataArray = $data->toArray();

// 通过Package模型调用（会自动加载图片）
try {
    $packageModel = new Package();
    $result = $packageModel->sendEnterMessage([$dataArray]);
    
    if ($result) {
        echo "✅ 发送成功\n";
        echo "\n请检查LINE消息是否包含:\n";
        echo "- ✓ 尺寸信息\n";
        echo "- ✓ 唛头信息（如果有）\n";
        echo "- ✓ 包裹图片（如果有）\n";
    } else {
        echo "❌ 发送失败\n";
    }
} catch (\Exception $e) {
    echo "❌ 异常: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n==================== 完成 ====================\n";
