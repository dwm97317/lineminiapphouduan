<?php
/**
 * 检查推荐系统表结构
 */

$config = [
    'host' => '103.119.1.84',
    'database' => 'xinsuju',
    'username' => 'xinsuju',
    'password' => 'cJGzwZTDCLHzWXN4',
    'port' => '3306',
    'charset' => 'utf8',
];

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "=== 推荐系统表结构检查 ===\n\n";
    
    // 1. 任务配置表
    echo "1. yoshop_referral_task_config 表结构:\n";
    echo str_repeat('-', 60) . "\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM yoshop_referral_task_config");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo sprintf("  %-25s %-20s %s\n", $col['Field'], $col['Type'], $col['Null']);
    }
    echo "\n";
    
    // 2. 奖励配置表
    echo "2. yoshop_referral_reward_config 表结构:\n";
    echo str_repeat('-', 60) . "\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM yoshop_referral_reward_config");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo sprintf("  %-25s %-20s %s\n", $col['Field'], $col['Type'], $col['Null']);
    }
    echo "\n";
    
    // 3. 系统配置表
    echo "3. yoshop_referral_system_config 表结构:\n";
    echo str_repeat('-', 60) . "\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM yoshop_referral_system_config");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo sprintf("  %-25s %-20s %s\n", $col['Field'], $col['Type'], $col['Null']);
    }
    echo "\n";
    
    // 4. 查看示例数据
    echo "4. 任务配置示例数据:\n";
    echo str_repeat('-', 60) . "\n";
    $stmt = $pdo->query("SELECT * FROM yoshop_referral_task_config LIMIT 2");
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($tasks)) {
        foreach ($tasks as $task) {
            echo "ID: {$task['id']}\n";
            foreach ($task as $key => $value) {
                if ($key != 'id') {
                    echo "  {$key}: " . (is_null($value) ? 'NULL' : $value) . "\n";
                }
            }
            echo "\n";
        }
    } else {
        echo "  (无数据)\n\n";
    }
    
} catch (PDOException $e) {
    echo "数据库错误: " . $e->getMessage() . "\n";
}
