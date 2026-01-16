<?php
$host = '103.119.1.84';
$db = 'xinsuju';
$user = 'xinsuju';
$pass = 'cJGzwZTDCLHzWXN4';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 查询仓库信息
    $sql = "SELECT shop_id, shop_name, linkman, phone, address FROM yoshop_shop WHERE shop_id = 167";
    $stmt = $conn->query($sql);
    $warehouse = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "=== Warehouse Info (shop_id=167) ===" . PHP_EOL;
    echo "Shop Name: " . $warehouse['shop_name'] . PHP_EOL;
    echo "Linkman (联系人): " . $warehouse['linkman'] . PHP_EOL;
    echo "Phone: " . $warehouse['phone'] . PHP_EOL;
    echo "Address: " . $warehouse['address'] . PHP_EOL;
    echo PHP_EOL;
    echo "这个联系人应该显示在前端，而不是用户昵称" . PHP_EOL;
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
