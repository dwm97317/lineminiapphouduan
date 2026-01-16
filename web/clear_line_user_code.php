<?php
/**
 * 清空LINE用户的user_code，让他们使用User ID模式
 */

$host = '103.119.1.84';
$db = 'xinsuju';
$user = 'xinsuju';
$pass = 'cJGzwZTDCLHzWXN4';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 查找所有LINE用户
    $sql = "SELECT user_id, nickName, line_openid, user_code FROM yoshop_user 
            WHERE line_openid IS NOT NULL AND line_openid != ''";
    $stmt = $conn->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($users) . " LINE users" . PHP_EOL;
    echo PHP_EOL;
    
    // 清空user_code
    $updateSql = "UPDATE yoshop_user SET user_code = NULL WHERE user_id = :user_id";
    $updateStmt = $conn->prepare($updateSql);
    
    foreach ($users as $user) {
        echo "User ID: " . $user['user_id'] . PHP_EOL;
        echo "Nickname: " . $user['nickName'] . PHP_EOL;
        echo "Current user_code: " . ($user['user_code'] ?: 'NULL') . PHP_EOL;
        
        $updateStmt->execute(['user_id' => $user['user_id']]);
        
        echo "✅ Cleared user_code - will now use User ID mode" . PHP_EOL;
        echo "---" . PHP_EOL;
    }
    
    echo PHP_EOL;
    echo "✅ All LINE users now use User ID mode (is_show=0)" . PHP_EOL;
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
}

$conn = null;
