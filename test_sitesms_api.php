<?php
/**
 * 站内信API测试脚本
 * 测试所有站内信相关接口
 */

// 配置
$baseUrl = 'http://localhost/api';
$token = 'YOUR_TEST_TOKEN_HERE'; // 需要替换为真实token

// 测试用户ID
$testUserId = 1;

echo "=== 站内信API测试 ===\n\n";

// 1. 测试获取站内信列表
echo "1. 测试获取站内信列表\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/sitesms/lists?page=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'token: ' . $token,
    'Content-Type: application/json'
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP状态码: $httpCode\n";
echo "响应: " . $response . "\n\n";

$listData = json_decode($response, true);
$messageId = null;
if (isset($listData['data']['list']['data'][0]['id'])) {
    $messageId = $listData['data']['list']['data'][0]['id'];
    echo "获取到消息ID: $messageId\n\n";
}

// 2. 测试获取站内信详情
if ($messageId) {
    echo "2. 测试获取站内信详情\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/sitesms/detail?id=' . $messageId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'token: ' . $token,
        'Content-Type: application/json'
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP状态码: $httpCode\n";
    echo "响应: " . $response . "\n\n";
}

// 3. 测试标记单条消息为已读
if ($messageId) {
    echo "3. 测试标记单条消息为已读\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/sitesms/read');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['id' => $messageId]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'token: ' . $token,
        'Content-Type: application/json'
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP状态码: $httpCode\n";
    echo "响应: " . $response . "\n\n";
}

// 4. 测试获取未读消息数量
echo "4. 测试获取未读消息数量\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/sitesms/unreadCount');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'token: ' . $token,
    'Content-Type: application/json'
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP状态码: $httpCode\n";
echo "响应: " . $response . "\n\n";

// 5. 测试标记全部消息为已读
echo "5. 测试标记全部消息为已读\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/sitesms/readAll');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'token: ' . $token,
    'Content-Type: application/json'
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP状态码: $httpCode\n";
echo "响应: " . $response . "\n\n";

echo "=== 测试完成 ===\n";
