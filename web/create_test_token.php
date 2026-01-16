<?php
/**
 * 为LINE用户创建测试token并测试API
 */

// 引入ThinkPHP框架
define('APP_PATH', __DIR__ . '/../source/application/');
define('RUNTIME_PATH', __DIR__ . '/../source/runtime/');

require __DIR__ . '/../source/thinkphp/base.php';

use think\Cache;

// 用户ID
$userId = 31966;
$lineOpenId = 'Ud4e37d68c438cc70350957039add98d8';

// 生成token
$guid = uniqid();
$timeStamp = microtime(true);
$salt = 'token_salt';
$wxapp_id = 10001;
$token = md5("{$wxapp_id}_{$timeStamp}_{$lineOpenId}_{$guid}_{$salt}");

echo "Creating test token for LINE user..." . PHP_EOL;
echo "User ID: " . $userId . PHP_EOL;
echo "LINE OpenID: " . $lineOpenId . PHP_EOL;
echo "Token: " . $token . PHP_EOL;
echo PHP_EOL;

// 设置缓存数据
$cacheData = [
    'line_openid' => $lineOpenId,
    'openid' => '',  // LINE用户的openid为空
    'store_id' => $wxapp_id,
    'is_login' => true,
];

// 保存到缓存，7天有效
Cache::set($token, $cacheData, 86400 * 7);

echo "✅ Token saved to cache" . PHP_EOL;
echo PHP_EOL;

// 测试API
$url = "http://localhost:8080/index.php?s=api/page/getStorageFirst&wxapp_id=10001&token=" . $token;

echo "Testing API: " . $url . PHP_EOL;
echo PHP_EOL;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . PHP_EOL;

if ($response) {
    $result = json_decode($response, true);
    echo "Response:" . PHP_EOL;
    print_r($result);
    
    if (isset($result['data'])) {
        echo PHP_EOL;
        echo "=== Warehouse Address Details ===" . PHP_EOL;
        echo "Shop Name: " . ($result['data']['shop_name'] ?? 'N/A') . PHP_EOL;
        echo "Linkman: " . ($result['data']['linkman'] ?? 'N/A') . PHP_EOL;
        echo "Phone: " . ($result['data']['phone'] ?? 'N/A') . PHP_EOL;
        echo "Address: " . ($result['data']['address'] ?? 'N/A') . PHP_EOL;
        echo "Post Code: " . ($result['data']['post'] ?? 'N/A') . PHP_EOL;
        echo PHP_EOL;
        
        // 检查是否包含用户CODE
        if (strpos($result['data']['address'], 'Y34311') !== false || 
            strpos($result['data']['linkman'], 'Y34311') !== false) {
            echo "✅ User CODE (Y34311) found in warehouse address!" . PHP_EOL;
        } else {
            echo "❌ User CODE (Y34311) NOT found in warehouse address!" . PHP_EOL;
            echo "This is the problem we need to fix." . PHP_EOL;
        }
    }
}
