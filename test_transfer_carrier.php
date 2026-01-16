<?php
/**
 * 测试转单功能 - 承运商更新
 * 用于调试转单时承运商名称无法更新的问题
 */

// 引入框架
require __DIR__ . '/source/think.php';

use app\store\model\Inpack;
use app\common\model\Express;
use app\store\model\Ditch as DitchModel;

echo "=== 转单功能测试 ===\n\n";

// 测试数据
$test_inpack_id = 69407; // 替换为实际的集运单ID

echo "1. 查询集运单信息\n";
$inpack = Inpack::get($test_inpack_id);
if (!$inpack) {
    die("错误：找不到集运单 ID: $test_inpack_id\n");
}

echo "集运单号: {$inpack['order_sn']}\n";
echo "当前状态: {$inpack['status']}\n";
echo "当前承运商: {$inpack['t_name']} ({$inpack['t_number']})\n";
echo "当前单号: {$inpack['t_order_sn']}\n";
echo "转单承运商: {$inpack['t2_name']} ({$inpack['t2_number']})\n";
echo "转单单号: {$inpack['t2_order_sn']}\n\n";

echo "2. 测试承运商查询\n";

// 测试外部承运商查询
echo "\n测试外部承运商 (DHL):\n";
$express = (new Express())->where('express_code', 'dhl')->find();
if ($express) {
    echo "  ✓ 找到承运商: {$express['express_name']} (代码: {$express['express_code']})\n";
} else {
    echo "  ✗ 未找到承运商代码: dhl\n";
}

// 列出所有可用的承运商
echo "\n可用的外部承运商列表:\n";
$expressList = (new Express())->where('type', '<>', 1)->limit(10)->select();
foreach ($expressList as $exp) {
    echo "  - {$exp['express_name']} (代码: {$exp['express_code']})\n";
}

// 测试自有物流查询
echo "\n测试自有物流:\n";
$ditchList = DitchModel::getAll();
if ($ditchList && count($ditchList) > 0) {
    echo "  ✓ 找到 " . count($ditchList) . " 个自有物流渠道\n";
    foreach ($ditchList as $ditch) {
        echo "  - {$ditch['ditch_name']} (ID: {$ditch['ditch_id']})\n";
    }
} else {
    echo "  ✗ 未找到自有物流渠道\n";
}

echo "\n3. 模拟转单请求数据\n";

// 模拟外部承运商转单
$test_data_external = [
    'id' => $test_inpack_id,
    'type' => 'change',
    'transfer' => 1,
    'tt_number' => 'dhl',  // 外部承运商代码
    't_order_sn' => 'TEST123456789'
];

echo "外部承运商转单数据:\n";
print_r($test_data_external);

// 模拟自有物流转单
if ($ditchList && count($ditchList) > 0) {
    $first_ditch = $ditchList[0];
    $test_data_internal = [
        'id' => $test_inpack_id,
        'type' => 'change',
        'transfer' => 0,
        't_number' => $first_ditch['ditch_id'],  // 自有物流ID
        't_order_sn' => 'TEST987654321'
    ];
    
    echo "\n自有物流转单数据:\n";
    print_r($test_data_internal);
}

echo "\n4. 检查转单逻辑中的变量\n";

// 模拟转单逻辑
$data = $test_data_external;
$update = [];
$update['updated_time'] = time();
$update['status'] = '6';

echo "初始 \$update 数组:\n";
print_r($update);

if($data['type']=='change'){
    echo "\n进入转单模式分支\n";
    
    // 转单模式：需要查询承运商名称
    $carrier_name = '';
    $carrier_number = '';
    
    if($data['transfer']==1){
        echo "  选择: 外部承运商\n";
        // 外部承运商
        $express = (new Express())->where('express_code',$data['tt_number'])->find();
        if($express){
            $carrier_name = $express['express_name'];
            $carrier_number = $data['tt_number'];
            echo "  ✓ 查询成功: $carrier_name ($carrier_number)\n";
        } else {
            echo "  ✗ 查询失败: 未找到承运商代码 {$data['tt_number']}\n";
        }
    }else{
        echo "  选择: 自有物流\n";
        // 自有物流
        $ditchdetail = DitchModel::detail($data['t_number']);
        if($ditchdetail){
            $carrier_name = $ditchdetail['ditch_name'];
            $carrier_number = $ditchdetail['ditch_id'];
            echo "  ✓ 查询成功: $carrier_name ($carrier_number)\n";
        } else {
            echo "  ✗ 查询失败: 未找到自有物流 ID {$data['t_number']}\n";
        }
    }
    
    $upd['t2_number'] = $carrier_number;
    $upd['t2_name'] = $carrier_name;
    $upd['t2_order_sn'] = $data['t_order_sn'];
    $upd['updated_time'] = $update['updated_time'];
    $upd['status'] = $update['status'];
    
    echo "\n最终更新数组 \$upd:\n";
    print_r($upd);
    
    // 检查是否有空值
    $has_empty = false;
    foreach ($upd as $key => $value) {
        if (empty($value) && $value !== 0) {
            echo "  ⚠ 警告: 字段 '$key' 为空\n";
            $has_empty = true;
        }
    }
    
    if (!$has_empty) {
        echo "  ✓ 所有字段都有值\n";
    }
}

echo "\n=== 测试完成 ===\n";
echo "\n如果要实际执行更新，请取消下面代码的注释：\n";
echo "// \$model = new Inpack();\n";
echo "// \$result = \$model->modify(\$test_data_external);\n";
echo "// echo \"更新结果: \" . (\$result ? '成功' : '失败') . \"\\n\";\n";
