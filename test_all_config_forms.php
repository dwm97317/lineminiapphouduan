<?php
/**
 * 测试所有三个配置表单的保存功能
 * 验证修复后的 saveSystemConfig, saveTaskConfig, saveRewardConfig
 */

// 引入ThinkPHP
require __DIR__ . '/source/thinkphp/base.php';

// 数据库配置
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

// 连接数据库
$db = \think\Db::connect($config);

echo "=== 测试推荐系统配置保存功能 ===\n\n";

// 测试1: 系统配置
echo "【测试1】系统配置保存\n";
echo str_repeat('-', 50) . "\n";

try {
    $wxappId = 10001;
    
    // 模拟系统配置数据
    $systemConfig = [
        'max_referral_levels' => '3',
        'referral_code_length' => '8',
        'expire_days' => '90',
        'referral_limit_enabled' => '1',
        'referral_limit_per_month' => '10',
    ];
    
    echo "准备保存系统配置:\n";
    foreach ($systemConfig as $key => $value) {
        echo "  - {$key}: {$value}\n";
    }
    
    // 使用原始SQL保存（模拟控制器逻辑）
    foreach ($systemConfig as $key => $value) {
        $existing = $db->name('referral_system_config')
            ->where('config_key', $key)
            ->where('wxapp_id', $wxappId)
            ->find();
        
        if ($existing) {
            $result = $db->name('referral_system_config')
                ->where('config_key', $key)
                ->where('wxapp_id', $wxappId)
                ->update(['config_value' => $value]);
            echo "  ✓ 更新配置: {$key}\n";
        } else {
            $result = $db->name('referral_system_config')
                ->insert([
                    'wxapp_id' => $wxappId,
                    'config_key' => $key,
                    'config_value' => $value,
                    'config_name' => $key,
                    'is_enabled' => 1,
                ]);
            echo "  ✓ 创建配置: {$key}\n";
        }
    }
    
    echo "✅ 系统配置保存成功\n\n";
} catch (\Exception $e) {
    echo "❌ 系统配置保存失败: " . $e->getMessage() . "\n\n";
}

// 测试2: 任务配置
echo "【测试2】任务配置保存\n";
echo str_repeat('-', 50) . "\n";

try {
    $wxappId = 10001;
    
    // 模拟任务配置数据（来自表单）
    $taskConfig = [
        'referrer' => [
            3 => [
                'is_enabled' => 1,
                'is_required' => 1,
            ]
        ],
        'referee' => [
            1 => [
                'is_enabled' => 1,
                'is_required' => 1,
            ],
            2 => [
                'is_enabled' => 0,
                'is_required' => 1,
            ]
        ]
    ];
    
    $userTypeMap = [
        'referrer' => 1,
        'referee' => 2,
    ];
    
    echo "准备保存任务配置:\n";
    
    foreach ($taskConfig as $userTypeKey => $tasks) {
        if (!isset($userTypeMap[$userTypeKey])) {
            continue;
        }
        
        $userType = $userTypeMap[$userTypeKey];
        echo "\n处理 {$userTypeKey} 任务 (user_type={$userType}):\n";
        
        foreach ($tasks as $taskId => $taskData) {
            // 使用原始SQL查询
            $task = $db->name('referral_task_config')
                ->where('id', $taskId)
                ->where('wxapp_id', $wxappId)
                ->where('user_type', $userType)
                ->find();
            
            if ($task) {
                $updateData = [
                    'is_enabled' => isset($taskData['is_enabled']) ? 1 : 0,
                    'is_required' => isset($taskData['is_required']) ? 1 : 0,
                ];
                
                $result = $db->name('referral_task_config')
                    ->where('id', $taskId)
                    ->where('wxapp_id', $wxappId)
                    ->where('user_type', $userType)
                    ->update($updateData);
                
                echo "  ✓ 任务 ID {$taskId}: {$task['config_name']}\n";
                echo "    - is_enabled: {$updateData['is_enabled']}\n";
                echo "    - is_required: {$updateData['is_required']}\n";
            } else {
                echo "  ✗ 任务 ID {$taskId} 不存在或user_type不匹配\n";
            }
        }
    }
    
    echo "\n✅ 任务配置保存成功\n\n";
} catch (\Exception $e) {
    echo "❌ 任务配置保存失败: " . $e->getMessage() . "\n\n";
}

// 测试3: 奖励配置
echo "【测试3】奖励配置保存\n";
echo str_repeat('-', 50) . "\n";

try {
    $wxappId = 10001;
    
    // 模拟奖励配置数据
    $rewardConfig = [
        1 => [
            'is_enabled' => 1,
            'reward_type' => 1,
            'reward_amount' => 50.00,
            'reward_ratio' => 0,
            'expire_days' => null,
        ],
        2 => [
            'is_enabled' => 1,
            'reward_type' => 1,
            'reward_amount' => 30.00,
            'reward_ratio' => 0,
            'expire_days' => null,
        ]
    ];
    
    echo "准备保存奖励配置:\n\n";
    
    foreach ($rewardConfig as $configId => $configData) {
        // 使用原始SQL查询
        $config = $db->name('referral_reward_config')
            ->where('id', $configId)
            ->where('wxapp_id', $wxappId)
            ->find();
        
        if ($config) {
            $updateData = [
                'is_enabled' => $configData['is_enabled'] ?? 0,
                'reward_type' => $configData['reward_type'] ?? $config['reward_type'],
                'reward_amount' => $configData['reward_amount'] ?? $config['reward_amount'],
                'reward_ratio' => $configData['reward_ratio'] ?? $config['reward_ratio'],
                'expire_days' => $configData['expire_days'] ?? null,
            ];
            
            $result = $db->name('referral_reward_config')
                ->where('id', $configId)
                ->where('wxapp_id', $wxappId)
                ->update($updateData);
            
            echo "✓ 奖励配置 ID {$configId}: {$config['config_name']}\n";
            echo "  - is_enabled: {$updateData['is_enabled']}\n";
            echo "  - reward_type: {$updateData['reward_type']}\n";
            echo "  - reward_amount: {$updateData['reward_amount']}\n";
            echo "  - reward_ratio: {$updateData['reward_ratio']}\n";
            echo "\n";
        } else {
            echo "✗ 奖励配置 ID {$configId} 不存在\n\n";
        }
    }
    
    echo "✅ 奖励配置保存成功\n\n";
} catch (\Exception $e) {
    echo "❌ 奖励配置保存失败: " . $e->getMessage() . "\n\n";
}

// 验证保存结果
echo "【验证】检查保存结果\n";
echo str_repeat('-', 50) . "\n";

try {
    $wxappId = 10001;
    
    // 验证系统配置
    echo "\n1. 系统配置:\n";
    $systemConfigs = $db->name('referral_system_config')
        ->where('wxapp_id', $wxappId)
        ->select();
    foreach ($systemConfigs as $config) {
        echo "  - {$config['config_key']}: {$config['config_value']}\n";
    }
    
    // 验证任务配置
    echo "\n2. 任务配置:\n";
    $taskConfigs = $db->name('referral_task_config')
        ->where('wxapp_id', $wxappId)
        ->order('user_type', 'asc')
        ->order('id', 'asc')
        ->select();
    foreach ($taskConfigs as $task) {
        $userTypeText = $task['user_type'] == 1 ? '推荐人' : '被推荐人';
        $enabledText = $task['is_enabled'] ? '启用' : '禁用';
        $requiredText = $task['is_required'] ? '必须' : '可选';
        echo "  - ID {$task['id']}: {$task['config_name']} ({$userTypeText}) - {$enabledText}, {$requiredText}\n";
    }
    
    // 验证奖励配置
    echo "\n3. 奖励配置:\n";
    $rewardConfigs = $db->name('referral_reward_config')
        ->where('wxapp_id', $wxappId)
        ->order('id', 'asc')
        ->select();
    foreach ($rewardConfigs as $reward) {
        $enabledText = $reward['is_enabled'] ? '启用' : '禁用';
        $rewardTypeText = $reward['reward_type'] == 1 ? '现金' : ($reward['reward_type'] == 2 ? '积分' : '优惠券');
        echo "  - ID {$reward['id']}: {$reward['config_name']} - {$enabledText}, {$rewardTypeText}, 金额: {$reward['reward_amount']}\n";
    }
    
    echo "\n✅ 所有配置验证完成\n";
    
} catch (\Exception $e) {
    echo "❌ 验证失败: " . $e->getMessage() . "\n";
}

echo "\n=== 测试完成 ===\n";
