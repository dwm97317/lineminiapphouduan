<?php
/**
 * 测试 details_pack API
 * 
 * 用法: 
 * php test_details_pack_api.php
 * 或在浏览器访问: http://localhost:8080/test_details_pack_api.php
 */

// 测试配置
$apiUrl = 'http://localhost:8080/index.php?s=api/package/detailsPack&wxapp_id=10001&token=746cb905b97bc6314ee4bbd2041417b0';
$orderId = 69406;

echo "=== 测试 details_pack API ===\n\n";
echo "API URL: $apiUrl\n";
echo "Order ID: $orderId\n\n";

// 准备请求数据
$postData = json_encode(['id' => $orderId]);

// 初始化 cURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($postData)
]);

// 执行请求
echo "发送请求...\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// 显示结果
echo "\n=== 响应结果 ===\n";
echo "HTTP Code: $httpCode\n";

if ($error) {
    echo "错误: $error\n";
} else {
    echo "\n响应内容:\n";
    $data = json_decode($response, true);
    if ($data) {
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        // 验证响应
        echo "\n=== 验证结果 ===\n";
        if (isset($data['code']) && $data['code'] == 1) {
            echo "✅ API 调用成功!\n";
            if (isset($data['data'])) {
                echo "✅ 返回了订单数据\n";
                echo "   订单号: " . ($data['data']['order_sn'] ?? '未知') . "\n";
                echo "   包裹数: " . (isset($data['data']['item']) ? count($data['data']['item']) : 0) . "\n";
                echo "   总费用: " . ($data['data']['free_total'] ?? '0') . "\n";
            }
        } else {
            echo "❌ API 调用失败\n";
            echo "   错误信息: " . ($data['msg'] ?? '未知错误') . "\n";
        }
    } else {
        echo "原始响应:\n$response\n";
    }
}

echo "\n=== 测试完成 ===\n";
?>
