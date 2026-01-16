<?php
$host = '103.119.1.84';
$db = 'xinsuju';
$user = 'xinsuju';
$pass = 'cJGzwZTDCLHzWXN4';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 查找包含shop的表
    $sql = "SHOW TABLES LIKE '%shop%'";
    $stmt = $conn->query($sql);
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables containing 'shop':" . PHP_EOL;
    foreach ($tables as $table) {
        echo "  - " . $table . PHP_EOL;
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
