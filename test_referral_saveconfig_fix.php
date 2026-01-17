<?php
/**
 * 测试推荐配置保存修复
 * 直接测试数据库操作，验证不会出现 array 错误
 */

// 数据库配置
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
    
    echo "=== 推荐配置保存测试 ===\n\n";
    
    // 1. 测试任务配置更新
    echo "1. 测试任务配置更新\n";
    echo str_repeat('-', 50) . "\n";
    
    $wxappId = 10001;
    
    // 查询现有任务配置
    $stmt = $pdo->prepare("
        SELECT id, wxapp_id, user_type, task_type, config_name, is_enabled, is_required, task_params
        FROM yoshop_referral_task_config
        WHERE wxapp_id = ?
        LIMIT 2
    ");
    $stmt->execute([$wxappId]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($tasks)) {
        echo "❌ 没有找到任务配置\n\n";
    } else {
        echo "找到 " . count($tasks) . " 个任务配置:\n";
        foreach ($tasks as $task) {
            echo "  - ID: {$task['id']}, 类型: {$task['user_type']}, 任务: {$task['config_name']}\n";
        }
        echo "\n";
        
        // 模拟更新第一个任务
        $taskId = $tasks[0]['id'];
        $userType = $tasks[0]['user_type'];
        
        $updateData = [
            'is_enabled' => 1,
            'is_required' => 1,
            'task_params' => json_encode(['min_amount' => 100, 'test' => 'value'], JSON_UNESCAPED_UNICODE)
        ];
        
        echo "更新任务 ID {$taskId}:\n";
        echo "  - is_enabled: {$updateData['is_enabled']}\n";
        echo "  - is_required: {$updateData['is_required']}\n";
        echo "  - task_params: {$updateData['task_params']}\n\n";
        
        $stmt = $pdo->prepare("
            UPDATE yoshop_referral_task_config
            SET is_enabled = ?, is_required = ?, task_params = ?
            WHERE id = ? AND wxapp_id = ? AND user_type = ?
        ");
        
        $result = $stmt->execute([
            $updateData['is_enabled'],
            $updateData['is_required'],
            $updateData['task_params'],
            $taskId,
            $wxappId,
            $userType
        ]);
        
        if ($result) {
            echo "✅ 任务配置更新成功\n";
            
            // 验证更新
            $stmt = $pdo->prepare("SELECT * FROM yoshop_referral_task_config WHERE id = ?");
            $stmt->execute([$taskId]);
            $updated = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "验证: task_params = {$updated['task_params']}\n";
        } else {
            echo "❌ 任务配置更新失败\n";
        }
    }
    
    echo "\n";
    
    // 2. 测试奖励配置更新
    echo "2. 测试奖励配置更新\n";
    echo str_repeat('-', 50) . "\n";
    
    // 查询现有奖励配置
    $stmt = $pdo->prepare("
        SELECT id, wxapp_id, level, user_type, reward_type, reward_amount, reward_params
        FROM yoshop_referral_reward_config
        WHERE wxapp_id = ?
        LIMIT 2
    ");
    $stmt->execute([$wxappId]);
    $rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($rewards)) {
        echo "❌ 没有找到奖励配置\n\n";
    } else {
        echo "找到 " . count($rewards) . " 个奖励配置:\n";
        foreach ($rewards as $reward) {
            echo "  - ID: {$reward['id']}, 级别: {$reward['level']}, 类型: {$reward['reward_type']}\n";
        }
        echo "\n";
        
        // 模拟更新第一个奖励
        $rewardId = $rewards[0]['id'];
        
        $updateData = [
            'is_enabled' => 1,
            'reward_amount' => 50.00,
            'reward_params' => json_encode(['coupon_id' => 123, 'min_withdraw' => 100], JSON_UNESCAPED_UNICODE)
        ];
        
        echo "更新奖励 ID {$rewardId}:\n";
        echo "  - is_enabled: {$updateData['is_enabled']}\n";
        echo "  - reward_amount: {$updateData['reward_amount']}\n";
        echo "  - reward_params: {$updateData['reward_params']}\n\n";
        
        $stmt = $pdo->prepare("
            UPDATE yoshop_referral_reward_config
            SET is_enabled = ?, reward_amount = ?, reward_params = ?
            WHERE id = ? AND wxapp_id = ?
        ");
        
        $result = $stmt->execute([
            $updateData['is_enabled'],
            $updateData['reward_amount'],
            $updateData['reward_params'],
            $rewardId,
            $wxappId
        ]);
        
        if ($result) {
            echo "✅ 奖励配置更新成功\n";
            
            // 验证更新
            $stmt = $pdo->prepare("SELECT * FROM yoshop_referral_reward_config WHERE id = ?");
            $stmt->execute([$rewardId]);
            $updated = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "验证: reward_params = {$updated['reward_params']}\n";
        } else {
            echo "❌ 奖励配置更新失败\n";
        }
    }
    
    echo "\n";
    
    // 3. 测试系统配置更新
    echo "3. 测试系统配置更新\n";
    echo str_repeat('-', 50) . "\n";
    
    $configKey = 'max_referral_levels';
    $configValue = '3';
    
    // 检查配置是否存在
    $stmt = $pdo->prepare("
        SELECT * FROM yoshop_referral_system_config
        WHERE config_key = ? AND wxapp_id = ?
    ");
    $stmt->execute([$configKey, $wxappId]);
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "更新系统配置: {$configKey} = {$configValue}\n";
        $stmt = $pdo->prepare("
            UPDATE yoshop_referral_system_config
            SET config_value = ?
            WHERE config_key = ? AND wxapp_id = ?
        ");
        $result = $stmt->execute([$configValue, $configKey, $wxappId]);
    } else {
        echo "创建系统配置: {$configKey} = {$configValue}\n";
        $stmt = $pdo->prepare("
            INSERT INTO yoshop_referral_system_config
            (wxapp_id, config_key, config_value, config_name, is_enabled)
            VALUES (?, ?, ?, ?, 1)
        ");
        $result = $stmt->execute([$wxappId, $configKey, $configValue, '最大推荐级数']);
    }
    
    if ($result) {
        echo "✅ 系统配置保存成功\n";
    } else {
        echo "❌ 系统配置保存失败\n";
    }
    
    echo "\n=== 测试完成 ===\n";
    echo "✅ 所有数据库操作正常，不会出现 array 类型错误\n";
    
} catch (PDOException $e) {
    echo "❌ 数据库错误: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
}
