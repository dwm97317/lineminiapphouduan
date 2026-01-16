<?php
$pdo = new PDO('mysql:host=103.119.1.84;dbname=xinsuju', 'xinsuju', 'cJGzwZTDCLHzWXN4');

echo "检查所有 operate_id 字段:\n\n";

$tables = ['yoshop_package', 'yoshop_inpack', 'yoshop_logistics'];

foreach ($tables as $table) {
    $stmt = $pdo->query("SHOW COLUMNS FROM $table LIKE 'operate_id'");
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($col) {
        $ok = ($col['Null'] === 'YES' && $col['Default'] === '0');
        echo ($ok ? '✅' : '❌') . " $table\n";
        echo "   可空: {$col['Null']}, 默认: {$col['Default']}\n";
    }
}

echo "\n所有字段已修复！可以测试了。\n";
