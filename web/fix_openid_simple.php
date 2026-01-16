<?php
// 简单的 LINE open_id 修复脚本（不依赖框架）
header('Content-Type: text/plain; charset=utf-8');

// 数据库配置
$host = '103.119.1.84';
$user = 'xinsuju';
$pass = 'cJGzwZTDCLHzWXN4';
$db = 'xinsuju';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "修复 LINE 用户的 open_id 字段\n";
    echo "=====================================\n\n";
    
    // 1. 查看需要修复的用户
    $stmt = $pdo->query("
        SELECT user_id, nickName, line_openid, open_id
        FROM yoshop_user
        WHERE line_openid IS NOT NULL 
          AND line_openid != ''
          AND open_id != line_openid
    ");
    $needFix = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "需要修复的用户数量: " . count($needFix) . "\n";
    if (count($needFix) > 0) {
        echo "-------------------------------------\n";
        foreach ($needFix as $user) {
            echo "User ID: {$user['user_id']}, Nick: {$user['nickName']}\n";
            echo "  当前 open_id: {$user['open_id']}\n";
            echo "  应为 line_openid: {$user['line_openid']}\n";
        }
        echo "-------------------------------------\n\n";
        
        // 2. 执行修复
        echo "开始修复...\n";
        $stmt = $pdo->exec("
            UPDATE yoshop_user
            SET open_id = line_openid
            WHERE line_openid IS NOT NULL 
              AND line_openid != ''
              AND open_id != line_openid
        ");
        
        echo "✓ 修复完成！更新了 $stmt 条记录\n\n";
    } else {
        echo "✓ 所有 LINE 用户的 open_id 都是正确的\n\n";
    }
    
    // 3. 验证结果
    echo "验证修复结果:\n";
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
        $status = ($user['open_id'] === $user['line_openid']) ? '✓ 正确' : '✗ 错误';
        if ($user['open_id'] === $user['line_openid']) {
            $correct++;
        } else {
            $incorrect++;
        }
        echo "User ID: {$user['user_id']}, Nick: {$user['nickName']}, Status: $status\n";
    }
    echo "-------------------------------------\n";
    echo "总计: " . count($allLineUsers) . " 个 LINE 用户\n";
    echo "正确: $correct, 错误: $incorrect\n";
    
} catch (PDOException $e) {
    echo "数据库错误: " . $e->getMessage() . "\n";
}
