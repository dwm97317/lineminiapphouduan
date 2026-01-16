<?php
/**
 * 完整测试站内信功能
 * 1. 检查数据库数据
 * 2. 插入测试数据
 * 3. 测试API接口
 */

// 数据库配置
$host = '103.119.1.84';
$dbname = 'xinsuju';
$username = 'xinsuju';
$password = 'cJGzwZTDCLHzWXN4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== 站内信完整测试 ===\n\n";
    
    // 1. 检查现有数据
    echo "1. 检查现有数据\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM yoshop_site_sms");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "现有消息总数: $total\n\n";
    
    // 2. 获取一个测试用户ID
    echo "2. 获取测试用户\n";
    $stmt = $pdo->query("SELECT user_id FROM yoshop_user LIMIT 1");
    $testUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$testUser) {
        echo "❌ 没有找到用户，无法测试\n";
        exit;
    }
    
    $testUserId = $testUser['user_id'];
    echo "测试用户ID: $testUserId\n\n";
    
    // 3. 检查该用户的现有消息
    echo "3. 检查用户现有消息\n";
    $stmt = $pdo->prepare("SELECT * FROM yoshop_site_sms WHERE user_id = ? ORDER BY created_time DESC LIMIT 5");
    $stmt->execute([$testUserId]);
    $userMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "用户 $testUserId 的消息数: " . count($userMessages) . "\n";
    if (count($userMessages) > 0) {
        echo "最近的消息:\n";
        foreach ($userMessages as $msg) {
            echo "  - [{$msg['id']}] {$msg['content']} (" . 
                 ($msg['is_read'] == 0 ? '未读' : '已读') . ") - {$msg['created_time']}\n";
        }
    }
    echo "\n";
    
    // 4. 插入测试消息
    echo "4. 插入测试消息\n";
    $testContent = "测试站内信 - " . date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("
        INSERT INTO yoshop_site_sms (user_id, content, is_read, created_time, updated_time, wxapp_id)
        VALUES (?, ?, 0, NOW(), NOW(), 10001)
    ");
    $stmt->execute([$testUserId, $testContent]);
    $newId = $pdo->lastInsertId();
    echo "✓ 插入成功，消息ID: $newId\n";
    echo "  内容: $testContent\n\n";
    
    // 5. 验证插入
    echo "5. 验证插入的消息\n";
    $stmt = $pdo->prepare("SELECT * FROM yoshop_site_sms WHERE id = ?");
    $stmt->execute([$newId]);
    $newMessage = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($newMessage) {
        echo "✓ 消息已保存\n";
        echo "  ID: {$newMessage['id']}\n";
        echo "  用户ID: {$newMessage['user_id']}\n";
        echo "  内容: {$newMessage['content']}\n";
        echo "  已读状态: " . ($newMessage['is_read'] == 0 ? '未读' : '已读') . "\n";
        echo "  wxapp_id: {$newMessage['wxapp_id']}\n";
    }
    echo "\n";
    
    // 6. 统计信息
    echo "6. 最终统计\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM yoshop_site_sms");
    $finalTotal = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM yoshop_site_sms WHERE user_id = ?");
    $stmt->execute([$testUserId]);
    $userTotal = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM yoshop_site_sms WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$testUserId]);
    $userUnread = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "数据库总消息数: $finalTotal\n";
    echo "用户 $testUserId 的消息数: $userTotal\n";
    echo "用户 $testUserId 的未读数: $userUnread\n\n";
    
    echo "=== 测试完成 ===\n\n";
    echo "现在可以测试前端API:\n";
    echo "1. 访问: http://localhost/api/sitesms/lists?token=YOUR_TOKEN\n";
    echo "2. 确保token对应的用户ID是: $testUserId\n";
    echo "3. 或者在前端登录用户ID为 $testUserId 的账号\n";
    
} catch (PDOException $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
