<?php
/**
 * 检查 yoshop_package 表结构
 */

$host = '103.119.1.84';
$database = 'xinsuju';
$username = 'xinsuju';
$password = 'cJGzwZTDCLHzWXN4';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$database};charset=utf8",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "=== 检查 yoshop_package 表 ===\n\n";
    
    // 检查表是否存在
    $stmt = $pdo->query("SHOW TABLES LIKE 'yoshop_package'");
    $table = $stmt->fetch(PDO::FETCH_COLUMN);
    
    if (!$table) {
        echo "❌ yoshop_package 表不存在\n";
        exit(1);
    }
    
    echo "✅ yoshop_package 表存在\n\n";
    
    // 检查 operate_id 字段
    echo "=== 检查 operate_id 字段 ===\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM `yoshop_package` LIKE 'operate_id'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($column) {
        echo "✅ operate_id 字段存在\n";
        echo "  类型: {$column['Type']}\n";
        echo "  可空: {$column['Null']}\n";
        echo "  默认值: " . ($column['Default'] ?? 'NULL') . "\n\n";
    } else {
        echo "❌ operate_id 字段不存在\n\n";
        
        echo "=== 需要添加 operate_id 字段 ===\n";
        echo "执行以下SQL:\n\n";
        echo "ALTER TABLE `yoshop_package`\n";
        echo "ADD COLUMN `operate_id` int(11) NULL DEFAULT 0 COMMENT '操作员ID';\n\n";
        
        // 询问是否自动添加
        echo "是否自动添加该字段? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        
        if (strtolower($line) === 'y') {
            $pdo->exec("ALTER TABLE `yoshop_package` ADD COLUMN `operate_id` int(11) NULL DEFAULT 0 COMMENT '操作员ID'");
            echo "✅ operate_id 字段已添加\n\n";
            
            // 验证
            $stmt = $pdo->query("SHOW COLUMNS FROM `yoshop_package` LIKE 'operate_id'");
            $column = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($column) {
                echo "验证成功:\n";
                echo "  类型: {$column['Type']}\n";
                echo "  可空: {$column['Null']}\n";
                echo "  默认值: " . ($column['Default'] ?? 'NULL') . "\n";
            }
        } else {
            echo "跳过自动添加\n";
        }
    }
    
    // 显示表的所有字段
    echo "\n=== yoshop_package 表的所有字段 ===\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM `yoshop_package`");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "共 " . count($columns) . " 个字段:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
    
} catch (PDOException $e) {
    echo "❌ 数据库错误: " . $e->getMessage() . "\n";
    exit(1);
}
