<?php
/**
 * 为推荐系统表添加 wxapp_id 字段
 */

$conn = new mysqli('103.119.1.84', 'xinsuju', 'cJGzwZTDCLHzWXN4', 'xinsuju', 3306);
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}
$conn->set_charset('utf8');

echo "【1】为推荐系统表添加 wxapp_id 字段\n\n";

$tables = [
    'yoshop_user_referral_code',
    'yoshop_referral_relation',
    'yoshop_referral_reward',
    'yoshop_referral_task_config',
    'yoshop_referral_reward_config',
    'yoshop_referral_system_config',
    'yoshop_referral_leaderboard'
];

foreach ($tables as $table) {
    echo "处理表: {$table}\n";
    
    // 检查字段是否已存在
    $result = $conn->query("SHOW COLUMNS FROM {$table} LIKE 'wxapp_id'");
    
    if ($result && $result->num_rows > 0) {
        echo "  ✓ wxapp_id 字段已存在\n";
    } else {
        // 添加 wxapp_id 字段
        $sql = "ALTER TABLE {$table} ADD COLUMN wxapp_id INT UNSIGNED NOT NULL DEFAULT 10001 COMMENT '小程序ID' AFTER id";
        
        if ($conn->query($sql)) {
            echo "  ✓ 成功添加 wxapp_id 字段\n";
            
            // 添加索引
            $indexSql = "ALTER TABLE {$table} ADD INDEX idx_wxapp_id (wxapp_id)";
            if ($conn->query($indexSql)) {
                echo "  ✓ 成功添加 wxapp_id 索引\n";
            } else {
                echo "  ⚠ 添加索引失败: " . $conn->error . "\n";
            }
        } else {
            echo "  ✗ 添加字段失败: " . $conn->error . "\n";
        }
    }
    echo "\n";
}

echo "【2】验证字段添加结果\n\n";
foreach ($tables as $table) {
    $result = $conn->query("SHOW COLUMNS FROM {$table} LIKE 'wxapp_id'");
    if ($result && $result->num_rows > 0) {
        $field = $result->fetch_assoc();
        echo "✓ {$table}: {$field['Field']} - {$field['Type']} - Default: {$field['Default']}\n";
    } else {
        echo "✗ {$table}: wxapp_id 字段不存在\n";
    }
}

$conn->close();
echo "\n完成！\n";
