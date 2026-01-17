<?php
/**
 * 测试配置页面修复
 * 模拟控制器的config()方法逻辑
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
    
    echo "=== 测试配置页面分组逻辑修复 ===\n\n";
    
    $wxappId = 10001;
    
    // 1. 查询任务配置
    echo "步骤1: 查询任务配置\n";
    $stmt = $pdo->query("
        SELECT * FROM yoshop_referral_task_config 
        WHERE wxapp_id = {$wxappId}
        ORDER BY user_type ASC, sort_order ASC
    ");
    $taskConfigList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "找到 " . count($taskConfigList) . " 条任务配置\n\n";
    
    // 2. 模拟访问器效果（返回数组）
    echo "步骤2: 模拟ThinkPHP访问器效果\n";
    foreach ($taskConfigList as &$task) {
        $userTypeValue = $task['user_type'];
        $task['user_type'] = [
            'value' => $userTypeValue,
            'text' => $userTypeValue == 1 ? '推荐人' : '被推荐人'
        ];
        
        $taskTypeValue = $task['task_type'];
        $task['task_type'] = [
            'value' => $taskTypeValue,
            'text' => $taskTypeValue
        ];
    }
    unset($task);
    
    echo "访问器已应用（user_type和task_type变为数组）\n\n";
    
    // 3. 旧的分组逻辑（会失败）
    echo "步骤3: 测试旧的分组逻辑\n";
    $taskConfigsOld = [
        'referrer' => [],
        'referee' => [],
    ];
    foreach ($taskConfigList as $task) {
        if ($task['user_type'] == 1) {  // 这里会失败
            $taskConfigsOld['referrer'][] = $task;
        } else {
            $taskConfigsOld['referee'][] = $task;
        }
    }
    
    echo "旧逻辑分组结果:\n";
    echo "  推荐人任务: " . count($taskConfigsOld['referrer']) . " 条\n";
    echo "  被推荐人任务: " . count($taskConfigsOld['referee']) . " 条\n";
    
    if (count($taskConfigsOld['referrer']) == 0) {
        echo "  ✗ 错误：所有任务都被分到被推荐人组！\n";
    }
    echo "\n";
    
    // 4. 新的分组逻辑（修复后）
    echo "步骤4: 测试新的分组逻辑（修复后）\n";
    $taskConfigsNew = [
        'referrer' => [],
        'referee' => [],
    ];
    foreach ($taskConfigList as $task) {
        // 处理访问器返回的数组结构
        $userType = is_array($task['user_type']) 
            ? $task['user_type']['value'] 
            : $task['user_type'];
        
        if ($userType == 1) {
            $taskConfigsNew['referrer'][] = $task;
        } else {
            $taskConfigsNew['referee'][] = $task;
        }
    }
    
    echo "新逻辑分组结果:\n";
    echo "  推荐人任务: " . count($taskConfigsNew['referrer']) . " 条\n";
    echo "  被推荐人任务: " . count($taskConfigsNew['referee']) . " 条\n\n";
    
    // 5. 详细显示分组结果
    echo "步骤5: 详细分组结果\n\n";
    
    echo "推荐人任务:\n";
    if (empty($taskConfigsNew['referrer'])) {
        echo "  (无)\n";
    } else {
        foreach ($taskConfigsNew['referrer'] as $task) {
            echo "  - ID {$task['id']}: {$task['config_name']}\n";
            echo "    user_type: {$task['user_type']['value']} ({$task['user_type']['text']})\n";
        }
    }
    echo "\n";
    
    echo "被推荐人任务:\n";
    if (empty($taskConfigsNew['referee'])) {
        echo "  (无)\n";
    } else {
        foreach ($taskConfigsNew['referee'] as $task) {
            echo "  - ID {$task['id']}: {$task['config_name']}\n";
            echo "    user_type: {$task['user_type']['value']} ({$task['user_type']['text']})\n";
        }
    }
    echo "\n";
    
    // 6. 验证结果
    echo "步骤6: 验证修复结果\n";
    $success = true;
    
    // 检查ID 3是否在referrer组
    $id3InReferrer = false;
    foreach ($taskConfigsNew['referrer'] as $task) {
        if ($task['id'] == 3) {
            $id3InReferrer = true;
            break;
        }
    }
    
    if ($id3InReferrer) {
        echo "✓ ID 3 (推荐人任务) 正确分组到 referrer\n";
    } else {
        echo "✗ ID 3 (推荐人任务) 未在 referrer 组中\n";
        $success = false;
    }
    
    // 检查ID 1和2是否在referee组
    $id1InReferee = false;
    $id2InReferee = false;
    foreach ($taskConfigsNew['referee'] as $task) {
        if ($task['id'] == 1) $id1InReferee = true;
        if ($task['id'] == 2) $id2InReferee = true;
    }
    
    if ($id1InReferee && $id2InReferee) {
        echo "✓ ID 1, 2 (被推荐人任务) 正确分组到 referee\n";
    } else {
        echo "✗ ID 1, 2 (被推荐人任务) 未在 referee 组中\n";
        $success = false;
    }
    
    echo "\n";
    
    if ($success) {
        echo "=== ✓ 修复验证成功！ ===\n";
        echo "\n现在视图将生成正确的表单结构：\n";
        echo "  - task_config[referrer][3][is_enabled] (推荐人任务)\n";
        echo "  - task_config[referee][1][is_enabled] (被推荐人任务)\n";
        echo "  - task_config[referee][2][is_enabled] (被推荐人任务)\n";
    } else {
        echo "=== ✗ 修复验证失败 ===\n";
    }
    
} catch (PDOException $e) {
    echo "\n数据库错误: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "\n错误: " . $e->getMessage() . "\n";
}
