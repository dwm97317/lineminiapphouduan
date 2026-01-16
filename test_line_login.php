<?php
/**
 * LINE Login API 测试脚本
 * 直接测试 login_mp_line 接口
 */

// 开启错误显示
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 创建一个正确格式的测试 token
// 使用正确的 Channel ID: 2008873580 (从日志中看到的配置)
$header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT', 'kid' => '7f31159a65aa4bf1ddf342b57170d']));
$payload = base64_encode(json_encode([
    'sub' => 'Ud4e37d68c438cc70350957039add98d8',
    'name' => 'TLLCARGO ไทย-ลาว',
    'picture' => 'https://profile.line-scdn.net/test',
    'aud' => '2008873580',  // 正确的 Channel ID
    'exp' => time() + 3600,
    'iat' => time()
]));

// 使用 URL-safe base64 编码
$header = strtr($header, '+/', '-_');
$header = rtrim($header, '=');
$payload = strtr($payload, '+/', '-_');
$payload = rtrim($payload, '=');

$testToken = $header . '.' . $payload . '.test_signature';

echo "=== LINE Login API Test ===\n\n";
echo "Test Token: " . substr($testToken, 0, 50) . "...\n\n";

// 设置请求数据
$postData = json_encode([
    'form' => [
        'id_token' => $testToken
    ]
]);

// 设置请求头
$headers = [
    'Content-Type: application/json',
    'platform: LINE'
];

// 初始化 cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/index.php?s=api/passport/login_mp_line&wxapp_id=10001');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

echo "Sending request to: http://localhost:8080/index.php?s=api/passport/login_mp_line&wxapp_id=10001\n";
echo "POST Data: " . $postData . "\n\n";

// 执行请求
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";

if ($error) {
    echo "cURL Error: " . $error . "\n";
}

echo "\nResponse:\n";
echo $response . "\n\n";

// 尝试解析 JSON
$jsonResponse = json_decode($response, true);
if ($jsonResponse) {
    echo "Parsed Response:\n";
    print_r($jsonResponse);
} else {
    echo "Failed to parse JSON response\n";
    echo "Raw response (first 500 chars):\n";
    echo substr($response, 0, 500) . "\n";
}

echo "\n=== Test Complete ===\n";
