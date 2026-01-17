<?php
// 调试推荐系统配置保存问题
require __DIR__ . '/source/application/common.php';

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
    
    // 1. 检查任务配置表结构
    echo "=== 检查 yoshop_referral_task_config 表结构 ===\n";
    $stmt = $pdo->query("DESCRIBE yoshop_referral_task_config");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "{$col['Field']}: {$col['Type']} (Null: {$col['Null']}, Default: {$col['Default']})\n";
    }
    echo "\n";
    
    // 2. 查询现有的任务配置数据
    echo "=== 查询现有任务配置 ===\n";
    $stmt = $pdo->query("SELECT * FROM yoshop_referral_task_config WHERE wxapp_id = 10001");
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($tasks)) {
        echo "没有找到任务配置数据！\n\n";
    } else {
        foreach ($tasks as $task) {
            echo "ID: {$task['id']}\n";
            echo "  wxapp_id: {$task['wxapp_id']}\n";
            echo "  user_type: {$task['user_type']}\n";
            echo "  task_type: {$task['task_type']}\n";
            echo "  config_name: {$task['config_name']}\n";
            echo "  is_enabled: {$task['is_enabled']}\n";
            echo "  is_required: {$task['is_required']}\n";
            echo "  sort_order: {$task['sort_order']}\n";
            echo "\n";
        }
    }
    
    // 3. 模拟表单提交的数据
    echo "=== 模拟表单提交数据 ===\n";
    $postData = [
        'config_type' => 'task',
        'task_config' => [
            'referee' => [
                3 => ['is_enabled' => 1, 'is_required' => 1],
                1 => ['is_enabled' => 1, 'is_required' => 1],
                2 => ['is_required' => 1], // 注意：这里没有 is_enabled
            ]
        ]
    ];
    
    echo "POST数据:\n";
    print_r($postData);
    echo "\n";
    
    // 4. 测试更新操作
    echo "=== 测试更新操作 ===\n";
    $wxappId = 10001;
    $taskConfig = $postData['task_config'];
    
    foreach ($taskConfig as $userType => $tasks) {
        echo "处理 {$userType} 的任务配置:\n";
        foreach ($tasks as $taskId => $taskData) {
            echo "  Task ID: {$taskId}\n";
            echo "    is_enabled: " . (isset($taskData['is_enabled']) ? 1 : 0) . "\n";
            echo "    is_required: " . (isset($taskData['is_required']) ? 1 : 0) . "\n";
            
            // 执行更新
            $updateData = [
                'is_enabled' => isset($taskData['is_enabled']) ? 1 : 0,
                'is_required' => isset($taskData['is_required']) ? 1 : 0,
            ];
            
            $sql = "UPDATE yoshop_referral_task_config 
                    SET is_enabled = :is_enabled, is_required = :is_required 
                    WHERE id = :id AND wxapp_id = :wxapp_id";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                ':is_enabled' => $updateData['is_enabled'],
                ':is_required' => $updateData['is_required'],
                ':id' => $taskId,
                ':wxapp_id' => $wxappId
            ]);
            
            if ($result) {
                echo "    ✓ 更新成功 (影响行数: {$stmt->rowCount()})\n";
            } else {
                echo "    ✗ 更新失败\n";
            }
        }
    }
    echo "\n";
    
    // 5. 验证更新后的数据
    echo "=== 验证更新后的数据 ===\n";
    $stmt = $pdo->query("SELECT id, config_name, is_enabled, is_required FROM yoshop_referral_task_config WHERE wxapp_id = 10001 AND user_type = 2 ORDER BY id");
    $updatedTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($updatedTasks as $task) {
        echo "ID {$task['id']} ({$task['config_name']}): is_enabled={$task['is_enabled']}, is_required={$task['is_required']}\n";
    }
    
} catch (PDOException $e) {
    echo "数据库错误: " . $e->getMessage() . "\n";
    echo "错误代码: " . $e->getCode() . "\n";
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
