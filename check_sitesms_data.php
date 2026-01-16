<?php
/**
 * 检查站内信数据和诊断问题
 */

require __DIR__ . '/source/bootstrap.php';

use think\Db;

echo "=== 站内信数据诊断 ===\n\n";

// 1. 检查表是否存在
echo "1. 检查表是否存在\n";
try {
    $tableExists = Db::query("SHOW TABLES LIKE 'yoshop_site_sms'");
    if (empty($tableExists)) {
        echo "❌ 表 yoshop_site_sms 不存在！\n";
        exit;
    }
    echo "✓ 表存在\n\n";
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
    exit;
}

// 2. 检查表结构
echo "2. 检查表结构\n";
try {
    $columns = Db::query("SHOW COLUMNS FROM yoshop_site_sms");
    echo "字段列表:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']}) " . 
             ($col['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . 
             ($col['Key'] == 'PRI' ? ' PRIMARY KEY' : '') . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
}

// 3. 统计总消息数
echo "3. 统计消息数据\n";
try {
    $total = Db::table('site_sms')->count();
    echo "总消息数: $total\n";
    
    if ($total == 0) {
        echo "⚠️  数据库中没有任何站内信数据！\n";
        echo "   需要先通过后台发送站内信: /store/market.push/sendsms\n\n";
    } else {
        $unread = Db::table('site_sms')->where('is_read', 0)->count();
        $read = Db::table('site_sms')->where('is_read', 1)->count();
        echo "未读消息: $unread\n";
        echo "已读消息: $read\n\n";
    }
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
}

// 4. 查看最近的消息
echo "4. 最近的5条消息\n";
try {
    $messages = Db::table('site_sms')
        ->order('created_time', 'desc')
        ->limit(5)
        ->select();
    
    if (empty($messages)) {
        echo "没有消息记录\n\n";
    } else {
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
    }
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
}

// 5. 按用户统计
echo "5. 按用户统计消息数\n";
try {
    $userStats = Db::table('site_sms')
        ->field('user_id, COUNT(*) as count, SUM(CASE WHEN is_read=0 THEN 1 ELSE 0 END) as unread')
        ->group('user_id')
        ->order('count', 'desc')
        ->limit(10)
        ->select();
    
    if (empty($userStats)) {
        echo "没有数据\n\n";
    } else {
        echo "用户ID | 总消息数 | 未读数\n";
        echo "-------|---------|-------\n";
        foreach ($userStats as $stat) {
            echo str_pad($stat['user_id'], 7) . "| " . 
                 str_pad($stat['count'], 8) . "| " . 
                 $stat['unread'] . "\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
}

// 6. 检查API模型的getList方法
echo "6. 测试API模型查询\n";
try {
    // 假设测试用户ID为1
    $testUserId = 1;
    
    // 检查用户1是否有消息
    $userMessages = Db::table('site_sms')
        ->where('user_id', $testUserId)
        ->count();
    
    echo "用户ID $testUserId 的消息数: $userMessages\n";
    
    if ($userMessages > 0) {
        echo "✓ 用户有消息数据\n";
    } else {
        echo "⚠️  用户没有消息数据\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
}

// 7. 检查wxapp_id
echo "7. 检查wxapp_id分布\n";
try {
    $wxappStats = Db::table('site_sms')
        ->field('wxapp_id, COUNT(*) as count')
        ->group('wxapp_id')
        ->select();
    
    if (empty($wxappStats)) {
        echo "没有数据\n\n";
    } else {
        echo "wxapp_id | 消息数\n";
        echo "---------|-------\n";
        foreach ($wxappStats as $stat) {
            echo str_pad($stat['wxapp_id'], 9) . "| " . $stat['count'] . "\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
}

echo "=== 诊断完成 ===\n";
echo "\n建议:\n";
echo "1. 如果没有数据，需要通过后台发送测试消息\n";
echo "2. 检查前端调用的user_id是否与数据库中的user_id匹配\n";
echo "3. 检查wxapp_id是否正确\n";
