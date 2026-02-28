<?php
/**
 * 快速检查用户31966的LINE通知问题
 */

// 数据库连接
$conn = new mysqli('103.119.1.84', 'xinsuju', 'cJGzwZTDCLHzWXN4', 'xinsuju');

if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error . "\n");
}

$conn->set_charset('utf8');

$userId = 31966;

echo "==================== 快速诊断用户 {$userId} ====================\n\n";

// 1. 检查用户LINE绑定
echo "【1】检查用户LINE绑定\n";
$result = $conn->query("SELECT user_id, nickName, line_openid, wxapp_id FROM yoshop_user WHERE user_id = {$userId}");

if (!$result) {
    die("❌ 查询失败: " . $conn->error . "\n");
}

$user = $result->fetch_assoc();

if (!$user) {
    die("❌ 用户不存在\n");
}

echo "用户昵称：{$user['nickName']}\n";
echo "wxapp_id：{$user['wxapp_id']}\n";
echo "line_openid：" . ($user['line_openid'] ?: '❌ 未设置') . "\n";

if (empty($user['line_openid'])) {
    die("\n❌ 问题：用户没有绑定LINE账号！\n");
}

$lineUserId = $user['line_openid'];
echo "✅ LINE ID：{$lineUserId}\n\n";

// 2. 检查LINE配置
echo "【2】检查LINE消息配置\n";
$result = $conn->query("SELECT `values` FROM yoshop_setting WHERE `key` = 'line_messaging' AND wxapp_id = {$user['wxapp_id']}");
$setting = $result->fetch_assoc();

if (!$setting) {
    die("❌ LINE消息配置不存在\n");
}

$config = json_decode($setting['values'], true);

echo "全局启用：" . ($config['is_enable'] == '1' ? '✅ 是' : '❌ 否') . "\n";
echo "Channel ID：" . ($config['channel_id'] ? '✅ ' . $config['channel_id'] : '❌ 未设置') . "\n";
echo "Access Token：" . ($config['access_token'] ? '✅ 已设置' : '❌ 未设置') . "\n";

if ($config['is_enable'] != '1') {
    die("\n❌ 问题：LINE消息通知全局未启用！\n");
}

// 3. 检查入库通知模板
echo "\n【3】检查入库通知模板\n";
if (!isset($config['templates']['inwarehouse'])) {
    die("❌ 入库通知模板不存在\n");
}

$template = $config['templates']['inwarehouse'];
echo "模板启用：" . ($template['is_enable'] == '1' ? '✅ 是' : '❌ 否') . "\n";

if ($template['is_enable'] != '1') {
    die("\n❌ 问题：入库通知模板未启用！\n");
}

// 4. 检查好友关系
echo "\n【4】检查LINE OA好友关系\n";
$url = "https://api.line.me/v2/bot/profile/{$lineUserId}";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $config['access_token'],
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $profile = json_decode($response, true);
    echo "✅ 用户是好友：{$profile['displayName']}\n";
} else {
    echo "❌ 用户不是好友（HTTP {$httpCode}）\n";
    if ($response) {
        echo "响应：{$response}\n";
    }
    die("\n❌ 问题：用户未添加LINE OA为好友！\n");
}

// 5. 检查最近包裹
echo "\n【5】检查最近包裹记录\n";
$result = $conn->query("SELECT id, express_num, status, entering_warehouse_time, created_time 
                        FROM yoshop_package 
                        WHERE member_id = {$userId} 
                        ORDER BY id DESC LIMIT 3");

if ($result->num_rows == 0) {
    echo "该用户没有包裹记录\n";
} else {
    while ($pkg = $result->fetch_assoc()) {
        echo "- 包裹ID: {$pkg['id']}, 单号: {$pkg['express_num']}, 状态: {$pkg['status']}\n";
        echo "  入库时间: " . ($pkg['entering_warehouse_time'] ?: '未入库') . "\n";
    }
}

// 6. 关键问题检查
echo "\n【6】关键问题检查\n";
echo "❓ 后台录入包裹时是否调用了通知代码？\n";
echo "   检查方法：查看后台包裹录入的代码中是否有以下代码：\n";
echo "   \$messageService = new \\app\\common\\service\\message\\line\\Inwarehouse();\n";
echo "   \$messageService->send(\$data);\n\n";

echo "❓ 如果代码已添加，检查日志文件：\n";
$logPath = __DIR__ . '/runtime/log/' . date('Ym') . '/' . date('d') . '.log';
echo "   日志路径：{$logPath}\n";
if (file_exists($logPath)) {
    echo "   ✅ 日志文件存在\n";
    echo "   搜索关键词：LINE消息发送、user_id {$userId}\n";
} else {
    echo "   ❌ 日志文件不存在\n";
}

echo "\n==================== 诊断结论 ====================\n";
echo "根据以上检查，最可能的原因是：\n";
echo "❌ 后台录入包裹的代码中还没有集成通知触发代码\n\n";
echo "解决方案：\n";
echo "1. 找到后台包裹录入的控制器文件\n";
echo "2. 在包裹入库成功后添加通知触发代码\n";
echo "3. 参考文件：LINE_NOTIFICATION_INTEGRATION_GUIDE.md\n";

$conn->close();
