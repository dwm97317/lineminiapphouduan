<?php
/**
 * 查看 yoshop_user 表结构
 */

$host = '103.119.1.84';
$db = 'xinsuju';
$user = 'xinsuju';
$pass = 'cJGzwZTDCLHzWXN4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== yoshop_user 表结构 ===\n\n";
    echo str_pad("Field", 25) . str_pad("Type", 25) . str_pad("Null", 10) . str_pad("Key", 10) . str_pad("Default", 20) . str_pad("Extra", 20) . "\n";
    echo str_repeat("-", 110) . "\n";
    
    $stmt = $pdo->query('DESCRIBE yoshop_user');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        echo str_pad($col['Field'], 25) . 
             str_pad($col['Type'], 25) . 
             str_pad($col['Null'], 10) . 
             str_pad($col['Key'], 10) . 
             str_pad($col['Default'] ?? 'NULL', 20) . 
             str_pad($col['Extra'], 20) . "\n";
    }
    
    echo "\n=== 必填字段（NOT NULL 且无默认值）===\n\n";
    $requiredFields = [];
    foreach ($columns as $col) {
        if ($col['Null'] === 'NO' && $col['Default'] === null && $col['Extra'] !== 'auto_increment') {
            $requiredFields[] = $col['Field'];
            echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    }
    
    echo "\n=== 有默认值的字段 ===\n\n";
    foreach ($columns as $col) {
        if ($col['Default'] !== null) {
            echo "- " . $col['Field'] . " = " . $col['Default'] . "\n";
        }
    }
    
    echo "\n=== 可为 NULL 的字段 ===\n\n";
    foreach ($columns as $col) {
        if ($col['Null'] === 'YES') {
            echo "- " . $col['Field'] . "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "数据库连接失败: " . $e->getMessage() . "\n";
}
