<?php
/**
 * 测试账户绑定 API
 * 
 * 使用方法：
 * php test_account_bind.php
 * 
 * 需要配置：
 * 1. BASE_URL: 后端 API 地址
 * 2. WXAPP_ID: 小程序 ID
 * 3. TOKEN: 用户登录 token
 */

$BASE_URL = 'http://localhost/web/index.php'; // 修改为实际地址
$WXAPP_ID = '10001';
$TOKEN = 'YOUR_TEST_TOKEN'; // 替换为实际 token

echo "========================================\n";
echo "账户绑定 API 测试\n";
echo "========================================\n\n";

/**
 * 发送 HTTP POST 请求
 */
function sendPostRequest($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'response' => $response,
    ];
}

// Test 1: 绑定有效的 Customer ID
echo "Test 1: 绑定有效的 Customer ID\n";
echo "----------------------------------------\n";
$testData = [
    'wxapp_id' => $WXAPP_ID,
    'token' => $TOKEN,
    'customer_id' => 'CUST_TEST_001',
    'platform_type' => 'FACEBOOK',
];

$result = sendPostRequest($BASE_URL . '?s=/api/account/bind', $testData);
echo "HTTP Code: " . $result['http_code'] . "\n";
echo "Response: " . json_encode(json_decode($result['response']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Test 2: Customer ID 为空
echo "Test 2: Customer ID 为空\n";
echo "----------------------------------------\n";
$testData = [
    'wxapp_id' => $WXAPP_ID,
    'token' => $TOKEN,
    'customer_id' => '',
    'platform_type' => 'FACEBOOK',
];

$result = sendPostRequest($BASE_URL . '?s=/api/account/bind', $testData);
echo "HTTP Code: " . $result['http_code'] . "\n";
echo "Response: " . json_encode(json_decode($result['response']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Test 3: 未登录
echo "Test 3: 未登录状态\n";
echo "----------------------------------------\n";
$testData = [
    'wxapp_id' => $WXAPP_ID,
    'token' => '',
    'customer_id' => 'CUST_TEST_002',
    'platform_type' => 'FACEBOOK',
];

$result = sendPostRequest($BASE_URL . '?s=/api/account/bind', $testData);
echo "HTTP Code: " . $result['http_code'] . "\n";
echo "Response: " . json_encode(json_decode($result['response']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Test 4: 查询绑定列表
echo "Test 4: 查询绑定列表\n";
echo "----------------------------------------\n";
$testData = [
    'wxapp_id' => $WXAPP_ID,
    'token' => $TOKEN,
];

$result = sendPostRequest($BASE_URL . '?s=/api/account/bindings', $testData);
echo "HTTP Code: " . $result['http_code'] . "\n";
echo "Response: " . json_encode(json_decode($result['response']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Test 5: 验证 Customer ID（不绑定）
echo "Test 5: 验证 Customer ID（不绑定）\n";
echo "----------------------------------------\n";
$testData = [
    'wxapp_id' => $WXAPP_ID,
    'token' => $TOKEN,
    'customer_id' => 'CUST_TEST_003',
    'platform_type' => 'FACEBOOK',
];

$result = sendPostRequest($BASE_URL . '?s=/api/account/verify-customer', $testData);
echo "HTTP Code: " . $result['http_code'] . "\n";
echo "Response: " . json_encode(json_decode($result['response']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "========================================\n";
echo "测试完成\n";
echo "========================================\n";
