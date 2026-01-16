<?php
// 测试仓库 API
header('Content-Type: text/plain; charset=utf-8');

$token = isset($_GET['token']) ? $_GET['token'] : 'd797858e518f37eaccbc8e0e3e0e3e0e';
$wxapp_id = isset($_GET['wxapp_id']) ? $_GET['wxapp_id'] : '10001';

echo "测试仓库 API\n";
echo "===================\n";
echo "Token: $token\n";
echo "Wxapp ID: $wxapp_id\n\n";

// 构建 API URL
$apiUrl = "http://localhost:8080/index.php?s=api/page/getStorageFirst&wxapp_id=$wxapp_id&token=$token";
echo "API URL: $apiUrl\n\n";

// 发送请求
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($error) {
    echo "Error: $error\n";
}
echo "\nResponse:\n";
echo $response;
