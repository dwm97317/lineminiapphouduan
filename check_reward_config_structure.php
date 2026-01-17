<?php
require __DIR__ . '/source/thinkphp/base.php';

$config = [
    'type' => 'mysql',
    'hostname' => '103.119.1.84',
    'database' => 'xinsuju',
    'username' => 'xinsuju',
    'password' => 'cJGzwZTDCLHzWXN4',
    'hostport' => '3306',
    'prefix' => 'yoshop_',
    'charset' => 'utf8',
];

$db = \think\Db::connect($config);

echo "=== 奖励配置表结构 ===\n\n";
$columns = $db->query('SHOW FULL COLUMNS FROM yoshop_referral_reward_config');
foreach ($columns as $col) {
    echo $col['Field'] . " - " . $col['Type'] . " - " . $col['Comment'] . "\n";
}

echo "\n=== 当前奖励配置数据 ===\n\n";
$rewards = $db->name('referral_reward_config')->where('wxapp_id', 10001)->select();
foreach ($rewards as $r) {
    echo "ID {$r['id']}: {$r['config_name']}\n";
    echo "  level: {$r['level']}\n";
    echo "  user_type: {$r['user_type']}\n";
    echo "  reward_type: {$r['reward_type']}\n";
    echo "  reward_amount: {$r['reward_amount']}\n";
    echo "  reward_ratio: {$r['reward_ratio']}\n";
    echo "  expire_days: " . ($r['expire_days'] ?: '(空)') . "\n";
    echo "  reward_params: " . ($r['reward_params'] ?: '(空)') . "\n\n";
}
