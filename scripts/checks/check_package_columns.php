<?php
define('APP_PATH', __DIR__ . '/source/application/');
require __DIR__ . '/source/thinkphp/start.php';

$db = \think\Db::connect();
$columns = $db->query('SHOW COLUMNS FROM yoshop_package');

echo "所有包裹表字段:\n";
foreach ($columns as $col) {
    echo "- {$col['Field']} ({$col['Type']})\n";
}

