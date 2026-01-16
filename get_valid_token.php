<?php
/**
 * 获取有效的用户 token
 * 用于前端开发测试
 */

// 数据库配置
$host = '103.119.1.84';
$database = 'xinsuju';
$username = 'xinsuju';
$password = 'cJGzwZTDCLHzWXN4';
$port = '3306';
$prefix = 'yoshop_';

try {
    // 连接数据库
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ 数据库连接成功\n\n";
    
    // 查询用户表结构
    $sql = "DESCRIBE {$prefix}user";
    $stmt = $pdo->query($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📋 用户表结构:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
    echo "\n";
    
    // 查询用户表，获取 wxapp_id = 10001 的用户
    $sql = "SELECT user_id, nickName, wxapp_id, create_time 
            FROM {$prefix}user 
            WHERE wxapp_id = 10001 
            ORDER BY create_time DESC 
            LIMIT 5";
    
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "❌ 没有找到 wxapp_id = 10001 的用户\n\n";
        
        // 查询所有 wxapp_id
        $sql = "SELECT DISTINCT wxapp_id FROM {$prefix}user ORDER BY wxapp_id";
        $stmt = $pdo->query($sql);
        $wxapp_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "📋 数据库中存在的 wxapp_id:\n";
        foreach ($wxapp_ids as $id) {
            echo "  - $id\n";
        }
        
        // 获取第一个 wxapp_id 的用户
        if (!empty($wxapp_ids)) {
            $first_wxapp_id = $wxapp_ids[0];
            echo "\n🔄 尝试获取 wxapp_id = $first_wxapp_id 的用户...\n\n";
            
            $sql = "SELECT user_id, nickName, wxapp_id, create_time 
                    FROM {$prefix}user 
                    WHERE wxapp_id = ? 
                    ORDER BY create_time DESC 
                    LIMIT 5";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$first_wxapp_id]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    if (!empty($users)) {
        echo "✅ 找到 " . count($users) . " 个用户:\n\n";
        
        foreach ($users as $index => $user) {
            echo "用户 " . ($index + 1) . ":\n";
            echo "  user_id: {$user['user_id']}\n";
            echo "  nickName: {$user['nickName']}\n";
            echo "  wxapp_id: {$user['wxapp_id']}\n";
            echo "  create_time: {$user['create_time']}\n";
            echo "\n";
        }
        
        // 查询 token 表
        echo "🔍 查询 token 表...\n\n";
        $first_user_id = $users[0]['user_id'];
        $first_wxapp_id = $users[0]['wxapp_id'];
        
        $sql = "SELECT token, user_id, wxapp_id, create_time, update_time 
                FROM {$prefix}user_token 
                WHERE user_id = ? AND wxapp_id = ?
                ORDER BY update_time DESC 
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$first_user_id, $first_wxapp_id]);
        $token_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($token_info) {
            echo "✅ 找到 token:\n";
            echo "  token: {$token_info['token']}\n";
            echo "  user_id: {$token_info['user_id']}\n";
            echo "  wxapp_id: {$token_info['wxapp_id']}\n";
            echo "  update_time: {$token_info['update_time']}\n";
            echo "\n";
            
            // 输出 token 供前端使用
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "🔑 推荐使用的 Token:\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "Token: {$token_info['token']}\n";
            echo "User ID: {$token_info['user_id']}\n";
            echo "Wxapp ID: {$token_info['wxapp_id']}\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            
            echo "📝 在浏览器控制台执行以下代码来设置 token:\n\n";
            echo "localStorage.setItem('token', '{$token_info['token']}');\n";
            echo "localStorage.setItem('userId', '{$token_info['user_id']}');\n";
            echo "window.location.reload();\n\n";
        } else {
            echo "❌ 没有找到该用户的 token\n";
            echo "  user_id: $first_user_id\n";
            echo "  wxapp_id: $first_wxapp_id\n\n";
        }
    } else {
        echo "❌ 数据库中没有任何用户\n";
    }
    
    // 查询仓库列表
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📦 仓库列表:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    $sql = "SELECT shop_id, shop_name, wxapp_id, address, phone 
            FROM {$prefix}shop 
            ORDER BY wxapp_id, shop_id 
            LIMIT 10";
    $stmt = $pdo->query($sql);
    $shops = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($shops)) {
        foreach ($shops as $shop) {
            echo "仓库 ID: {$shop['shop_id']}\n";
            echo "  名称: {$shop['shop_name']}\n";
            echo "  wxapp_id: {$shop['wxapp_id']}\n";
            echo "  地址: {$shop['address']}\n";
            echo "  电话: {$shop['phone']}\n";
            echo "\n";
        }
    } else {
        echo "❌ 没有找到仓库数据\n";
    }
    
} catch (PDOException $e) {
    echo "❌ 数据库错误: " . $e->getMessage() . "\n";
    exit(1);
}
