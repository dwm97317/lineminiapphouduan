<?php
/**
 * 查看 yoshop_user_oauth 表结构
 */

$host = '103.119.1.84';
$db = 'xinsuju';
$user = 'xinsuju';
$pass = 'cJGzwZTDCLHzWXN4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== yoshop_user_oauth 表结构 ===\n\n";
    echo str_pad("Field", 25) . str_pad("Type", 25) . str_pad("Null", 10) . str_pad("Key", 10) . str_pad("Default", 20) . "\n";
    echo str_repeat("-", 90) . "\n";
    
    $stmt = $pdo->query('DESCRIBE yoshop_user_oauth');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        echo str_pad($col['Field'], 25) . 
             str_pad($col['Type'], 25) . 
             str_pad($col['Null'], 10) . 
             str_pad($col['Key'], 10) . 
             str_pad($col['Default'] ?? 'NULL', 20) . "\n";
    }
    
} catch (PDOException $e) {
    echo "数据库连接失败: " . $e->getMessage() . "\n";
}
