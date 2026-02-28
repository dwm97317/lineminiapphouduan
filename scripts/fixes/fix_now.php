<?php
$pdo = new PDO('mysql:host=103.119.1.84;dbname=xinsuju', 'xinsuju', 'cJGzwZTDCLHzWXN4');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "修复 yoshop_logistics.operate_id...\n";
$pdo->exec("ALTER TABLE yoshop_logistics MODIFY COLUMN operate_id int(11) NULL DEFAULT 0");
echo "完成!\n";

$stmt = $pdo->query("SHOW COLUMNS FROM yoshop_logistics LIKE 'operate_id'");
$col = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($col);
