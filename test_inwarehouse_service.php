<?php
/**
 * 测试Inwarehouse服务类
 */

// 引入ThinkPHP框架
define('APP_PATH', __DIR__ . '/source/application/');
require __DIR__ . '/source/thinkphp/start.php';

use app\common\service\message\line\Inwarehouse;
use app\common\model\User;
use app\common\model\Package;

echo "==================== 测试Inwarehouse服务 ====================\n\n";

$userId = 31966;

// 获取用户
$user = User::where(['user_id' => $userId])->find();
echo "用户ID：{$userId}\n";
echo "LINE ID：{$user['line_openid']}\n";
echo "wxapp_id：{$user['wxapp_id']}\n\n";

// 获取包裹
$package = Package::where(['member_id' => $userId])
    ->with('packageimage')
    ->order('id DESC')
    ->find();

echo "包裹ID：{$package['id']}\n";
echo "快递单号：{$package['express_num']}\n\n";

// 准备数据
$data = [
    'wxapp_id' => $user['wxapp_id'],
    'member_id' => $userId,
    'id' => $package['id'],
    'shop_name' => '泰国仓库',
    'express_num' => $package['express_num'],
    'entering_warehouse_time' => $package['entering_warehouse_time'] ?: date('Y-m-d H:i:s'),
    'weight' => $package['weight'] ?: 1.5,
    'remark' => '【服务测试】包裹已入库',
];

if ($package->packageimage && count($package->packageimage) > 0) {
    $data['packageimage'] = $package->packageimage->toArray();
}

echo "数据准备完成\n";
echo "wxapp_id: {$data['wxapp_id']}\n";
echo "member_id: {$data['member_id']}\n";
echo "id: {$data['id']}\n\n";

// 创建服务实例并启用调试
echo "创建Inwarehouse服务实例...\n";
$service = new Inwarehouse();

echo "调用send方法...\n";
try {
    $result = $service->send($data);
    
    echo "\n返回结果：" . ($result ? '✅ true' : '❌ false') . "\n";
    
    if ($result) {
        echo "\n✅ 通知发送成功！请检查LINE\n";
    } else {
        echo "\n❌ 通知发送失败\n";
        echo "可能的原因：\n";
        echo "1. getLineUserIdByUserId 返回空\n";
        echo "2. sendLineFlexMsg 返回false\n";
        echo "3. 检查日志文件查看详细错误\n";
    }
} catch (\Exception $e) {
    echo "\n❌ 异常：" . $e->getMessage() . "\n";
    echo "文件：" . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\n堆栈：\n" . $e->getTraceAsString() . "\n";
}

echo "\n==================== 测试完成 ====================\n";
