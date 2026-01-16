<?php
/**
 * 直接发送测试通知给用户31966
 */

// 引入ThinkPHP框架
define('APP_PATH', __DIR__ . '/source/application/');
require __DIR__ . '/source/thinkphp/start.php';

use app\common\service\message\line\Inwarehouse;
use app\common\model\User;
use app\common\model\Package;

echo "==================== 发送测试通知给用户31966 ====================\n\n";

$userId = 31966;

// 获取用户信息
$user = User::where(['user_id' => $userId])->find();
if (!$user) {
    die("❌ 用户不存在\n");
}

echo "用户：{$user['nickName']}\n";
echo "LINE ID：{$user['line_openid']}\n";
echo "wxapp_id：{$user['wxapp_id']}\n\n";

// 获取最新包裹
$package = Package::where(['member_id' => $userId])
    ->with('packageimage')
    ->order('id DESC')
    ->find();

if (!$package) {
    die("❌ 没有包裹记录\n");
}

echo "包裹ID：{$package['id']}\n";
echo "快递单号：{$package['express_num']}\n\n";

// 准备通知数据
$data = [
    'wxapp_id' => $user['wxapp_id'],
    'member_id' => $userId,
    'id' => $package['id'],
    'shop_name' => '泰国仓库',
    'express_num' => $package['express_num'],
    'entering_warehouse_time' => $package['entering_warehouse_time'] ?: date('Y-m-d H:i:s'),
    'weight' => $package['weight'] ?: 1.5,
    'remark' => '【测试通知】包裹已入库，可提交打包',
];

// 添加图片
if ($package->packageimage && count($package->packageimage) > 0) {
    $data['packageimage'] = $package->packageimage->toArray();
    echo "包裹图片：" . count($package->packageimage) . " 张\n\n";
}

echo "发送通知...\n";

try {
    $service = new Inwarehouse();
    $result = $service->send($data);
    
    if ($result) {
        echo "✅ 通知发送成功！\n";
        echo "\n请检查用户LINE是否收到消息\n";
    } else {
        echo "❌ 通知发送失败（返回false）\n";
        echo "可能原因：\n";
        echo "1. 用户未添加LINE OA为好友\n";
        echo "2. LINE配置未启用\n";
        echo "3. 入库模板未启用\n";
    }
} catch (\Exception $e) {
    echo "❌ 发送异常：" . $e->getMessage() . "\n";
    echo "\n堆栈跟踪：\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n==================== 完成 ====================\n";
