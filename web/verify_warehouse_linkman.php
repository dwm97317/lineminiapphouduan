<?php
$host = '103.119.1.84';
$db = 'xinsuju';
$user = 'xinsuju';
$pass = 'cJGzwZTDCLHzWXN4';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get warehouse info
    $sql = "SELECT shop_id, shop_name, linkman, phone, address FROM yoshop_store_shop WHERE wxapp_id = 10001 LIMIT 1";
    $stmt = $conn->query($sql);
    $warehouse = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "=== Warehouse Info from Database ===" . PHP_EOL;
    echo "Shop ID: " . $warehouse['shop_id'] . PHP_EOL;
    echo "Shop Name: " . $warehouse['shop_name'] . PHP_EOL;
    echo "Linkman: " . $warehouse['linkman'] . PHP_EOL;
    echo "Phone: " . $warehouse['phone'] . PHP_EOL;
    echo "Address: " . $warehouse['address'] . PHP_EOL;
    echo PHP_EOL;
    
    // Get backend settings
    $sql = "SELECT `values` FROM yoshop_setting WHERE `key` = 'store' AND wxapp_id = 10001";
    $stmt = $conn->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $settings = json_decode($result['values'], true);
    
    echo "=== Backend Settings ===" . PHP_EOL;
    echo "link_mode: " . $settings['link_mode'] . PHP_EOL;
    echo "  10 = shop_name + UID" . PHP_EOL;
    echo "  20 = warehouse linkman + UID" . PHP_EOL;
    echo "  30 = user nickname + UID" . PHP_EOL;
    echo "  40 = shop_alias_name + UID" . PHP_EOL;
    echo "  50 = user nickname only" . PHP_EOL;
    echo PHP_EOL;
    
    echo "With link_mode=20 and is_show=0 (User ID mode):" . PHP_EOL;
    echo "Expected linkman: " . $warehouse['linkman'] . "31966室" . PHP_EOL;
    echo "Current display: 李四31966室" . PHP_EOL;
    echo PHP_EOL;
    
    if ($warehouse['linkman'] == '李四') {
        echo "✅ Correct! '李四' is the warehouse's linkman from database" . PHP_EOL;
    } else {
        echo "❌ Mismatch! Database linkman is '" . $warehouse['linkman'] . "' but display shows '李四'" . PHP_EOL;
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

$conn = null;
