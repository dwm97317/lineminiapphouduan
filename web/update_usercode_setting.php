<?php
/**
 * 修改后台设置：将is_show改为0（只显示User ID）
 * 这样LINE用户（没有user_code）就会显示User ID
 */

$host = '103.119.1.84';
$db = 'xinsuju';
$user = 'xinsuju';
$pass = 'cJGzwZTDCLHzWXN4';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 获取当前设置
    $sql = "SELECT `values` FROM yoshop_setting WHERE `key` = 'store' AND wxapp_id = 10001";
    $stmt = $conn->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $settings = json_decode($result['values'], true);
    
    echo "=== Current Settings ===" . PHP_EOL;
    echo "is_show: " . $settings['usercode_mode']['is_show'] . " (0=UID only, 1=CODE only, 2=both)" . PHP_EOL;
    echo PHP_EOL;
    
    // 修改设置
    $settings['usercode_mode']['is_show'] = 0;  // 改为只显示User ID
    
    // 更新数据库
    $updateSql = "UPDATE yoshop_setting SET `values` = :values WHERE `key` = 'store' AND wxapp_id = 10001";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->execute(['values' => json_encode($settings, JSON_UNESCAPED_UNICODE)]);
    
    echo "✅ Updated is_show to 0 (User ID only mode)" . PHP_EOL;
    echo PHP_EOL;
    echo "Now LINE users will show User ID in warehouse address" . PHP_EOL;
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
}

$conn = null;
