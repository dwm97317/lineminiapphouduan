<?php
/**
 * 测试 JWT Token 解码
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 真实的 LINE ID Token (从日志中获取的前缀)
$realTokenPrefix = 'eyJraWQiOiI3ZjMxMTU5YTY1YWE0YmYxZGRmMzQyYjU3MTcwZG';

echo "=== JWT Token Decode Test ===\n\n";

// 创建一个完整的测试 token
$header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT', 'kid' => '7f31159a65aa4bf1ddf342b57170d']));
$payload = base64_encode(json_encode([
    'sub' => 'Ud4e37d68c438cc70350957039add98d8',
    'name' => 'TLLCARGO ไทย-ลาว',
    'picture' => 'https://profile.line-scdn.net/test',
    'aud' => '2006559068',
    'exp' => time() + 3600,
    'iat' => time()
]));

// 使用 URL-safe base64 编码
$header = strtr($header, '+/', '-_');
$header = rtrim($header, '=');
$payload = strtr($payload, '+/', '-_');
$payload = rtrim($payload, '=');

$testToken = $header . '.' . $payload . '.test_signature';

echo "Test Token:\n";
echo $testToken . "\n\n";

// 测试解码逻辑（与后端相同）
$parts = explode('.', $testToken);
echo "Token Parts: " . count($parts) . "\n\n";

if (count($parts) === 3) {
    echo "Decoding payload...\n";
    $payloadDecoded = base64_decode(strtr($parts[1], '-_', '+/'));
    echo "Decoded payload (raw): " . $payloadDecoded . "\n\n";
    
    $payloadJson = json_decode($payloadDecoded, true);
    echo "Parsed payload:\n";
    print_r($payloadJson);
    
    if ($payloadJson && isset($payloadJson['sub'])) {
        echo "\n✅ Token decode successful!\n";
        echo "User ID: " . $payloadJson['sub'] . "\n";
        echo "Name: " . $payloadJson['name'] . "\n";
        echo "Expires: " . date('Y-m-d H:i:s', $payloadJson['exp']) . "\n";
    } else {
        echo "\n❌ Token decode failed\n";
    }
}

echo "\n=== Test Complete ===\n";
