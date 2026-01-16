<?php
/**
 * 测试仓管录入包裹时的LINE通知
 * 模拟后台录入包裹入库的流程
 */

// 引入ThinkPHP框架
define('APP_PATH', __DIR__ . '/source/application/');
require __DIR__ . '/source/thinkphp/start.php';

use app\common\model\Package;
use app\common\model\User;

echo "==================== 测试仓管录入包裹LINE通知 ====================\n\n";

// 测试用户ID
$userId = 31966;

echo "【1】获取用户信息\n";
$user = User::where(['user_id' => $userId])->find();

if (!$user) {
    die("❌ 用户不存在\n");
}

echo "用户昵称：{$user['nickName']}\n";
echo "wxapp_id：{$user['wxapp_id']}\n";
echo "line_openid：" . ($user['line_openid'] ?: '未设置') . "\n\n";

if (empty($user['line_openid'])) {
    die("❌ 用户未绑定LINE账号\n");
}

echo "【2】获取用户最新包裹\n";
$package = Package::where(['member_id' => $userId])
    ->order('id DESC')
    ->find();

if (!$package) {
    die("❌ 用户没有包裹记录\n");
}

echo "包裹ID：{$package['id']}\n";
echo "快递单号：{$package['express_num']}\n";
echo "状态：{$package['status']}\n";
echo "入库时间：" . ($package['entering_warehouse_time'] ?: '未入库') . "\n\n";

echo "【3】模拟发送入库通知\n";
echo "准备发送通知数据...\n";

// 构建通知数据（模拟Useropration控制器中的数据结构）
$notificationData = [
    'wxapp_id' => $user['wxapp_id'],
    'member_id' => $userId,
    'id' => $package['id'],
    'shop_name' => '泰国仓库',
    'express_num' => $package['express_num'],
    'entering_warehouse_time' => $package['entering_warehouse_time'] ?: date('Y-m-d H:i:s'),
    'weight' => $package['weight'] ?: 0,
    'remark' => '包裹已入库，可提交打包',
];

// 如果包裹有图片，添加图片数据
$packageImages = $package->packageimage;
if ($packageImages && count($packageImages) > 0) {
    $notificationData['packageimage'] = $packageImages->toArray();
    echo "包裹图片数量：" . count($packageImages) . "\n";
}

echo "\n发送通知...\n";

try {
    // 调用Package模型的sendEnterMessage方法（这是实际代码中调用的方法）
    $packageModel = new Package();
    $result = $packageModel->sendEnterMessage([$notificationData]);
    
    if ($result) {
        echo "✅ 通知发送成功！\n\n";
        
        echo "【4】验证结果\n";
        echo "请检查：\n";
        echo "1. 用户LINE是否收到入库通知\n";
        echo "2. 日志文件是否有记录：runtime/log/" . date('Ym') . "/" . date('d') . ".log\n";
        echo "3. 如果没有收到，检查日志中的错误信息\n";
    } else {
        echo "❌ 通知发送失败\n";
    }
    
} catch (\Exception $e) {
    echo "❌ 发送异常：" . $e->getMessage() . "\n";
    echo "堆栈跟踪：\n" . $e->getTraceAsString() . "\n";
}

echo "\n==================== 测试完成 ====================\n";
