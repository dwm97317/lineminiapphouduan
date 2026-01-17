<?php
/**
 * 详细测试任务配置保存功能
 * 模拟表单提交并检查每一步
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
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== 数据库连接成功 ===\n\n";
    
    // 1. 查询现有任务配置
    echo "=== 步骤1: 查询现有任务配置 ===\n";
    $stmt = $pdo->query("SELECT * FROM yoshop_referral_task_config WHERE wxapp_id = 10001 ORDER BY user_type, id");
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "找到 " . count($tasks) . " 条任务配置:\n";
    foreach ($tasks as $task) {
        echo "  ID {$task['id']}: user_type={$task['user_type']} ({$task['config_name']}), ";
        echo "is_enabled={$task['is_enabled']}, is_required={$task['is_required']}\n";
    }
    echo "\n";
    
    // 2. 模拟表单提交数据
    // 根据URL参数: task_config[referee][3][is_enabled]=1&task_config[referee][3][is_required]=1
    //                task_config[referee][1][is_enabled]=1&task_config[referee][1][is_required]=1
    echo "=== 步骤2: 模拟表单提交数据 ===\n";
    $formData = [
        'referee' => [
            3 => [
                'is_enabled' => 1,
                'is_required' => 1,
            ],
            1 => [
                'is_enabled' => 1,
                'is_required' => 1,
            ],
        ],
    ];
    
    echo "表单数据:\n";
    print_r($formData);
    echo "\n";
    
    // 3. 用户类型映射
    $userTypeMap = [
        'referrer' => 1,
        'referee' => 2,
    ];
    
    // 4. 处理表单数据
    echo "=== 步骤3: 处理表单数据 ===\n";
    $wxappId = 10001;
    
    foreach ($formData as $userTypeKey => $taskConfigs) {
        if (!isset($userTypeMap[$userTypeKey])) {
            echo "跳过未知用户类型: {$userTypeKey}\n";
            continue;
        }
        
        $userType = $userTypeMap[$userTypeKey];
        echo "\n处理用户类型: {$userTypeKey} (user_type={$userType})\n";
        
        foreach ($taskConfigs as $taskId => $taskData) {
            echo "\n  处理任务 ID: {$taskId}\n";
            
            // 查询任务是否存在
            $stmt = $pdo->prepare("
                SELECT id, user_type, config_name, is_enabled, is_required 
                FROM yoshop_referral_task_config 
                WHERE id = :id AND wxapp_id = :wxapp_id
            ");
            $stmt->execute([':id' => $taskId, ':wxapp_id' => $wxappId]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$task) {
                echo "    ✗ 任务不存在\n";
                continue;
            }
            
            echo "    ✓ 任务存在: {$task['config_name']}\n";
            echo "    当前 user_type: {$task['user_type']}\n";
            echo "    期望 user_type: {$userType}\n";
            
            // 检查user_type是否匹配
            if ($task['user_type'] != $userType) {
                echo "    ✗ user_type不匹配，跳过更新\n";
                echo "    原因: 任务ID {$taskId} 属于 user_type={$task['user_type']}，";
                echo "但表单提交的是 {$userTypeKey} (user_type={$userType})\n";
                continue;
            }
            
            echo "    ✓ user_type匹配\n";
            
            // 准备更新数据
            $updateData = [
                'is_enabled' => isset($taskData['is_enabled']) ? 1 : 0,
                'is_required' => isset($taskData['is_required']) ? 1 : 0,
            ];
            
            echo "    更新数据: is_enabled={$updateData['is_enabled']}, is_required={$updateData['is_required']}\n";
            
            // 执行更新
            $updateSql = "UPDATE yoshop_referral_task_config 
                         SET is_enabled = :is_enabled, is_required = :is_required 
                         WHERE id = :id AND wxapp_id = :wxapp_id AND user_type = :user_type";
            
            $updateStmt = $pdo->prepare($updateSql);
            $result = $updateStmt->execute([
                ':is_enabled' => $updateData['is_enabled'],
                ':is_required' => $updateData['is_required'],
                ':id' => $taskId,
                ':wxapp_id' => $wxappId,
                ':user_type' => $userType,
            ]);
            
            if ($result) {
                $rowCount = $updateStmt->rowCount();
                if ($rowCount > 0) {
                    echo "    ✓ 更新成功 (影响 {$rowCount} 行)\n";
                } else {
                    echo "    ⚠ 更新执行成功但没有影响任何行 (可能数据未变化)\n";
                }
            } else {
                echo "    ✗ 更新失败\n";
            }
        }
    }
    
    // 5. 查询更新后的数据
    echo "\n\n=== 步骤4: 查询更新后的数据 ===\n";
    $stmt = $pdo->query("SELECT * FROM yoshop_referral_task_config WHERE wxapp_id = 10001 ORDER BY user_type, id");
    $tasksAfter = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "更新后的任务配置:\n";
    foreach ($tasksAfter as $task) {
        echo "  ID {$task['id']}: user_type={$task['user_type']} ({$task['config_name']}), ";
        echo "is_enabled={$task['is_enabled']}, is_required={$task['is_required']}\n";
    }
    
    echo "\n=== 测试完成 ===\n";
    
} catch (PDOException $e) {
    echo "\n数据库错误: " . $e->getMessage() . "\n";
    echo "错误代码: " . $e->getCode() . "\n";
} catch (Exception $e) {
    echo "\n错误: " . $e->getMessage() . "\n";
}
