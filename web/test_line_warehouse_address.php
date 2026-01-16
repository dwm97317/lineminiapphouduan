<?php
/**
 * 测试LINE用户的仓库地址生成逻辑
 */

$host = '103.119.1.84';
$db = 'xinsuju';
$user = 'xinsuju';
$pass = 'cJGzwZTDCLHzWXN4';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 检查LINE用户的user_code状态
    $sql = "SELECT user_id, nickName, line_openid, user_code FROM yoshop_user WHERE user_id = 31966";
    $stmt = $conn->query($sql);
    $lineUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "=== LINE User Info ===" . PHP_EOL;
    echo "User ID: " . $lineUser['user_id'] . PHP_EOL;
    echo "Nickname: " . $lineUser['nickName'] . PHP_EOL;
    echo "User Code: " . ($lineUser['user_code'] ?: 'NULL (will use User ID mode)') . PHP_EOL;
    echo PHP_EOL;
    
    // 检查后台设置
    $sql = "SELECT `values` FROM yoshop_setting WHERE `key` = 'store' AND wxapp_id = 10001";
    $stmt = $conn->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $settings = json_decode($result['values'], true);
    
    echo "=== Backend Settings ===" . PHP_EOL;
    echo "is_show: " . $settings['usercode_mode']['is_show'] . " (0=UID only, 1=CODE only, 2=both)" . PHP_EOL;
    echo "address_mode: " . $settings['address_mode'] . PHP_EOL;
    echo "link_mode: " . $settings['link_mode'] . PHP_EOL;
    echo PHP_EOL;
    
    // 根据逻辑推断应该生成的地址
    echo "=== Expected Address Format ===" . PHP_EOL;
    if ($settings['usercode_mode']['is_show'] == 0) {
        echo "Should show: User ID (31966)" . PHP_EOL;
        echo "Expected in address: UID:31966 or similar" . PHP_EOL;
    } elseif ($settings['usercode_mode']['is_show'] == 1) {
        if (empty($lineUser['user_code'])) {
            echo "⚠️ is_show=1 but user_code is NULL!" . PHP_EOL;
            echo "Backend should fallback to User ID mode" . PHP_EOL;
        } else {
            echo "Should show: User Code (" . $lineUser['user_code'] . ")" . PHP_EOL;
        }
    }
    echo PHP_EOL;
    
    // 测试API - 先不用ThinkPHP，直接用之前创建的token
    echo "=== Testing API with existing token ===" . PHP_EOL;
    
    $token = '058ab8e0f41d5903b1041da484a64520'; // 之前创建的token
    
    $url = "http://localhost:8080/index.php?s=api/page/getStorageFirst&wxapp_id=10001&token=" . $token;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($result['code'] == 1) {
        echo "✅ API Success" . PHP_EOL;
        echo "Linkman: " . $result['data']['linkman'] . PHP_EOL;
        echo "Address: " . $result['data']['address'] . PHP_EOL;
        echo PHP_EOL;
        
        // 检查是否包含User ID
        if (strpos($result['data']['address'], '31966') !== false || 
            strpos($result['data']['linkman'], '31966') !== false) {
            echo "✅ User ID (31966) found in warehouse address!" . PHP_EOL;
        } else {
            echo "❌ User ID (31966) NOT found in warehouse address!" . PHP_EOL;
            echo "⚠️ This is the problem - backend is not generating User ID in address" . PHP_EOL;
        }
    } else {
        echo "❌ API Error: " . $result['msg'] . PHP_EOL;
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

$conn = null;
