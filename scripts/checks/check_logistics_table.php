<?php
$pdo = new PDO('mysql:host=103.119.1.84;dbname=xinsuju;charset=utf8', 'xinsuju', 'cJGzwZTDCLHzWXN4');

echo "=== 检查所有可能需要 operate_id 的表 ===\n\n";

$tables = ['yoshop_package', 'yoshop_inpack', 'yoshop_logistics', 'yoshop_package_item', 'yoshop_shelf_unit_item'];

foreach ($tables as $table) {
    echo "表: $table\n";
    echo str_repeat('-', 60) . "\n";
    
    // 检查表是否存在
    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
    $exists = $stmt->fetch(PDO::FETCH_COLUMN);
    
    if (!$exists) {
        echo "  ❌ 表不存在\n\n";
        continue;
    }
    
    // 检查 operate_id 字段
    $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'operate_id'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($column) {
        echo "  ✅ 有 operate_id 字段\n";
        echo "     类型: {$column['Type']}, 可空: {$column['Null']}, 默认值: " . ($column['Default'] ?? 'NULL') . "\n";
        
        if ($column['Null'] === 'NO' && $column['Default'] === null) {
            echo "     ⚠️  问题: 不允许NULL且没有默认值\n";
        }
    } else {
        echo "  ℹ️  没有 operate_id 字段\n";
    }
    
    echo "\n";
}

// 尝试模拟插入操作，看看哪个表会报错
echo "=== 测试插入操作 ===\n\n";

// 测试 yoshop_package
try {
    $testData = [
        'order_sn' => 'TEST' . time(),
        'express_num' => 'TEST' . time(),
        'status' => 1,
        'wxapp_id' => 10001,
        'created_time' => date('Y-m-d H:i:s'),
        'updated_time' => date('Y-m-d H:i:s'),
    ];
    
    $fields = implode(',', array_keys($testData));
    $placeholders = ':' . implode(',:', array_keys($testData));
    
    $sql = "INSERT INTO yoshop_package ($fields) VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($testData);
    
    $insertId = $pdo->lastInsertId();
    echo "✅ yoshop_package 插入成功 (ID: $insertId)\n";
    
    // 清理
    $pdo->exec("DELETE FROM yoshop_package WHERE id = $insertId");
    
} catch (PDOException $e) {
    echo "❌ yoshop_package 插入失败: " . $e->getMessage() . "\n";
}

echo "\n";
