<?php
/**
 * 检查任务配置表结构和数据
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
    
    echo "=== 1. 检查表结构 ===\n\n";
    $stmt = $pdo->query("DESCRIBE yoshop_referral_task_config");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "字段列表:\n";
    foreach ($columns as $col) {
        $default = $col['Default'] === null ? 'NULL' : $col['Default'];
        echo sprintf("  %-20s %-20s Null:%-5s Default: %s\n", 
            $col['Field'], 
            $col['Type'], 
            $col['Null'],
            $default
        );
    }
    
    echo "\n=== 2. 查询实际数据 ===\n\n";
    $stmt = $pdo->query("SELECT * FROM yoshop_referral_task_config WHERE wxapp_id = 10001");
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($tasks as $task) {
        echo "ID: {$task['id']} - {$task['config_name']}\n";
        echo "  wxapp_id: {$task['wxapp_id']}\n";
        echo "  user_type: {$task['user_type']}\n";
        echo "  task_type: {$task['task_type']}\n";
        echo "  is_enabled: {$task['is_enabled']}\n";
        echo "  is_required: {$task['is_required']}\n";
        echo "  sort_order: {$task['sort_order']}\n";
        
        // 检查是否有task_params字段
        if (isset($task['task_params'])) {
            echo "  task_params: {$task['task_params']}\n";
        }
        
        // 检查是否有其他字段
        $knownFields = ['id', 'wxapp_id', 'user_type', 'task_type', 'config_name', 
                       'is_enabled', 'is_required', 'sort_order', 'task_params', 
                       'create_time', 'update_time'];
        
        foreach ($task as $key => $value) {
            if (!in_array($key, $knownFields)) {
                echo "  [未知字段] $key: $value\n";
            }
        }
        echo "\n";
    }
    
    echo "=== 3. 检查控制器期望的字段 ===\n\n";
    echo "控制器 saveTaskConfig() 方法更新的字段:\n";
    echo "  - is_enabled (必需)\n";
    echo "  - is_required (必需)\n";
    echo "\n";
    
    echo "控制器 saveTaskConfig() 方法查询条件:\n";
    echo "  - id (必需)\n";
    echo "  - wxapp_id (必需)\n";
    echo "  - user_type (必需)\n";
    echo "\n";
    
    echo "=== 4. 模拟表单提交数据 ===\n\n";
    $formData = [
        'config_type' => 'task',
        'task_config' => [
            'referee' => [
                1 => [
                    'is_enabled' => 1,
                    'is_required' => 1,
                ],
                2 => [
                    'is_enabled' => 0,
                    'is_required' => 1,
                ],
            ],
            'referrer' => [
                3 => [
                    'is_enabled' => 1,
                    'is_required' => 1,
                ],
            ],
        ],
    ];
    
    echo "表单数据结构:\n";
    echo "config_type: {$formData['config_type']}\n";
    echo "task_config:\n";
    foreach ($formData['task_config'] as $userTypeKey => $tasks) {
        echo "  $userTypeKey:\n";
        foreach ($tasks as $taskId => $taskData) {
            echo "    ID $taskId:\n";
            foreach ($taskData as $field => $value) {
                echo "      $field: $value\n";
            }
        }
    }
    
    echo "\n=== 5. 检查是否缺少字段 ===\n\n";
    
    // 检查表中是否有控制器需要但表单没有提供的字段
    $tableFields = array_column($columns, 'Field');
    $formFields = ['is_enabled', 'is_required'];
    
    echo "表中的所有字段:\n";
    foreach ($tableFields as $field) {
        echo "  - $field\n";
    }
    
    echo "\n表单提交的字段:\n";
    foreach ($formFields as $field) {
        echo "  - $field\n";
    }
    
    echo "\n表中有但表单没有提交的字段:\n";
    $missingInForm = array_diff($tableFields, $formFields);
    $missingInForm = array_diff($missingInForm, ['id', 'wxapp_id', 'user_type', 'task_type', 
                                                  'config_name', 'sort_order', 'create_time', 'update_time']);
    
    if (empty($missingInForm)) {
        echo "  (无 - 表单已包含所有必要字段)\n";
    } else {
        foreach ($missingInForm as $field) {
            echo "  ⚠️ $field (可能需要在表单中添加)\n";
        }
    }
    
    echo "\n=== 6. 检查 task_params 字段 ===\n\n";
    
    $hasTaskParams = in_array('task_params', $tableFields);
    if ($hasTaskParams) {
        echo "✓ 表中存在 task_params 字段\n";
        
        // 检查是否有任务使用了task_params
        $stmt = $pdo->query("SELECT id, config_name, task_params FROM yoshop_referral_task_config WHERE wxapp_id = 10001 AND task_params IS NOT NULL AND task_params != ''");
        $tasksWithParams = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($tasksWithParams)) {
            echo "  当前没有任务使用 task_params\n";
        } else {
            echo "  使用 task_params 的任务:\n";
            foreach ($tasksWithParams as $task) {
                echo "    ID {$task['id']}: {$task['config_name']} = {$task['task_params']}\n";
            }
        }
    } else {
        echo "✗ 表中不存在 task_params 字段\n";
    }
    
} catch (PDOException $e) {
    echo "数据库错误: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
