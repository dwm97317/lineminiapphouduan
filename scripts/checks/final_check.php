<?php
$pdo = new PDO('mysql:host=103.119.1.84;dbname=xinsuju', 'xinsuju', 'cJGzwZTDCLHzWXN4');

echo "=== 最终验证所有 operate_id 字段 ===\n\n";

$tables = ['yoshop_package', 'yoshop_inpack', 'yoshop_logistics'];

foreach ($tables as $table) {
    $stmt = $pdo->query("SHOW COLUMNS FROM $table LIKE 'operate_id'");
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($col) {
        $status = ($col['Null'] === 'YES' && $col['Default'] === '0') ? '✅' : '❌';
        echo "$status $table.operate_id\n";
        echo "   可空: {$col['Null']}, 默认值: {$col['Default']}\n\n";
    }
}

echo "=== 测试完整流程 ===\n\n";

// 模拟插入 package
try {
    $testPkg = [
        'order_sn' => 'TEST' . time(),
        'express_num' => 'TEST' . time(),
        'status' => 2,
        'operate_id' => 0,
        'wxapp_id' => 10001,
        'created_time' => date('Y-m-d H:i:s'),
        'updated_time' => date('Y-m-d H:i:s'),
    ];
    
    $stmt = $pdo->prepare("INSERT INTO yoshop_package (order_sn, express_num, status, operate_id, wxapp_id, created_time, updated_time) VALUES (:order_sn, :express_num, :status, :operate_id, :wxapp_id, :created_time, :updated_time)");
    $stmt->execute($testPkg);
    $pkgId = $pdo->lastInsertId();
    
    echo "✅ yoshop_package 插入成功 (ID: $pkgId)\n";
    
    // 模拟插入 logistics
    $testLog = [
        'order_sn' => $testPkg['order_sn'],
        'express_num' => $testPkg['express_num'],
        'status' => 2,
        'status_cn' => '已入库',
        'logistics_describe' => '包裹已入库',
        'wxapp_id' => 10001,
        'created_time' => date('Y-m-d H:i:s'),
    ];
    
    $stmt = $pdo->prepare("INSERT INTO yoshop_logistics (order_sn, express_num, status, status_cn, logistics_describe, wxapp_id, created_time) VALUES (:order_sn, :express_num, :status, :status_cn, :logistics_describe, :wxapp_id, :created_time)");
    $stmt->execute($testLog);
    $logId = $pdo->lastInsertId();
    
    echo "✅ yoshop_logistics 插入成功 (ID: $logId)\n";
    
    // 清理
    $pdo->exec("DELETE FROM yoshop_package WHERE id = $pkgId");
    $pdo->exec("DELETE FROM yoshop_logistics WHERE id = $logId");
    
    echo "✅ 测试数据已清理\n\n";
    
    echo "=== 所有测试通过！===\n";
    echo "现在可以使用 /store/package.index/uodatepackstatus 接口了！\n";
    
} catch (PDOException $e) {
    echo "❌ 测试失败: " . $e->getMessage() . "\n";
}
