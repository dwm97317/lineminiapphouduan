<?php
/**
 * 简单测试：验证转单功能的字段传递
 */

echo "=== 转单功能字段传递测试 ===\n\n";

// 模拟表单提交的数据
$delivery = [
    'id' => 69407,
    'type' => 'change',
    'transfer' => 1,
    'tt_number' => 'dhl',
    't_order_sn' => 'TEST123456789'
];

echo "1. 表单提交数据:\n";
print_r($delivery);

// 模拟原来的字段白名单（有问题的版本）
$field_old = ['line_id','length','width','height','weight','verify','free','pack_free','cale_weight','volume','other_free','remark','t_number','t_name','t_order_sn'];

echo "\n2. 原字段白名单:\n";
print_r($field_old);

$update_old = [];
foreach ($field_old as $v){
    if (isset($delivery[$v]))
       $update_old[$v] = $delivery[$v];
}

echo "\n3. 原白名单过滤后的数据:\n";
print_r($update_old);
echo "   ⚠️  问题: tt_number 和 transfer 字段丢失!\n";

// 模拟新的字段白名单（修复后的版本）
$field_new = ['line_id','length','width','height','weight','verify','free','pack_free','cale_weight','volume','other_free','remark','t_number','t_name','t_order_sn','tt_number','transfer'];

echo "\n4. 新字段白名单:\n";
print_r($field_new);

$update_new = [];
foreach ($field_new as $v){
    if (isset($delivery[$v]))
       $update_new[$v] = $delivery[$v];
}

echo "\n5. 新白名单过滤后的数据:\n";
print_r($update_new);
echo "   ✓ 修复: tt_number 和 transfer 字段保留!\n";

// 验证转单逻辑能否获取到必要的字段
echo "\n6. 验证转单逻辑:\n";
if(isset($update_new['transfer']) && $update_new['transfer']==1){
    echo "   ✓ transfer 字段存在，值为: {$update_new['transfer']}\n";
    if(isset($update_new['tt_number'])){
        echo "   ✓ tt_number 字段存在，值为: {$update_new['tt_number']}\n";
        echo "   ✓ 可以查询承运商: SELECT * FROM yoshop_express WHERE express_code = '{$update_new['tt_number']}'\n";
    } else {
        echo "   ✗ tt_number 字段缺失，无法查询承运商!\n";
    }
} else {
    echo "   ✗ transfer 字段缺失或值不正确!\n";
}

echo "\n=== 测试完成 ===\n";
echo "\n结论:\n";
echo "- 原代码: tt_number 和 transfer 字段被白名单过滤掉，导致转单失败\n";
echo "- 修复后: 字段正常传递，转单功能应该可以正常工作\n";
