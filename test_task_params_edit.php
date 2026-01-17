<?php
/**
 * 测试任务参数编辑功能
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

echo "=== 测试任务参数编辑功能 ===\n\n";

$wxappId = 10001;

// 1. 查看当前任务配置
echo "【1】当前任务配置\n";
echo str_repeat('-', 60) . "\n";

$tasks = $db->name('referral_task_config')
    ->where('wxapp_id', $wxappId)
    ->order('user_type', 'asc')
    ->order('id', 'asc')
    ->select();

foreach ($tasks as $task) {
    $userTypeText = $task['user_type'] == 1 ? '推荐人' : '被推荐人';
    echo "ID {$task['id']}: {$task['config_name']} ({$userTypeText})\n";
    echo "  - task_type: {$task['task_type']}\n";
    echo "  - is_enabled: {$task['is_enabled']}\n";
    echo "  - is_required: {$task['is_required']}\n";
    echo "  - task_params: " . ($task['task_params'] ?: '(空)') . "\n";
    echo "\n";
}

// 2. 模拟表单提交 - 修改首次充值任务的最低金额
echo "【2】模拟表单提交 - 修改首次充值任务参数\n";
echo str_repeat('-', 60) . "\n";

$taskConfig = [
    'referee' => [
        1 => [
            'is_enabled' => 1,
            'is_required' => 1,
        ],
        2 => [
            'is_enabled' => 1,
            'is_required' => 1,
            'task_params' => [
                'min_amount' => 200  // 修改为200
            ]
        ]
    ],
    'referrer' => [
        3 => [
            'is_enabled' => 1,
            'is_required' => 1,
        ]
    ]
];

$userTypeMap = [
    'referrer' => 1,
    'referee' => 2,
];

echo "准备更新任务配置...\n\n";

foreach ($taskConfig as $userTypeKey => $tasks) {
    if (!isset($userTypeMap[$userTypeKey])) {
        continue;
    }
    
    $userType = $userTypeMap[$userTypeKey];
    echo "处理 {$userTypeKey} 任务 (user_type={$userType}):\n";
    
    foreach ($tasks as $taskId => $taskData) {
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
            
            // 处理 task_params
            if (isset($taskData['task_params']) && is_array($taskData['task_params'])) {
                $updateData['task_params'] = json_encode($taskData['task_params'], JSON_UNESCAPED_UNICODE);
                echo "  ✓ 任务 ID {$taskId}: {$task['config_name']}\n";
                echo "    - 更新 task_params: {$updateData['task_params']}\n";
            } else {
                echo "  ✓ 任务 ID {$taskId}: {$task['config_name']}\n";
                echo "    - 保持原有 task_params\n";
            }
            
            $result = $db->name('referral_task_config')
                ->where('id', $taskId)
                ->where('wxapp_id', $wxappId)
                ->where('user_type', $userType)
                ->update($updateData);
            
            echo "    - 更新结果: " . ($result !== false ? '成功' : '失败') . "\n";
        }
    }
    echo "\n";
}

// 3. 验证更新结果
echo "【3】验证更新结果\n";
echo str_repeat('-', 60) . "\n";

$tasks = $db->name('referral_task_config')
    ->where('wxapp_id', $wxappId)
    ->order('user_type', 'asc')
    ->order('id', 'asc')
    ->select();

foreach ($tasks as $task) {
    $userTypeText = $task['user_type'] == 1 ? '推荐人' : '被推荐人';
    echo "ID {$task['id']}: {$task['config_name']} ({$userTypeText})\n";
    echo "  - task_type: {$task['task_type']}\n";
    echo "  - is_enabled: {$task['is_enabled']}\n";
    echo "  - is_required: {$task['is_required']}\n";
    echo "  - task_params: " . ($task['task_params'] ?: '(空)') . "\n";
    
    // 解析并显示参数
    if (!empty($task['task_params'])) {
        $params = json_decode($task['task_params'], true);
        if ($params) {
            echo "  - 解析后的参数:\n";
            foreach ($params as $key => $value) {
                echo "    * {$key}: {$value}\n";
            }
        }
    }
    echo "\n";
}

// 4. 测试不同任务类型的参数
echo "【4】测试任务类型识别\n";
echo str_repeat('-', 60) . "\n";

$taskTypes = [
    'register' => '完成注册',
    'first_recharge' => '首次充值',
    'first_order' => '首次下单',
    'real_name' => '实名认证',
];

foreach ($tasks as $task) {
    $taskTypeName = $taskTypes[$task['task_type']] ?? $task['task_type'];
    echo "任务类型: {$task['task_type']} ({$taskTypeName})\n";
    
    if ($task['task_type'] == 'first_recharge') {
        echo "  → 应显示: 最低充值金额输入框\n";
        $params = json_decode($task['task_params'], true);
        if ($params && isset($params['min_amount'])) {
            echo "  → 当前值: {$params['min_amount']} 泰铢\n";
        }
    } elseif ($task['task_type'] == 'first_order') {
        echo "  → 应显示: 最低订单金额输入框\n";
    } elseif ($task['task_type'] == 'register') {
        echo "  → 无需参数\n";
    } else {
        echo "  → 显示通用JSON编辑器\n";
    }
    echo "\n";
}

echo "=== 测试完成 ===\n";
