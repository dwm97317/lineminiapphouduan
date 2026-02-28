<?php
/**
 * 简单检查站内信数据
 */

// 数据库配置
$host = 'localhost';
$dbname = 'yoshop';
$username = 'root';
$password = '123456';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== 站内信数据诊断 ===\n\n";
    
    // 1. 检查表是否存在
    echo "1. 检查表是否存在\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'yoshop_site_sms'");
    if ($stmt->rowCount() == 0) {
        echo "❌ 表 yoshop_site_sms 不存在！\n";
        exit;
    }
    echo "✓ 表存在\n\n";
    
    // 2. 统计总消息数
    echo "2. 统计消息数据\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM yoshop_site_sms");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "总消息数: $total\n";
    
    if ($total == 0) {
        echo "⚠️  数据库中没有任何站内信数据！\n";
        echo "   需要先通过后台发送站内信\n\n";
        exit;
    }
    
    $stmt = $pdo->query("SELECT COUNT(*) as unread FROM yoshop_site_sms WHERE is_read = 0");
    $unread = $stmt->fetch(PDO::FETCH_ASSOC)['unread'];
    echo "未读消息: $unread\n";
    echo "已读消息: " . ($total - $unread) . "\n\n";
    
    // 3. 查看最近的5条消息
    echo "3. 最近的5条消息\n";
    $stmt = $pdo->query("SELECT * FROM yoshop_site_sms ORDER BY created_time DESC LIMIT 5");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($messages as $msg) {
        echo "---\n";
        echo "ID: {$msg['id']}\n";
        echo "用户ID: {$msg['user_id']}\n";
        echo "内容: {$msg['content']}\n";
        echo "已读: " . ($msg['is_read'] == 0 ? '未读' : '已读') . "\n";
        echo "创建时间: {$msg['created_time']}\n";
        echo "wxapp_id: {$msg['wxapp_id']}\n";
    }
    echo "\n";
    
    // 4. 按用户统计
    echo "4. 按用户统计消息数\n";
    $stmt = $pdo->query("
        SELECT user_id, 
               COUNT(*) as count, 
               SUM(CASE WHEN is_read=0 THEN 1 ELSE 0 END) as unread
        FROM yoshop_site_sms
        GROUP BY user_id
        ORDER BY count DESC
        LIMIT 10
    ");
    $userStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "用户ID | 总消息数 | 未读数\n";
    echo "-------|---------|-------\n";
    foreach ($userStats as $stat) {
        echo str_pad($stat['user_id'], 7) . "| " . 
             str_pad($stat['count'], 8) . "| " . 
             $stat['unread'] . "\n";
    }
    echo "\n";
    
    // 5. 检查wxapp_id
    echo "5. 检查wxapp_id分布\n";
    $stmt = $pdo->query("
        SELECT wxapp_id, COUNT(*) as count
        FROM yoshop_site_sms
        GROUP BY wxapp_id
    ");
    $wxappStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "wxapp_id | 消息数\n";
    echo "---------|-------\n";
    foreach ($wxappStats as $stat) {
        echo str_pad($stat['wxapp_id'] ?? 'NULL', 9) . "| " . $stat['count'] . "\n";
    }
    echo "\n";
    
    echo "=== 诊断完成 ===\n";
    
} catch (PDOException $e) {
    echo "数据库连接失败: " . $e->getMessage() . "\n";
}
