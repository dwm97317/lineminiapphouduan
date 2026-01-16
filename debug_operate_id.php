<?php
/**
 * 调试 operate_id 问题
 */

$pdo = new PDO('mysql:host=103.119.1.84;dbname=xinsuju;charset=utf8', 'xinsuju', 'cJGzwZTDCLHzWXN4', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

echo "=== 完整检查所有表的 operate_id 字段 ===\n\n";

// 获取所有表
$stmt = $pdo->query("SHOW TABLES");
$allTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

$tablesWithOperateId = [];
$tablesNeedingOperateId = [];

foreach ($allTables as $table) {
    $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'operate_id'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($column) {
        $tablesWithOperateId[] = $table;
        
        $hasIssue = ($column['Null'] === 'NO' && $column['Default'] === null);
        
        echo "✅ $table\n";
        echo "   类型: {$column['Type']}, 可空: {$column['Null']}, 默认值: " . ($column['Default'] ?? 'NULL') . "\n";
        
        if ($hasIssue) {
            echo "   ⚠️  问题: 不允许NULL且没有默认值！\n";
            $tablesNeedingOperateId[] = $table;
        }
        
        echo "\n";
    }
}

echo "\n=== 总结 ===\n";
echo "有 operate_id 字段的表: " . count($tablesWithOperateId) . " 个\n";
foreach ($tablesWithOperateId as $t) {
    echo "  - $t\n";
}

if (!empty($tablesNeedingOperateId)) {
    echo "\n需要修复的表: " . count($tablesNeedingOperateId) . " 个\n";
    foreach ($tablesNeedingOperateId as $t) {
        echo "  - $t\n";
        echo "    修复SQL: ALTER TABLE `$t` MODIFY COLUMN `operate_id` int(11) NULL DEFAULT 0;\n";
    }
} else {
    echo "\n✅ 所有表的 operate_id 字段都正常\n";
}
