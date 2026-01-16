<?php
/**
 * 测试仓库地址API，查看是否包含用户CODE
 */

// 模拟用户31966的token
$token = '1acd34602ef2cf199ea3eceb47eda09d5c25a568';

$url = "http://localhost:8080/index.php?s=api/page/getStorageFirst&wxapp_id=10001&token=" . $token;

echo "Testing Warehouse API..." . PHP_EOL;
echo "URL: " . $url . PHP_EOL;
echo PHP_EOL;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . PHP_EOL;
echo PHP_EOL;

if ($response) {
    $data = json_decode($response, true);
    echo "Response:" . PHP_EOL;
    print_r($data);
    
    if (isset($data['data'])) {
        echo PHP_EOL;
        echo "Warehouse Details:" . PHP_EOL;
        echo "Shop Name: " . ($data['data']['shop_name'] ?? 'N/A') . PHP_EOL;
        echo "Linkman: " . ($data['data']['linkman'] ?? 'N/A') . PHP_EOL;
        echo "Phone: " . ($data['data']['phone'] ?? 'N/A') . PHP_EOL;
        echo "Address: " . ($data['data']['address'] ?? 'N/A') . PHP_EOL;
        echo "Post Code: " . ($data['data']['post'] ?? 'N/A') . PHP_EOL;
    }
} else {
    echo "No response received" . PHP_EOL;
}
