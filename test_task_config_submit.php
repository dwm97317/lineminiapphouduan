<?php
// 测试任务配置提交
require __DIR__ . '/source/application/common.php';

// 模拟POST数据
$_POST = [
    'config_type' => 'task',
    'task_config' => [
        'referee' => [
            '1' => [
                'is_enabled' => '1',
                'is_required' => '1',
            ],
            '2' => [
                'is_enabled' => '1',
                'is_required' => '1',
            ],
            '3' => [
                'is_enabled' => '1',
                'is_required' => '1',
            ],
        ]
    ]
];

echo "=== 测试任务配置提交 ===\n\n";

// 连接数据库
$config = [
    'type' => 'mysql',
    'hostname' => '103.119.1.84',
    'database' => 'xinsuju',
    'username' => 'root',
    'password' => 'cJGzwZTDCLHzWXN4',
    'hostport' => '3306',
    'prefix' => 'yoshop_',
    'charset' => 'utf8',
];

\think\Db::setConfig($config);

// 查询任务配置
echo "1. 查询现有任务配置:\n";
$tasks = \think\Db::name('referral_task_config')
    ->where('wxapp_id', 10001)
    ->select();

foreach ($tasks as $task) {
    echo "  ID: {$task['id']}, 用户类型: {$task['user_type']}, 名称: {$task['config_name']}\n";
}

echo "\n2. 测试提交的数据:\n";
print_r($_POST['task_config']);

echo "\n3. 模拟保存逻辑:\n";

$wxappId = 10001;
$data = $_POST['task_config'];

// 用户类型映射
$userTypeMap = [
    'referrer' => 1,
    'referee' => 2,
];

foreach ($data as $userTypeKey => $tasks) {
    if (!isset($userTypeMap[$userTypeKey]) || !is_array($tasks)) {
        echo "  跳过无效的用户类型: {$userTypeKey}\n";
        continue;
    }
    
    $userType = $userTypeMap[$userTypeKey];
    echo "  处理 {$userTypeKey} (user_type={$userType}):\n";
    
    foreach ($tasks as $taskId => $taskData) {
        if (!is_array($taskData)) {
            echo "    跳过无效的任务数据: Task ID {$taskId}\n";
            continue;
        }
        
        // 验证任务是否存在且属于正确的用户类型
        $task = \think\Db::name('referral_task_config')
            ->where('id', $taskId)
            ->where('wxapp_id', $wxappId)
            ->find();
        
        if (!$task) {
            echo "    ✗ Task ID {$taskId} 不存在\n";
            continue;
        }
        
        if ($task['user_type'] != $userType) {
            echo "    ✗ Task ID {$taskId} 用户类型不匹配 (期望: {$userType}, 实际: {$task['user_type']})\n";
            continue;
        }
        
        $updateData = [
            'is_enabled' => isset($taskData['is_enabled']) ? 1 : 0,
            'is_required' => isset($taskData['is_required']) ? 1 : 0,
        ];
        
        echo "    ✓ Task ID {$taskId} 验证通过，更新数据: ";
        echo "is_enabled={$updateData['is_enabled']}, is_required={$updateData['is_required']}\n";
        
        // 执行更新
        try {
            $result = \think\Db::name('referral_task_config')
                ->where('id', $taskId)
                ->where('wxapp_id', $wxappId)
                ->where('user_type', $userType)
                ->update($updateData);
            
            echo "      更新结果: " . ($result !== false ? "成功" : "失败") . "\n";
        } catch (\Exception $e) {
            echo "      更新失败: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n4. 查询更新后的任务配置:\n";
$tasks = \think\Db::name('referral_task_config')
    ->where('wxapp_id', 10001)
    ->select();

foreach ($tasks as $task) {
    echo "  ID: {$task['id']}, 用户类型: {$task['user_type']}, ";
    echo "启用: {$task['is_enabled']}, 必须: {$task['is_required']}\n";
}

echo "\n=== 测试完成 ===\n";
