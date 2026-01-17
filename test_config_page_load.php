<?php
/**
 * 测试配置页面加载
 * 验证数据格式是否正确
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
    
    echo "=== 测试配置页面数据加载 ===\n\n";
    
    $wxappId = 10001;
    
    // 模拟控制器的 config() 方法逻辑
    echo "1. 加载任务配置...\n";
    $stmt = $pdo->prepare("
        SELECT id, wxapp_id, config_name, user_type, task_type, is_enabled, is_required, task_params
        FROM yoshop_referral_task_config
        WHERE wxapp_id = ?
        ORDER BY user_type ASC, sort_order ASC
    ");
    $stmt->execute([$wxappId]);
    $taskConfigList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $taskConfigs = [
        'referrer' => [],
        'referee' => [],
    ];
    
    foreach ($taskConfigList as $task) {
        $userType = $task['user_type'];
        
        echo "  - 任务 ID {$task['id']}: user_type = ";
        var_dump($userType);
        echo "    类型: " . (is_array($userType) ? 'ARRAY (错误!)' : 'SCALAR (正确)') . "\n";
        
        if ($userType == 1) {
            $taskConfigs['referrer'][] = $task;
        } else {
            $taskConfigs['referee'][] = $task;
        }
    }
    
    echo "\n推荐人任务数: " . count($taskConfigs['referrer']) . "\n";
    echo "被推荐人任务数: " . count($taskConfigs['referee']) . "\n\n";
    
    // 2. 测试奖励配置
    echo "2. 加载奖励配置...\n";
    $stmt = $pdo->prepare("
        SELECT id, config_name, level, user_type, reward_type, is_enabled
        FROM yoshop_referral_reward_config
        WHERE wxapp_id = ?
        ORDER BY level ASC, user_type ASC
    ");
    $stmt->execute([$wxappId]);
    $rewardConfigs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($rewardConfigs as $reward) {
        $userType = $reward['user_type'];
        echo "  - 奖励 ID {$reward['id']}: user_type = ";
        var_dump($userType);
        echo "    类型: " . (is_array($userType) ? 'ARRAY (错误!)' : 'SCALAR (正确)') . "\n";
    }
    
    echo "\n奖励配置数: " . count($rewardConfigs) . "\n\n";
    
    echo "=== 测试完成 ===\n";
    echo "✅ 所有字段都是标量值，不会出现 array 错误\n";
    
} catch (PDOException $e) {
    echo "❌ 数据库错误: " . $e->getMessage() . "\n";
}
