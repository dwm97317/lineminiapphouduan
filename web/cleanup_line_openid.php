<?php
// 清理 LINE 用户的 open_id 字段
// LINE 用户应该只使用 line_openid，open_id 应该为空
header('Content-Type: text/plain; charset=utf-8');

// 数据库配置
$host = '103.119.1.84';
$user = 'xinsuju';
$pass = 'cJGzwZTDCLHzWXN4';
$db = 'xinsuju';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "清理 LINE 用户的 open_id 字段\n";
    echo "=====================================\n\n";
    
    // 1. 查看需要清理的用户
    $stmt = $pdo->query("
        SELECT user_id, nickName, line_openid, open_id
        FROM yoshop_user
        WHERE line_openid IS NOT NULL 
          AND line_openid != ''
          AND open_id != ''
    ");
    $needCleanup = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "需要清理的用户数量: " . count($needCleanup) . "\n";
    if (count($needCleanup) > 0) {
        echo "-------------------------------------\n";
        foreach ($needCleanup as $user) {
            echo "User ID: {$user['user_id']}, Nick: {$user['nickName']}\n";
            echo "  line_openid: {$user['line_openid']}\n";
            echo "  当前 open_id: {$user['open_id']} (将被清空)\n";
        }
        echo "-------------------------------------\n\n";
        
        // 2. 执行清理
        echo "开始清理...\n";
        $stmt = $pdo->exec("
            UPDATE yoshop_user
            SET open_id = ''
            WHERE line_openid IS NOT NULL 
              AND line_openid != ''
              AND open_id != ''
        ");
        
        echo "✓ 清理完成！更新了 $stmt 条记录\n\n";
    } else {
        echo "✓ 所有 LINE 用户的 open_id 都已经是空的\n\n";
    }
    
    // 3. 验证结果
    echo "验证清理结果:\n";
    echo "-------------------------------------\n";
    $stmt = $pdo->query("
        SELECT user_id, nickName, line_openid, open_id
        FROM yoshop_user
        WHERE line_openid IS NOT NULL AND line_openid != ''
        ORDER BY user_id
    ");
    $allLineUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $correct = 0;
    $incorrect = 0;
    foreach ($allLineUsers as $user) {
        $status = ($user['open_id'] === '') ? '✓ 正确' : '✗ 错误';
        if ($user['open_id'] === '') {
            $correct++;
        } else {
            $incorrect++;
        }
        echo "User ID: {$user['user_id']}, Nick: {$user['nickName']}\n";
        echo "  line_openid: {$user['line_openid']}\n";
        echo "  open_id: " . ($user['open_id'] === '' ? '(空)' : $user['open_id']) . " - $status\n";
    }
    echo "-------------------------------------\n";
    echo "总计: " . count($allLineUsers) . " 个 LINE 用户\n";
    echo "正确: $correct, 错误: $incorrect\n";
    
} catch (PDOException $e) {
    echo "数据库错误: " . $e->getMessage() . "\n";
}
