<?php
$host = '103.119.1.84';
$db = 'xinsuju';
$user = 'xinsuju';
$pass = 'cJGzwZTDCLHzWXN4';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "SELECT `values` FROM yoshop_setting WHERE `key` = 'store' AND wxapp_id = 10001";
    $stmt = $conn->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $settings = json_decode($result['values'], true);
    
    echo "is_change_uid: " . ($settings['is_change_uid'] ?? 'not set') . PHP_EOL;
    echo "is_show: " . $settings['usercode_mode']['is_show'] . PHP_EOL;
    echo "address_mode: " . $settings['address_mode'] . PHP_EOL;
    echo "link_mode: " . $settings['link_mode'] . PHP_EOL;
    echo PHP_EOL;
    
    // 显示link_mode的含义
    $linkModes = [
        10 => '仓库名称-UID',
        20 => '仓库联系人-UID',
        30 => '用户昵称-UID',
        40 => '仓库别名-UID',
        50 => '用户昵称（地址中包含UID）'
    ];
    echo "link_mode含义: " . ($linkModes[$settings['link_mode']] ?? 'unknown') . PHP_EOL;
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
