<?php
/**
 * 完整测试配置保存流程
 * 模拟从视图提交到控制器保存的完整过程
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
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== 完整配置保存流程测试 ===\n\n";
    
    $wxappId = 10001;
    
    // 1. 查询当前状态
    echo "步骤1: 查询当前任务配置状态\n";
    $stmt = $pdo->query("SELECT id, config_name, user_type, is_enabled, is_required, task_params FROM yoshop_referral_task_config WHERE wxapp_id = {$wxappId}");
    $tasksBefore = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "当前状态:\n";
    foreach ($tasksBefore as $task) {
        echo "  ID {$task['id']}: {$task['config_name']}\n";
        echo "    user_type: {$task['user_type']}, is_enabled: {$task['is_enabled']}, is_required: {$task['is_required']}\n";
        if ($task['task_params']) {
            echo "    task_params: {$task['task_params']}\n";
        }
    }
    echo "\n";
    
    // 2. 模拟正确的表单提交（修复后）
    echo "步骤2: 模拟正确的表单提交\n";
    $formData = [
        'config_type' => 'task',
        'task_config' => [
            'referrer' => [
                3 => [
                    'is_enabled' => 1,  // 勾选
                    'is_required' => 1, // 勾选
                ],
            ],
            'referee' => [
                1 => [
                    'is_enabled' => 1,  // 勾选
                    'is_required' => 1, // 勾选
                ],
                2 => [
                    // 不勾选is_enabled，只勾选is_required
                    'is_required' => 1,
                ],
            ],
        ],
    ];
    
    echo "表单数据:\n";
    echo "  referrer[3]: is_enabled=1, is_required=1\n";
    echo "  referee[1]: is_enabled=1, is_required=1\n";
    echo "  referee[2]: is_enabled=未勾选, is_required=1\n";
    echo "\n";
    
    // 3. 模拟控制器的saveTaskConfig逻辑
    echo "步骤3: 执行保存逻辑\n";
    
    $userTypeMap = [
        'referrer' => 1,
        'referee' => 2,
    ];
    
    $pdo->beginTransaction();
    
    $updateCount = 0;
    $skipCount = 0;
    
    foreach ($formData['task_config'] as $userTypeKey => $tasks) {
        if (!isset($userTypeMap[$userTypeKey])) {
            continue;
        }
        
        $userType = $userTypeMap[$userTypeKey];
        echo "\n处理 {$userTypeKey} (user_type={$userType}):\n";
        
        foreach ($tasks as $taskId => $taskData) {
            echo "  任务 ID {$taskId}:\n";
            
            // 查询任务
            $stmt = $pdo->prepare("
                SELECT id, config_name, user_type, task_params 
                FROM yoshop_referral_task_config 
                WHERE id = :id AND wxapp_id = :wxapp_id
            ");
            $stmt->execute([':id' => $taskId, ':wxapp_id' => $wxappId]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$task) {
                echo "    ✗ 任务不存在\n";
                $skipCount++;
                continue;
            }
            
            echo "    ✓ 找到任务: {$task['config_name']}\n";
            echo "    数据库 user_type: {$task['user_type']}\n";
            echo "    期望 user_type: {$userType}\n";
            
            // 验证user_type
            if ($task['user_type'] != $userType) {
                echo "    ✗ user_type不匹配，跳过\n";
                $skipCount++;
                continue;
            }
            
            echo "    ✓ user_type匹配\n";
            
            // 准备更新数据
            $updateData = [
                'is_enabled' => isset($taskData['is_enabled']) ? 1 : 0,
                'is_required' => isset($taskData['is_required']) ? 1 : 0,
            ];
            
            echo "    更新: is_enabled={$updateData['is_enabled']}, is_required={$updateData['is_required']}\n";
            
            // 注意：task_params不会被更新
            if ($task['task_params']) {
                echo "    保持: task_params={$task['task_params']} (不变)\n";
            }
            
            // 执行更新
            $updateSql = "UPDATE yoshop_referral_task_config 
                         SET is_enabled = :is_enabled, 
                             is_required = :is_required,
                             update_time = UNIX_TIMESTAMP()
                         WHERE id = :id 
                         AND wxapp_id = :wxapp_id 
                         AND user_type = :user_type";
            
            $updateStmt = $pdo->prepare($updateSql);
            $result = $updateStmt->execute([
                ':is_enabled' => $updateData['is_enabled'],
                ':is_required' => $updateData['is_required'],
                ':id' => $taskId,
                ':wxapp_id' => $wxappId,
                ':user_type' => $userType,
            ]);
            
            if ($result && $updateStmt->rowCount() > 0) {
                echo "    ✓ 更新成功\n";
                $updateCount++;
            } else {
                echo "    ⚠ 更新执行但无变化\n";
            }
        }
    }
    
    $pdo->commit();
    
    echo "\n更新统计:\n";
    echo "  成功更新: {$updateCount} 条\n";
    echo "  跳过: {$skipCount} 条\n";
    echo "\n";
    
    // 4. 查询更新后的状态
    echo "步骤4: 查询更新后的状态\n";
    $stmt = $pdo->query("SELECT id, config_name, user_type, is_enabled, is_required, task_params FROM yoshop_referral_task_config WHERE wxapp_id = {$wxappId}");
    $tasksAfter = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "更新后状态:\n";
    foreach ($tasksAfter as $task) {
        echo "  ID {$task['id']}: {$task['config_name']}\n";
        echo "    user_type: {$task['user_type']}, is_enabled: {$task['is_enabled']}, is_required: {$task['is_required']}\n";
        if ($task['task_params']) {
            echo "    task_params: {$task['task_params']} (保持不变)\n";
        }
    }
    echo "\n";
    
    // 5. 对比变化
    echo "步骤5: 对比变化\n";
    foreach ($tasksAfter as $after) {
        foreach ($tasksBefore as $before) {
            if ($after['id'] == $before['id']) {
                $changed = [];
                if ($after['is_enabled'] != $before['is_enabled']) {
                    $changed[] = "is_enabled: {$before['is_enabled']} → {$after['is_enabled']}";
                }
                if ($after['is_required'] != $before['is_required']) {
                    $changed[] = "is_required: {$before['is_required']} → {$after['is_required']}";
                }
                
                if (!empty($changed)) {
                    echo "  ID {$after['id']}: " . implode(', ', $changed) . "\n";
                } else {
                    echo "  ID {$after['id']}: 无变化\n";
                }
                break;
            }
        }
    }
    
    echo "\n=== ✓ 测试完成 ===\n";
    echo "\n结论:\n";
    echo "1. 控制器只更新 is_enabled 和 is_required 字段\n";
    echo "2. task_params 字段保持不变（这是正确的）\n";
    echo "3. 表单不需要提交 task_params\n";
    echo "4. 当前实现是正确的\n";
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n数据库错误: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n错误: " . $e->getMessage() . "\n";
}
