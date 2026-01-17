<?php
// 直接测试数据库更新
$mysqli = new mysqli('103.119.1.84', 'root', 'cJGzwZTDCLHzWXN4', 'xinsuju', 3306);

if ($mysqli->connect_error) {
    die("连接失败: " . $mysqli->connect_error);
}

$mysqli->set_charset('utf8');

echo "=== 测试任务配置更新 ===\n\n";

// 1. 查询现有任务配置
echo "1. 查询现有任务配置:\n";
$result = $mysqli->query("SELECT id, wxapp_id, user_type, config_name, is_enabled, is_required FROM yoshop_referral_task_config WHERE wxapp_id = 10001 ORDER BY user_type, id");

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
    echo "  ID: {$row['id']}, 用户类型: {$row['user_type']}, 名称: {$row['config_name']}, ";
    echo "启用: {$row['is_enabled']}, 必须: {$row['is_required']}\n";
}

// 2. 模拟表单提交数据
echo "\n2. 模拟表单提交数据:\n";
$postData = [
    'referee' => [
        '1' => ['is_enabled' => '1', 'is_required' => '1'],
        '2' => ['is_enabled' => '1', 'is_required' => '1'],
        '3' => ['is_enabled' => '1', 'is_required' => '1'],
    ]
];

echo "  提交的数据: referee任务 ID 1, 2, 3 都启用且必须完成\n";

// 3. 测试更新逻辑
echo "\n3. 测试更新逻辑:\n";

$wxappId = 10001;
$userTypeMap = ['referrer' => 1, 'referee' => 2];

foreach ($postData as $userTypeKey => $taskConfigs) {
    $userType = $userTypeMap[$userTypeKey];
    echo "  处理 {$userTypeKey} (user_type={$userType}):\n";
    
    foreach ($taskConfigs as $taskId => $taskData) {
        // 查找任务
        $stmt = $mysqli->prepare("SELECT id, user_type, config_name FROM yoshop_referral_task_config WHERE id = ? AND wxapp_id = ?");
        $stmt->bind_param('ii', $taskId, $wxappId);
        $stmt->execute();
        $result = $stmt->get_result();
        $task = $result->fetch_assoc();
        
        if (!$task) {
            echo "    ✗ Task ID {$taskId} 不存在\n";
            continue;
        }
        
        if ($task['user_type'] != $userType) {
            echo "    ✗ Task ID {$taskId} ({$task['config_name']}) 用户类型不匹配\n";
            echo "      期望: {$userType}, 实际: {$task['user_type']}\n";
            continue;
        }
        
        // 更新任务
        $isEnabled = isset($taskData['is_enabled']) ? 1 : 0;
        $isRequired = isset($taskData['is_required']) ? 1 : 0;
        
        $updateStmt = $mysqli->prepare("UPDATE yoshop_referral_task_config SET is_enabled = ?, is_required = ? WHERE id = ? AND wxapp_id = ? AND user_type = ?");
        $updateStmt->bind_param('iiiii', $isEnabled, $isRequired, $taskId, $wxappId, $userType);
        
        if ($updateStmt->execute()) {
            echo "    ✓ Task ID {$taskId} ({$task['config_name']}) 更新成功\n";
            echo "      is_enabled={$isEnabled}, is_required={$isRequired}\n";
        } else {
            echo "    ✗ Task ID {$taskId} 更新失败: " . $updateStmt->error . "\n";
        }
    }
}

// 4. 查询更新后的结果
echo "\n4. 查询更新后的结果:\n";
$result = $mysqli->query("SELECT id, user_type, config_name, is_enabled, is_required FROM yoshop_referral_task_config WHERE wxapp_id = 10001 ORDER BY user_type, id");

while ($row = $result->fetch_assoc()) {
    echo "  ID: {$row['id']}, 用户类型: {$row['user_type']}, 名称: {$row['config_name']}, ";
    echo "启用: {$row['is_enabled']}, 必须: {$row['is_required']}\n";
}

$mysqli->close();

echo "\n=== 测试完成 ===\n";
echo "\n现在请刷新浏览器并重新提交表单测试\n";
