<?php
/**
 * 修复 yoshop_logistics 表的 operate_id 字段
 */

$pdo = new PDO('mysql:host=103.119.1.84;dbname=xinsuju;charset=utf8', 'xinsuju', 'cJGzwZTDCLHzWXN4', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

echo "=== 修复 yoshop_logistics.operate_id 字段 ===\n\n";

try {
    // 修复字段
    echo "执行 SQL:\n";
    echo "ALTER TABLE `yoshop_logistics` MODIFY COLUMN `operate_id` int(11) NULL DEFAULT 0;\n\n";
    
    $pdo->exec("ALTER TABLE `yoshop_logistics` MODIFY COLUMN `operate_id` int(11) NULL DEFAULT 0");
    
    echo "✅ 字段修复成功\n\n";
    
    // 验证
    echo "=== 验证修复结果 ===\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM `yoshop_logistics` LIKE 'operate_id'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($column) {
        echo "字段信息:\n";
        echo "  类型: {$column['Type']}\n";
        echo "  可空: {$column['Null']}\n";
        echo "  默认值: " . ($column['Default'] ?? 'NULL') . "\n\n";
        
        if ($column['Null'] === 'YES' && $column['Default'] === '0') {
            echo "✅ 修复成功！字段现在允许NULL且默认值为0\n";
        } else {
            echo "⚠️  修复可能不完整\n";
        }
    }
    
    // 测试插入
    echo "\n=== 测试插入数据 ===\n";
    $testData = [
        'order_sn' => 'TEST' . time(),
        'express_num' => 'TEST' . time(),
        'status' => 1,
        'status_cn' => '测试',
        'logistics_describe' => '测试',
        'wxapp_id' => 10001,
        'created_time' => date('Y-m-d H:i:s'),
    ];
    
    $fields = implode(',', array_keys($testData));
    $placeholders = ':' . implode(',:', array_keys($testData));
    
    $sql = "INSERT INTO yoshop_logistics ($fields) VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($testData);
    
    $insertId = $pdo->lastInsertId();
    echo "✅ 测试插入成功 (ID: $insertId)\n";
    
    // 清理测试数据
    $pdo->exec("DELETE FROM yoshop_logistics WHERE id = $insertId");
    echo "✅ 测试数据已清理\n";
    
} catch (PDOException $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== 修复完成 ===\n";
echo "现在可以测试 /store/package.index/uodatepackstatus 接口了！\n";
