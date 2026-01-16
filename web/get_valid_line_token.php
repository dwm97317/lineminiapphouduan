<?php
/**
 * 从缓存中获取LINE用户的有效token
 */

$host = '103.119.1.84';
$db = 'xinsuju';
$user = 'xinsuju';
$pass = 'cJGzwZTDCLHzWXN4';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 查找LINE用户的缓存token
    $sql = "SELECT `key`, `value`, expire_time FROM yoshop_cache WHERE `key` LIKE '%' AND expire_time > UNIX_TIMESTAMP() ORDER BY expire_time DESC LIMIT 20";
    $stmt = $conn->query($sql);
    $caches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Looking for valid LINE user tokens..." . PHP_EOL;
    echo PHP_EOL;
    
    foreach ($caches as $cache) {
        $data = unserialize($cache['value']);
        
        // 检查是否包含line_openid
        if (is_array($data) && isset($data['line_openid']) && !empty($data['line_openid'])) {
            echo "✅ Found LINE user token:" . PHP_EOL;
            echo "Token: " . $cache['key'] . PHP_EOL;
            echo "LINE OpenID: " . $data['line_openid'] . PHP_EOL;
            echo "Expires: " . date('Y-m-d H:i:s', $cache['expire_time']) . PHP_EOL;
            echo PHP_EOL;
            
            // 测试这个token
            $url = "http://localhost:8080/index.php?s=api/page/getStorageFirst&wxapp_id=10001&token=" . $cache['key'];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200) {
                $result = json_decode($response, true);
                if ($result['code'] == 1) {
                    echo "✅ API Test Successful!" . PHP_EOL;
                    echo "Warehouse Data:" . PHP_EOL;
                    print_r($result['data']);
                    break;
                } else {
                    echo "❌ API returned error: " . $result['msg'] . PHP_EOL;
                }
            }
            echo PHP_EOL;
        }
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

$conn = null;
