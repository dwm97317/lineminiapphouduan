<?php
/**
 * 获取用户 Token
 * 
 * 方法1: 从数据库查询用户的 token
 * 方法2: 生成新的 token（如果需要）
 */

// 读取数据库配置
$config = include __DIR__ . '/source/application/database.php';

// 连接数据库
$mysqli = new mysqli(
    $config['hostname'],
    $config['username'],
    $config['password'],
    $config['database'],
    $config['hostport']
);

if ($mysqli->connect_error) {
    die("数据库连接失败: " . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

echo "=== 获取用户 Token ===\n\n";

// 1. 查询所有用户
echo "1. 查询用户列表...\n";
$result = $mysqli->query("
    SELECT user_id, nickName, avatarUrl, mobile, create_time 
    FROM yoshop_user 
    WHERE is_delete = 0 
    ORDER BY user_id ASC 
    LIMIT 10
");

if (!$result) {
    die("查询失败: " . $mysqli->error);
}

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

if (empty($users)) {
    die("⚠️ 没有找到用户\n");
}

echo "找到 " . count($users) . " 个用户:\n\n";

foreach ($users as $index => $user) {
    echo ($index + 1) . ". 用户ID: {$user['user_id']}\n";
    echo "   昵称: {$user['nickName']}\n";
    echo "   手机: " . ($user['mobile'] ?: '未绑定') . "\n";
    echo "   注册时间: " . date('Y-m-d H:i:s', $user['create_time']) . "\n";
    echo "\n";
}

// 2. 选择用户
echo "请选择用户编号 (1-" . count($users) . "): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
$selectedIndex = intval($line) - 1;

if ($selectedIndex < 0 || $selectedIndex >= count($users)) {
    die("❌ 无效的选择\n");
}

$selectedUser = $users[$selectedIndex];
echo "\n已选择用户: {$selectedUser['nickName']} (ID: {$selectedUser['user_id']})\n\n";

// 3. 生成 Token
echo "3. 生成 Token...\n";

// Token 生成逻辑（参考后端的 token 生成方式）
// 通常是 JWT 或者简单的加密字符串
// 这里使用简单的方式：user_id + 随机字符串 + 时间戳

$tokenData = [
    'user_id' => $selectedUser['user_id'],
    'wxapp_id' => 10001, // 根据实际情况修改
    'timestamp' => time(),
    'random' => bin2hex(random_bytes(16))
];

// 使用 base64 编码
$token = base64_encode(json_encode($tokenData));

echo "✅ Token 生成成功!\n\n";
echo "=== Token 信息 ===\n";
echo "用户ID: {$selectedUser['user_id']}\n";
echo "昵称: {$selectedUser['nickName']}\n";
echo "Token: {$token}\n\n";

// 4. 保存到文件
$tokenFile = __DIR__ . '/test_token.txt';
file_put_contents($tokenFile, $token);
echo "✅ Token 已保存到: {$tokenFile}\n\n";

// 5. 提供使用说明
echo "=== 使用方法 ===\n\n";

echo "方法1: 在浏览器测试页面中使用\n";
echo "  1. 访问: http://localhost/test_coupon_api.html\n";
echo "  2. 在 '用户 Token' 输入框中粘贴上面的 Token\n";
echo "  3. 点击测试按钮\n\n";

echo "方法2: 在 PHP 脚本中使用\n";
echo "  编辑 test_coupon_receive.php，将 \$token 变量设置为:\n";
echo "  \$token = '{$token}';\n\n";

echo "方法3: 使用 curl 命令测试\n";
echo "  curl -X GET 'http://localhost/api/coupon/lists' \\\n";
echo "       -H 'token: {$token}'\n\n";

// 6. 验证 Token（可选）
echo "=== 验证 Token ===\n";
echo "是否立即验证 Token? (y/n): ";
$line = trim(fgets($handle));

if (strtolower($line) === 'y') {
    echo "\n正在验证...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost/api/coupon/lists");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'token: ' . $token
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP 状态码: {$http_code}\n";
    
    if ($http_code == 200) {
        $data = json_decode($response, true);
        if ($data['code'] == 1) {
            echo "✅ Token 验证成功!\n";
            echo "获取到 " . count($data['data']['list']) . " 个优惠券\n";
        } else {
            echo "⚠️ Token 可能无效: {$data['msg']}\n";
        }
    } else {
        echo "❌ 请求失败\n";
    }
}

fclose($handle);

echo "\n=== 完成 ===\n";

$mysqli->close();
