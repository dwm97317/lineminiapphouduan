<?php
/**
 * 测试 saveConfig 的 array 错误
 * 模拟前端提交数据，诊断问题
 */

require __DIR__ . '/source/application/app.php';

// 模拟 POST 数据 - 任务配置
$_POST = [
    'config_type' => 'task',
    'task_config' => [
        'referrer' => [
            1 => [
                'is_enabled' => 1,
                'is_required' => 1,
                'task_params' => [
                    'min_amount' => 100
                ]
            ]
        ],
        'referee' => [
            2 => [
                'is_enabled' => 1,
                'is_required' => 0,
            ]
        ]
    ]
];

echo "=== 测试任务配置保存 ===\n\n";
echo "POST 数据:\n";
print_r($_POST);
echo "\n";

try {
    // 直接测试数据库操作
    $wxappId = 10001;
    $taskId = 1;
    $userType = 1;
    
    echo "1. 查询任务配置 (ID: {$taskId})...\n";
    $task = \think\Db::name('referral_task_config')
        ->where('id', $taskId)
        ->where('wxapp_id', $wxappId)
        ->where('user_type', $userType)
        ->find();
    
    if ($task) {
        echo "找到任务: " . json_encode($task, JSON_UNESCAPED_UNICODE) . "\n\n";
        
        // 准备更新数据
        $updateData = [
            'is_enabled' => 1,
            'is_required' => 1,
            'task_params' => json_encode(['min_amount' => 100], JSON_UNESCAPED_UNICODE)
        ];
        
        echo "2. 准备更新数据:\n";
        print_r($updateData);
        echo "\n";
        
        // 执行更新
        echo "3. 执行更新...\n";
        $result = \think\Db::name('referral_task_config')
            ->where('id', $taskId)
            ->where('wxapp_id', $wxappId)
            ->where('user_type', $userType)
            ->update($updateData);
        
        echo "更新结果: " . ($result ? '成功' : '失败') . "\n\n";
        
        // 验证更新后的数据
        echo "4. 验证更新后的数据...\n";
        $updated = \think\Db::name('referral_task_config')
            ->where('id', $taskId)
            ->find();
        echo json_encode($updated, JSON_UNESCAPED_UNICODE) . "\n\n";
        
    } else {
        echo "未找到任务配置\n";
    }
    
    echo "\n=== 测试使用模型访问器的问题 ===\n\n";
    
    // 使用模型查询（会触发访问器）
    $taskModel = \app\common\model\ReferralTaskConfig::where('id', $taskId)->find();
    
    if ($taskModel) {
        echo "模型数据:\n";
        echo "user_type 原始值: " . $taskModel->getData('user_type') . "\n";
        echo "user_type 访问器值: " . json_encode($taskModel->user_type, JSON_UNESCAPED_UNICODE) . "\n";
        echo "task_type 访问器值: " . json_encode($taskModel->task_type, JSON_UNESCAPED_UNICODE) . "\n\n";
        
        // 尝试使用模型保存（可能触发错误）
        echo "尝试使用模型更新...\n";
        try {
            $taskModel->is_enabled = 1;
            $taskModel->save();
            echo "模型更新成功\n";
        } catch (\Exception $e) {
            echo "模型更新失败: " . $e->getMessage() . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "堆栈:\n" . $e->getTraceAsString() . "\n";
}
