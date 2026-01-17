<?php
/**
 * 模拟实际的表单提交
 * 测试当复选框未勾选时会发生什么
 */

echo "=== 测试表单提交行为 ===\n\n";

// 模拟场景1: 所有复选框都勾选
echo "场景1: 所有复选框都勾选\n";
$_POST = [
    'config_type' => 'task',
    'task_config' => [
        'referrer' => [
            3 => [
                'is_enabled' => '1',
                'is_required' => '1',
            ],
        ],
        'referee' => [
            1 => [
                'is_enabled' => '1',
                'is_required' => '1',
            ],
            2 => [
                'is_enabled' => '1',
                'is_required' => '1',
            ],
        ],
    ],
];

echo "POST数据:\n";
print_r($_POST);
echo "\n";

// 模拟场景2: 部分复选框未勾选（这是问题所在）
echo "场景2: ID 2 的 is_enabled 未勾选\n";
$_POST = [
    'config_type' => 'task',
    'task_config' => [
        'referrer' => [
            3 => [
                'is_enabled' => '1',
                'is_required' => '1',
            ],
        ],
        'referee' => [
            1 => [
                'is_enabled' => '1',
                'is_required' => '1',
            ],
            2 => [
                // 注意：is_enabled 没有提交（复选框未勾选）
                'is_required' => '1',
            ],
        ],
    ],
];

echo "POST数据:\n";
print_r($_POST);
echo "\n";

echo "关键发现:\n";
echo "- 当复选框未勾选时，该字段不会出现在POST数据中\n";
echo "- 控制器使用 isset() 来检查字段是否存在\n";
echo "- isset(\$taskData['is_enabled']) 会返回 false\n";
echo "- 因此 is_enabled 会被设置为 0\n";
echo "\n";

// 模拟场景3: 奖励配置（可能的问题）
echo "场景3: 奖励配置提交\n";
$_POST = [
    'config_type' => 'reward',
    'reward_config' => [
        1 => [
            'id' => '1',
            'is_enabled' => '1',
            'reward_type' => '1',
            'reward_amount' => '50.00',
            'reward_ratio' => '100.00',
            'expire_days' => '',
        ],
    ],
];

echo "POST数据:\n";
print_r($_POST);
echo "\n";

echo "检查奖励配置字段:\n";
$rewardConfig = $_POST['reward_config'][1];
foreach ($rewardConfig as $key => $value) {
    $type = gettype($value);
    echo "  $key: $value (type: $type)\n";
}
echo "\n";

// 检查是否有数组类型的值
echo "检查是否有数组类型的值:\n";
$hasArray = false;
foreach ($rewardConfig as $key => $value) {
    if (is_array($value)) {
        echo "  ✗ $key 是数组: " . print_r($value, true) . "\n";
        $hasArray = true;
    }
}

if (!$hasArray) {
    echo "  ✓ 所有值都是标量类型\n";
}
