<?php
/**
 * 带详细调试的发送测试
 */

// 引入ThinkPHP框架
define('APP_PATH', __DIR__ . '/source/application/');
require __DIR__ . '/source/thinkphp/start.php';

use app\common\model\Package;

echo "==================== 详细调试测试 ====================\n\n";

$userId = 31966;
$code = '31966asdsadas';

// 查询包裹
$data = Package::alias('a')
    ->field('a.id,a.storage_id,a.wxapp_id,a.order_sn,u.nickName,a.member_id,s.shop_name,a.status,a.entering_warehouse_time,a.express_num,a.weight,a.remark')
    ->join('user u', 'a.member_id = u.user_id', "LEFT")
    ->join('store_shop s', 'a.storage_id = s.shop_id', "LEFT")
    ->where(['express_num' => $code])
    ->where('a.is_delete', 0)
    ->find();

if (!$data) {
    die("❌ 包裹不存在\n");
}

echo "包裹信息：\n";
echo "- ID: {$data['id']}\n";
echo "- 用户ID: {$data['member_id']}\n";
echo "- wxapp_id: {$data['wxapp_id']}\n";
echo "- 仓库: {$data['shop_name']}\n\n";

// 准备数据
$data['entering_warehouse_time'] = date('Y-m-d H:i:s');
$dataArray = $data->toArray();

echo "【测试1】直接调用Inwarehouse服务\n";
try {
    $lineService = new \app\common\service\message\line\Inwarehouse();
    echo "服务实例创建成功\n";
    
    $result = $lineService->send($dataArray);
    echo "send()返回: " . ($result ? 'TRUE' : 'FALSE') . "\n";
    
    if (!$result) {
        echo "❌ 发送失败\n";
    } else {
        echo "✅ 发送成功\n";
    }
} catch (\Exception $e) {
    echo "❌ 异常: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "堆栈:\n" . $e->getTraceAsString() . "\n";
}

echo "\n【测试2】通过Package模型调用\n";
try {
    $packageModel = new Package();
    $result = $packageModel->sendEnterMessage([$dataArray]);
    echo "sendEnterMessage()返回: " . ($result ? 'TRUE' : 'FALSE') . "\n";
} catch (\Exception $e) {
    echo "❌ 异常: " . $e->getMessage() . "\n";
}

echo "\n==================== 完成 ====================\n";
