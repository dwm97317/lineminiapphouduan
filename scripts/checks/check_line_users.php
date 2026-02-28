<?php
// 检查 LINE 用户重复问题
$mysqli = new mysqli('103.119.1.84', 'xinsuju', 'cJGzwZTDCLHzWXN4', 'xinsuju');
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

// 查询 LINE 用户
$lineOpenId = 'Ud4e37d68c438cc70350957039add98d8';
$result = $mysqli->query("SELECT user_id, nickName, line_openid, open_id, create_time FROM yoshop_user WHERE line_openid = '$lineOpenId' ORDER BY create_time DESC");

echo "LINE 用户记录 (line_openid = $lineOpenId):\n";
echo "===========================================\n";
$count = 0;
while ($row = $result->fetch_assoc()) {
    $count++;
    echo "[$count] User ID: {$row['user_id']}, Nick: {$row['nickName']}, Open ID: {$row['open_id']}, Created: {$row['create_time']}\n";
}

echo "\n总共找到 $count 条记录\n";

// 检查 user_binding 表
$result2 = $mysqli->query("SELECT id, user_id, openid, platform, create_time FROM yoshop_user_binding WHERE openid = '$lineOpenId' AND platform = 'LINE' ORDER BY create_time DESC");

echo "\nuser_binding 表中的 LINE 记录:\n";
echo "===========================================\n";
$count2 = 0;
while ($row = $result2->fetch_assoc()) {
    $count2++;
    echo "[$count2] ID: {$row['id']}, User ID: {$row['user_id']}, OpenID: {$row['openid']}, Platform: {$row['platform']}, Created: {$row['create_time']}\n";
}

echo "\n总共找到 $count2 条记录\n";

$mysqli->close();
