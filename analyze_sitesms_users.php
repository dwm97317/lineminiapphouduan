<?php
/**
 * 分析站内信用户分布
 */

$host = '103.119.1.84';
$dbname = 'xinsuju';
$username = 'xinsuju';
$password = 'cJGzwZTDCLHzWXN4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== 站内信用户分布分析 ===\n\n";
    
    // 1. 查看所有有消息的用户
    echo "1. 有消息的用户列表\n";
    $stmt = $pdo->query("
        SELECT s.user_id, 
               COUNT(*) as msg_count,
               SUM(CASE WHEN s.is_read = 0 THEN 1 ELSE 0 END) as unread_count,
               u.nickName,
               MAX(s.created_time) as last_msg_time
        FROM yoshop_site_sms s
        LEFT JOIN yoshop_user u ON s.user_id = u.user_id
        GROUP BY s.user_id
        ORDER BY msg_count DESC
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "用户ID | 昵称 | 消息数 | 未读数 | 最后消息时间\n";
    echo "-------|------|--------|--------|----------------\n";
    foreach ($users as $user) {
        echo str_pad($user['user_id'], 7) . "| " .
             str_pad(mb_substr($user['nickName'] ?? '未知', 0, 10), 12) . "| " .
             str_pad($user['msg_count'], 7) . "| " .
             str_pad($user['unread_count'], 7) . "| " .
             $user['last_msg_time'] . "\n";
    }
    echo "\n总共 " . count($users) . " 个用户有消息\n\n";
    
    // 2. 查看最近的消息内容
    echo "2. 最近10条消息内容\n";
    $stmt = $pdo->query("
        SELECT s.*, u.nickName
        FROM yoshop_site_sms s
        LEFT JOIN yoshop_user u ON s.user_id = u.user_id
        ORDER BY s.created_time DESC
        LIMIT 10
    ");
    $recentMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($recentMessages as $msg) {
        echo "---\n";
        echo "ID: {$msg['id']} | 用户: {$msg['nickName']} (ID:{$msg['user_id']})\n";
        echo "内容: {$msg['content']}\n";
        echo "状态: " . ($msg['is_read'] == 0 ? '未读' : '已读') . " | 时间: {$msg['created_time']}\n";
    }
    echo "\n";
    
    // 3. 统计总数
    echo "3. 总体统计\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM yoshop_site_sms");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as unread FROM yoshop_site_sms WHERE is_read = 0");
    $unread = $stmt->fetch(PDO::FETCH_ASSOC)['unread'];
    
    echo "总消息数: $total\n";
    echo "未读消息数: $unread\n";
    echo "已读消息数: " . ($total - $unread) . "\n\n";
    
    echo "=== 分析完成 ===\n";
    
} catch (PDOException $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
