<?php
/**
 * 为奖励配置表添加 reward_params 字段
 */

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

echo "=== 添加 reward_params 字段 ===\n\n";

try {
    // 检查字段是否已存在
    $columns = $db->query('SHOW COLUMNS FROM yoshop_referral_reward_config LIKE "reward_params"');
    
    if (empty($columns)) {
        echo "字段不存在，开始添加...\n";
        
        $sql = "ALTER TABLE yoshop_referral_reward_config 
                ADD COLUMN reward_params text NULL COMMENT '奖励参数(JSON格式)' 
                AFTER expire_days";
        
        $db->execute($sql);
        
        echo "✓ reward_params 字段添加成功\n\n";
    } else {
        echo "✓ reward_params 字段已存在\n\n";
    }
    
    // 显示更新后的表结构
    echo "=== 更新后的表结构 ===\n\n";
    $columns = $db->query('SHOW FULL COLUMNS FROM yoshop_referral_reward_config');
    foreach ($columns as $col) {
        echo $col['Field'] . " - " . $col['Type'];
        if ($col['Comment']) {
            echo " - " . $col['Comment'];
        }
        echo "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
}
