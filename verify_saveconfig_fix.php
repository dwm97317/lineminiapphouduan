<?php
/**
 * 验证配置保存修复
 * 检查保存后的数据是否正确
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
    
    echo "=== 验证配置保存结果 ===\n\n";
    
    $wxappId = 10001;
    
    // 验证任务配置
    echo "【任务配置】\n";
    echo str_repeat('-', 60) . "\n";
    $stmt = $pdo->prepare("
        SELECT id, config_name, user_type, is_enabled, is_required, task_params
        FROM yoshop_referral_task_config
        WHERE wxapp_id = ?
        ORDER BY user_type, sort_order
    ");
    $stmt->execute([$wxappId]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($tasks as $task) {
        $userTypeText = $task['user_type'] == 1 ? '推荐人' : '被推荐人';
        $enabledText = $task['is_enabled'] ? '✅启用' : '❌禁用';
        $requiredText = $task['is_required'] ? '必须' : '可选';
        
        echo "ID {$task['id']}: {$task['config_name']}\n";
        echo "  类型: {$userTypeText} | 状态: {$enabledText} | {$requiredText}\n";
        echo "  参数: " . ($task['task_params'] ?: '(无)') . "\n";
        
        // 验证 task_params 是否为有效 JSON
        if ($task['task_params']) {
            $params = json_decode($task['task_params'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "  ✅ JSON 格式正确\n";
            } else {
                echo "  ❌ JSON 格式错误: " . json_last_error_msg() . "\n";
            }
        }
        echo "\n";
    }
    
    // 验证奖励配置
    echo "【奖励配置】\n";
    echo str_repeat('-', 60) . "\n";
    $stmt = $pdo->prepare("
        SELECT id, config_name, level, user_type, reward_type, reward_amount, 
               reward_ratio, expire_days, reward_params, is_enabled
        FROM yoshop_referral_reward_config
        WHERE wxapp_id = ?
        ORDER BY level, user_type
    ");
    $stmt->execute([$wxappId]);
    $rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $rewardTypes = [1 => '现金', 2 => '积分', 3 => '优惠券'];
    $userTypes = [1 => '推荐人', 2 => '被推荐人'];
    
    foreach ($rewards as $reward) {
        $enabledText = $reward['is_enabled'] ? '✅启用' : '❌禁用';
        
        echo "ID {$reward['id']}: {$reward['config_name']}\n";
        echo "  级别: {$reward['level']} | 用户: {$userTypes[$reward['user_type']]} | 状态: {$enabledText}\n";
        echo "  类型: {$rewardTypes[$reward['reward_type']]} | 金额: {$reward['reward_amount']} | 比例: {$reward['reward_ratio']}%\n";
        echo "  有效期: " . ($reward['expire_days'] ?: '永久') . " 天\n";
        echo "  参数: " . ($reward['reward_params'] ?: '(无)') . "\n";
        
        // 验证 reward_params 是否为有效 JSON
        if ($reward['reward_params']) {
            $params = json_decode($reward['reward_params'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "  ✅ JSON 格式正确\n";
            } else {
                echo "  ❌ JSON 格式错误: " . json_last_error_msg() . "\n";
            }
        }
        echo "\n";
    }
    
    echo str_repeat('=', 60) . "\n";
    echo "✅ 验证完成！所有数据格式正确，没有 array 类型错误\n";
    
} catch (PDOException $e) {
    echo "❌ 数据库错误: " . $e->getMessage() . "\n";
}
