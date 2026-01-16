<?php
/**
 * 测试后台扫码入库流程
 * 模拟 store/controller/package/Index.php 的 scanResult 方法
 */

// 引入ThinkPHP框架
define('APP_PATH', __DIR__ . '/source/application/');
require __DIR__ . '/source/thinkphp/start.php';

use app\common\model\Package;

echo "==================== 测试后台扫码入库 ====================\n\n";

$userId = 31966;
$code = '31966asdsadas'; // 测试用的快递单号

echo "【1】查询包裹（模拟扫码）\n";
echo "快递单号：{$code}\n\n";

// 模拟后台的查询
$data = Package::alias('a')
    ->field('a.id,a.storage_id,a.is_scan,a.wxapp_id,a.order_sn,u.nickName,a.member_id,s.shop_name,a.status as a_status,a.entering_warehouse_time,a.pack_attr,a.goods_attr,a.pack_free,a.source,a.is_take,a.free,a.express_num,a.express_name, a.length, a.width, a.height, a.weight,a.price,a.real_payment,a.remark,c.title')
    ->join('user u', 'a.member_id = u.user_id', "LEFT")
    ->join('countries c', 'a.country_id = c.id', "LEFT")
    ->join('store_shop s', 'a.storage_id = s.shop_id', "LEFT")
    ->where(['express_num' => $code])
    ->where('a.is_delete', 0)
    ->find();

if (!$data) {
    die("❌ 包裹不存在\n");
}

echo "✅ 包裹找到\n";
echo "包裹ID：{$data['id']}\n";
echo "用户ID：{$data['member_id']}\n";
echo "用户昵称：{$data['nickName']}\n";
echo "仓库名称：" . ($data['shop_name'] ?: '未设置') . "\n";
echo "wxapp_id：{$data['wxapp_id']}\n";
echo "当前状态：{$data['a_status']}\n\n";

echo "【2】准备入库数据\n";

// 模拟更新
$update['status'] = 2;
$update['entering_warehouse_time'] = date('Y-m-d H:i:s');

echo "设置入库时间：{$update['entering_warehouse_time']}\n\n";

// 将更新的字段合并到data中（模拟后台的操作）
$data['entering_warehouse_time'] = $update['entering_warehouse_time'];
$data['status'] = $update['status'];

echo "【3】转换为数组并检查字段\n";
$dataArray = $data->toArray();

echo "数组键：" . implode(', ', array_keys($dataArray)) . "\n\n";

echo "关键字段检查：\n";
echo "- wxapp_id: " . ($dataArray['wxapp_id'] ?? '❌ 缺失') . "\n";
echo "- member_id: " . ($dataArray['member_id'] ?? '❌ 缺失') . "\n";
echo "- id: " . ($dataArray['id'] ?? '❌ 缺失') . "\n";
echo "- shop_name: " . ($dataArray['shop_name'] ?? '❌ 缺失') . "\n";
echo "- express_num: " . ($dataArray['express_num'] ?? '❌ 缺失') . "\n";
echo "- entering_warehouse_time: " . ($dataArray['entering_warehouse_time'] ?? '❌ 缺失') . "\n";
echo "- weight: " . ($dataArray['weight'] ?? '❌ 缺失') . "\n";
echo "- remark: " . ($dataArray['remark'] ?? '❌ 缺失') . "\n\n";

echo "【4】调用sendEnterMessage\n";

try {
    $packageModel = new Package();
    $result = $packageModel->sendEnterMessage([$dataArray]);
    
    if ($result) {
        echo "✅ sendEnterMessage返回true\n";
        echo "\n请检查用户LINE是否收到通知\n";
    } else {
        echo "❌ sendEnterMessage返回false\n";
    }
} catch (\Exception $e) {
    echo "❌ 异常：" . $e->getMessage() . "\n";
    echo "文件：" . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n==================== 测试完成 ====================\n";
