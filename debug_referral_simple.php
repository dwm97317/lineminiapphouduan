<?php
// 简单的数据库调试脚本
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
    echo "=== 查询现有任务配置 (wxapp_id=10001) ===\n";
    $stmt = $pdo->query("SELECT * FROM yoshop_referral_task_config WHERE wxapp_id = 10001");
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($tasks)) {
        echo "没有找到任务配置数据！\n\n";
        
        // 检查是否有其他wxapp_id的数据
        echo "=== 检查所有wxapp_id ===\n";
        $stmt = $pdo->query("SELECT DISTINCT wxapp_id FROM yoshop_referral_task_config");
        $wxappIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($wxappIds)) {
            echo "表中没有任何数据！\n";
        } else {
            echo "找到的wxapp_id: " . implode(', ', $wxappIds) . "\n";
        }
    } else {
        echo "找到 " . count($tasks) . " 条任务配置:\n\n";
        foreach ($tasks as $task) {
            echo "ID: {$task['id']}\n";
            echo "  wxapp_id: {$task['wxapp_id']}\n";
            echo "  user_type: {$task['user_type']} (" . ($task['user_type'] == 1 ? '推荐人' : '被推荐人') . ")\n";
            echo "  task_type: {$task['task_type']}\n";
            echo "  config_name: {$task['config_name']}\n";
            echo "  is_enabled: {$task['is_enabled']}\n";
            echo "  is_required: {$task['is_required']}\n";
            echo "  sort_order: {$task['sort_order']}\n";
            echo "\n";
        }
    }
    
    // 3. 测试更新操作（使用实际的ID）
    if (!empty($tasks)) {
        echo "=== 测试更新操作 ===\n";
        $testTask = $tasks[0];
        $testId = $testTask['id'];
        
        echo "测试更新 Task ID: {$testId}\n";
        
        $sql = "UPDATE yoshop_referral_task_config 
                SET is_enabled = :is_enabled, is_required = :is_required 
                WHERE id = :id AND wxapp_id = :wxapp_id";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':is_enabled' => 1,
            ':is_required' => 1,
            ':id' => $testId,
            ':wxapp_id' => 10001
        ]);
        
        if ($result) {
            echo "✓ 更新成功 (影响行数: {$stmt->rowCount()})\n";
        } else {
            echo "✗ 更新失败\n";
        }
    }
    
} catch (PDOException $e) {
    echo "数据库错误: " . $e->getMessage() . "\n";
    echo "错误代码: " . $e->getCode() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
