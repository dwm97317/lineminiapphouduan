<?php
/**
 * 测试奖励配置参数编辑功能
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

echo "=== 测试奖励配置参数功能 ===\n\n";

$wxappId = 10001;

// 1. 查看当前奖励配置
echo "【1】当前奖励配置\n";
echo str_repeat('-', 60) . "\n";

$rewards = $db->name('referral_reward_config')
    ->where('wxapp_id', $wxappId)
    ->select();

foreach ($rewards as $reward) {
    $userTypeText = $reward['user_type'] == 1 ? '推荐人' : '被推荐人';
    $rewardTypeText = $reward['reward_type'] == 1 ? '现金' : ($reward['reward_type'] == 2 ? '积分' : '优惠券');
    
    echo "ID {$reward['id']}: {$reward['config_name']} ({$userTypeText})\n";
    echo "  - reward_type: {$reward['reward_type']} ({$rewardTypeText})\n";
    echo "  - reward_amount: {$reward['reward_amount']}\n";
    echo "  - reward_ratio: {$reward['reward_ratio']}\n";
    echo "  - expire_days: " . ($reward['expire_days'] ?: '(空)') . "\n";
    echo "  - reward_params: " . ($reward['reward_params'] ?: '(空)') . "\n";
    echo "\n";
}

// 2. 模拟表单提交 - 添加不同类型的参数
echo "【2】模拟表单提交 - 添加奖励参数\n";
echo str_repeat('-', 60) . "\n";

$rewardConfig = [
    1 => [
        'is_enabled' => 1,
        'reward_type' => 1,  // 现金
        'reward_amount' => 50.00,
        'reward_ratio' => 0,
        'expire_days' => null,
        'reward_params' => [
            'min_withdraw' => 100  // 最低提现金额
        ]
    ],
    2 => [
        'is_enabled' => 1,
        'reward_type' => 1,  // 现金
        'reward_amount' => 30.00,
        'reward_ratio' => 0,
        'expire_days' => null,
        'reward_params' => [
            'min_withdraw' => 50
        ]
    ]
];

echo "准备更新奖励配置...\n\n";

foreach ($rewardConfig as $configId => $configData) {
    $config = $db->name('referral_reward_config')
        ->where('id', $configId)
        ->where('wxapp_id', $wxappId)
        ->find();
    
    if ($config) {
        $updateData = [
            'is_enabled' => $configData['is_enabled'],
            'reward_type' => $configData['reward_type'],
            'reward_amount' => $configData['reward_amount'],
            'reward_ratio' => $configData['reward_ratio'],
            'expire_days' => $configData['expire_days'],
        ];
        
        // 处理 reward_params
        if (isset($configData['reward_params']) && is_array($configData['reward_params'])) {
            $params = array_filter($configData['reward_params'], function($value) {
                return $value !== '' && $value !== null;
            });
            
            if (!empty($params)) {
                $updateData['reward_params'] = json_encode($params, JSON_UNESCAPED_UNICODE);
                echo "✓ 奖励配置 ID {$configId}: {$config['config_name']}\n";
                echo "  - 更新 reward_params: {$updateData['reward_params']}\n";
            }
        }
        
        $result = $db->name('referral_reward_config')
            ->where('id', $configId)
            ->where('wxapp_id', $wxappId)
            ->update($updateData);
        
        echo "  - 更新结果: " . ($result !== false ? '成功' : '失败') . "\n\n";
    }
}

// 3. 验证更新结果
echo "【3】验证更新结果\n";
echo str_repeat('-', 60) . "\n";

$rewards = $db->name('referral_reward_config')
    ->where('wxapp_id', $wxappId)
    ->select();

foreach ($rewards as $reward) {
    $userTypeText = $reward['user_type'] == 1 ? '推荐人' : '被推荐人';
    $rewardTypeText = $reward['reward_type'] == 1 ? '现金' : ($reward['reward_type'] == 2 ? '积分' : '优惠券');
    
    echo "ID {$reward['id']}: {$reward['config_name']} ({$userTypeText})\n";
    echo "  - reward_type: {$reward['reward_type']} ({$rewardTypeText})\n";
    echo "  - reward_amount: {$reward['reward_amount']}\n";
    echo "  - reward_params: " . ($reward['reward_params'] ?: '(空)') . "\n";
    
    // 解析并显示参数
    if (!empty($reward['reward_params'])) {
        $params = json_decode($reward['reward_params'], true);
        if ($params) {
            echo "  - 解析后的参数:\n";
            foreach ($params as $key => $value) {
                echo "    * {$key}: {$value}\n";
            }
        }
    }
    echo "\n";
}

// 4. 测试不同奖励类型的参数显示
echo "【4】测试奖励类型参数映射\n";
echo str_repeat('-', 60) . "\n";

$rewardTypeParams = [
    1 => ['min_withdraw' => '最低提现金额'],
    2 => ['points_expire_days' => '积分有效期(天)'],
    3 => ['coupon_id' => '优惠券ID'],
];

foreach ($rewards as $reward) {
    $rewardTypeText = $reward['reward_type'] == 1 ? '现金' : ($reward['reward_type'] == 2 ? '积分' : '优惠券');
    echo "奖励类型: {$reward['reward_type']} ({$rewardTypeText})\n";
    
    if (isset($rewardTypeParams[$reward['reward_type']])) {
        echo "  → 应显示参数:\n";
        foreach ($rewardTypeParams[$reward['reward_type']] as $param => $label) {
            echo "    - {$label} (字段: {$param})\n";
        }
    }
    echo "\n";
}

echo "=== 测试完成 ===\n";
