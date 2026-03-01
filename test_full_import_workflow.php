<?php
/**
 * 完整导入流程测试 - 模拟Web界面的完整操作
 */

// 定义应用目录
define('APP_PATH', __DIR__ . '/source/application/');

// 加载框架引导文件
require __DIR__ . '/source/thinkphp/start.php';

use app\store\service\payment\PaymentImportService;

echo "=== 完整导入流程测试 ===\n\n";

// 测试文件路径
$testFile = __DIR__ . '/web/uploads/temp/payment_import/test1111.xlsx';

if (!file_exists($testFile)) {
    echo "错误: 测试文件不存在: {$testFile}\n";
    exit(1);
}

echo "测试文件: {$testFile}\n";
echo "文件大小: " . filesize($testFile) . " bytes\n\n";

// 创建服务实例（使用wxapp_id=10022）
$service = new PaymentImportService(10022);

// 步骤1: 解析Excel文件
echo "步骤1: 解析Excel文件...\n";
$parseResult = $service->parseExcelFile($testFile);

if (!$parseResult['success']) {
    echo "解析失败: {$parseResult['error']}\n";
    exit(1);
}

echo "解析成功，总行数: {$parseResult['data']['total_rows']}\n\n";

// 步骤2: 生成预览数据
echo "步骤2: 生成预览数据（匹配订单）...\n";
$previewData = $service->generatePreview($parseResult['data']);

echo "预览数据生成完成\n";
echo "统计信息:\n";
echo "  - 总行数: {$previewData['statistics']['total_rows']}\n";
echo "  - 蓝色行: {$previewData['statistics']['blue_count']}\n";
echo "  - 粉红色行: {$previewData['statistics']['pink_count']}\n";
echo "  - 绿色行: {$previewData['statistics']['green_count']}\n";
echo "  - 未知颜色: {$previewData['statistics']['unknown_count']}\n";
echo "  - 匹配成功: {$previewData['statistics']['matched_count']}\n";
echo "  - 未匹配: {$previewData['statistics']['unmatched_count']}\n";
echo "  - 多重匹配: {$previewData['statistics']['multiple_match_count']}\n\n";

// 步骤3: 查询更新前的订单状态（抽样检查）
echo "步骤3: 查询更新前的订单状态（抽样检查）...\n";
$sampleOrders = [];
$sampleCount = 0;

foreach ($previewData['rows_by_color']['blue'] as $row) {
    if (!empty($row['matched_order']) && is_array($row['matched_order'])) {
        $orderId = $row['matched_order']['id'];
        $orderSn = $row['matched_order']['order_sn'];
        
        // 查询订单当前状态
        $order = \think\Db::name('inpack')
            ->where('id', $orderId)
            ->find();
        
        if ($order) {
            echo "订单 #{$orderId} ({$orderSn})\n";
            echo "  - 支付状态: " . ($order['is_pay'] == 1 ? '已支付' : '未支付') . "\n";
            echo "  - 支付时间: " . ($order['pay_time'] ?: '无') . "\n";
            
            $sampleOrders[] = $orderId;
            $sampleCount++;
            
            if ($sampleCount >= 5) {
                break;
            }
        }
    }
}
echo "\n";

// 步骤4: 执行导入（更新订单支付状态）
echo "步骤4: 执行导入（更新订单支付状态）...\n";

// 不需要用户修正（未知颜色会被跳过，多重匹配需要人工处理）
$userCorrections = [
    'color_corrections' => [],
    'order_selections' => []
];

try {
    $importReport = $service->executeImport($previewData, $userCorrections);
    
    echo "导入执行完成\n";
    echo "导入报告:\n";
    echo "  - 成功: " . ($importReport['success'] ? '是' : '否') . "\n";
    echo "  - 总处理数: {$importReport['total_processed']}\n";
    echo "  - 成功数: {$importReport['success_count']}\n";
    echo "  - 失败数: {$importReport['failure_count']}\n";
    echo "  - 蓝色行处理: {$importReport['statistics']['blue_processed']}\n";
    echo "  - 粉红色行处理: {$importReport['statistics']['pink_processed']}\n";
    echo "  - 绿色行处理: {$importReport['statistics']['green_processed']}\n";
    
    if (!empty($importReport['failed_members'])) {
        echo "\n失败的Member_ID:\n";
        foreach ($importReport['failed_members'] as $failed) {
            echo "  - Member_ID {$failed['member_id']}: {$failed['error']}\n";
        }
    }
    
    echo "\n";
    
} catch (\Exception $e) {
    echo "导入失败: " . $e->getMessage() . "\n";
    exit(1);
}

// 步骤5: 验证订单状态已更新
echo "步骤5: 验证订单状态已更新（抽样检查）...\n";
foreach ($sampleOrders as $orderId) {
    $order = \think\Db::name('inpack')
        ->where('id', $orderId)
        ->find();
    
    if ($order) {
        echo "订单 #{$orderId} ({$order['order_sn']})\n";
        echo "  - 支付状态: " . ($order['is_pay'] == 1 ? '已支付' : '未支付') . "\n";
        echo "  - 支付时间: " . ($order['pay_time'] ?: '无') . "\n";
    }
}

echo "\n=== 测试完成 ===\n";
