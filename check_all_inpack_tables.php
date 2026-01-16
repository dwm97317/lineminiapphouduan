<?php
/**
 * 检查所有 inpack 相关表的 operate_id 字段
 */

// 数据库配置
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
    
    echo "✅ 数据库连接成功\n\n";
    
    // 查找所有 inpack 相关的表
    echo "=== 查找所有 inpack 相关表 ===\n";
    $stmt = $pdo->query("SHOW TABLES LIKE '%inpack%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "找到 " . count($tables) . " 个表:\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    echo "\n";
    
    // 检查每个表的 operate_id 字段
    echo "=== 检查每个表的 operate_id 字段 ===\n\n";
    
    foreach ($tables as $table) {
        echo "表: $table\n";
        echo str_repeat('-', 60) . "\n";
        
        $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'operate_id'");
        $column = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($column) {
            echo "✅ 有 operate_id 字段\n";
            echo "  类型: {$column['Type']}\n";
            echo "  可空: {$column['Null']}\n";
            echo "  默认值: " . ($column['Default'] ?? 'NULL') . "\n";
            
            // 检查是否有问题
            if ($column['Null'] === 'NO' && $column['Default'] === null) {
                echo "  ⚠️  问题: 不允许NULL且没有默认值\n";
                echo "  修复 SQL:\n";
                echo "  ALTER TABLE `$table` MODIFY COLUMN `operate_id` int(11) NULL DEFAULT 0;\n";
            } else {
                echo "  ✅ 字段定义正常\n";
            }
        } else {
            echo "❌ 没有 operate_id 字段\n";
        }
        
        echo "\n";
    }
    
    // 检查是否有触发器
    echo "=== 检查触发器 ===\n";
    $stmt = $pdo->query("SHOW TRIGGERS WHERE `Trigger` LIKE '%inpack%'");
    $triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($triggers)) {
        echo "没有找到 inpack 相关的触发器\n";
    } else {
        echo "找到 " . count($triggers) . " 个触发器:\n";
        foreach ($triggers as $trigger) {
            echo "  - {$trigger['Trigger']} on {$trigger['Table']}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ 数据库错误: " . $e->getMessage() . "\n";
    exit(1);
}
