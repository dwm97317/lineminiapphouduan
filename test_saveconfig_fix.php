<?php
// 测试saveConfig修复
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
    
    // 模拟表单提交的数据（URL解码后）
    // task_config[referee][3][is_enabled]=1
    // task_config[referee][3][is_required]=1
    // task_config[referee][1][is_enabled]=1
    // task_config[referee][1][is_required]=1
    // task_config[referee][2][is_required]=1
    
    $postData = [
        'config_type' => 'task',
        'task_config' => [
            'referee' => [
                3 => ['is_enabled' => 1, 'is_required' => 1],
                1 => ['is_enabled' => 1, 'is_required' => 1],
                2 => ['is_required' => 1], // 注意：没有 is_enabled
            ]
        ]
    ];
    
    echo "=== 提交的数据 ===\n";
    print_r($postData);
    echo "\n";
    
    // 用户类型映射
    $userTypeMap = [
        'referrer' => 1,
        'referee' => 2,
    ];
    
    $wxappId = 10001;
    $taskConfig = $postData['task_config'];
    
    echo "=== 处理任务配置 ===\n";
    foreach ($taskConfig as $userTypeKey => $tasks) {
        if (!isset($userTypeMap[$userTypeKey])) {
            echo "跳过未知的用户类型: {$userTypeKey}\n";
            continue;
        }
        
        $userType = $userTypeMap[$userTypeKey];
        echo "处理 {$userTypeKey} (user_type={$userType}) 的任务:\n";
        
        foreach ($tasks as $taskId => $taskData) {
            echo "  Task ID: {$taskId}\n";
            
            // 先查询任务是否存在且属于正确的用户类型
            $stmt = $pdo->prepare("SELECT id, user_type, config_name FROM yoshop_referral_task_config WHERE id = ? AND wxapp_id = ?");
            $stmt->execute([$taskId, $wxappId]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$task) {
                echo "    ✗ 任务不存在\n";
                continue;
            }
            
            echo "    任务名称: {$task['config_name']}\n";
            echo "    数据库中的 user_type: {$task['user_type']}\n";
            echo "    期望的 user_type: {$userType}\n";
            
            if ($task['user_type'] != $userType) {
                echo "    ✗ 用户类型不匹配！跳过更新\n";
                continue;
            }
            
            // 准备更新数据
            $isEnabled = isset($taskData['is_enabled']) ? 1 : 0;
            $isRequired = isset($taskData['is_required']) ? 1 : 0;
            
            echo "    更新: is_enabled={$isEnabled}, is_required={$isRequired}\n";
            
            // 执行更新
            $sql = "UPDATE yoshop_referral_task_config 
                    SET is_enabled = ?, is_required = ? 
                    WHERE id = ? AND wxapp_id = ? AND user_type = ?";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([$isEnabled, $isRequired, $taskId, $wxappId, $userType]);
            
            if ($result) {
                echo "    ✓ 更新成功 (影响行数: {$stmt->rowCount()})\n";
            } else {
                echo "    ✗ 更新失败\n";
            }
        }
    }
    
    echo "\n=== 验证更新后的数据 ===\n";
    $stmt = $pdo->query("SELECT id, config_name, user_type, is_enabled, is_required FROM yoshop_referral_task_config WHERE wxapp_id = 10001 ORDER BY user_type, id");
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($tasks as $task) {
        $userTypeName = $task['user_type'] == 1 ? '推荐人' : '被推荐人';
        echo "ID {$task['id']} ({$task['config_name']}) [{$userTypeName}]: is_enabled={$task['is_enabled']}, is_required={$task['is_required']}\n";
    }
    
} catch (PDOException $e) {
    echo "数据库错误: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
