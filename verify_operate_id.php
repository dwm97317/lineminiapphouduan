<?php
$pdo = new PDO('mysql:host=103.119.1.84;dbname=xinsuju;charset=utf8', 'xinsuju', 'cJGzwZTDCLHzWXN4');

echo "=== 验证 operate_id 字段 ===\n\n";

// 检查 yoshop_package
$stmt = $pdo->query("SHOW COLUMNS FROM yoshop_package LIKE 'operate_id'");
$col = $stmt->fetch(PDO::FETCH_ASSOC);

if ($col) {
    echo "✅ yoshop_package.operate_id 存在\n";
    print_r($col);
} else {
    echo "❌ yoshop_package.operate_id 不存在\n";
}

echo "\n";

// 检查 yoshop_inpack
$stmt = $pdo->query("SHOW COLUMNS FROM yoshop_inpack LIKE 'operate_id'");
$col = $stmt->fetch(PDO::FETCH_ASSOC);

if ($col) {
    echo "✅ yoshop_inpack.operate_id 存在\n";
    print_r($col);
} else {
    echo "❌ yoshop_inpack.operate_id 不存在\n";
}

echo "\n";

// 检查 yoshop_logistics
$stmt = $pdo->query("SHOW COLUMNS FROM yoshop_logistics LIKE 'operate_id'");
$col = $stmt->fetch(PDO::FETCH_ASSOC);

if ($col) {
    echo "✅ yoshop_logistics.operate_id 存在\n";
    print_r($col);
} else {
    echo "❌ yoshop_logistics.operate_id 不存在\n";
}

echo "\n=== 测试插入数据 ===\n";

// 测试插入一条数据到 yoshop_package
try {
    $testData = [
        'order_sn' => 'TEST' . time(),
        'express_num' => 'TEST' . time(),
        'status' => 1,
        'operate_id' => 0,
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
    echo "✅ 测试插入成功，ID: $insertId\n";
    
    // 删除测试数据
    $pdo->exec("DELETE FROM yoshop_package WHERE id = $insertId");
    echo "✅ 测试数据已清理\n";
    
} catch (PDOException $e) {
    echo "❌ 插入失败: " . $e->getMessage() . "\n";
}
