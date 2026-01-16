<?php
/**
 * 独立测试脚本：发送 LINE 消息（带图片）
 * 不依赖 ThinkPHP 框架，直接调用 LINE API
 */

echo "=== LINE 消息发送测试（带图片） ===\n\n";

// 配置信息（从数据库获取）
$dbHost = '103.119.1.84';
$dbName = 'xinsuju';
$dbUser = 'xinsuju';
$dbPass = 'cJGzwZTDCLHzWXN4';
$dbPrefix = 'yoshop_';

// 测试参数
$lineUserId = 'Ud4e37d68c438cc70350957039add98d8';
$wxappId = 10001;

echo "目标用户: {$lineUserId}\n";
echo "小程序ID: {$wxappId}\n\n";

// 1. 连接数据库
echo "步骤 1: 连接数据库...\n";
try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ 数据库连接成功\n\n";
} catch (PDOException $e) {
    die("❌ 数据库连接失败: " . $e->getMessage() . "\n");
}

// 2. 获取 LINE 消息配置
echo "步骤 2: 获取 LINE 消息配置...\n";
$stmt = $pdo->prepare("SELECT `values` FROM {$dbPrefix}setting WHERE `key` = 'line_messaging' AND wxapp_id = ?");
$stmt->execute([$wxappId]);
$configRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$configRow) {
    die("❌ 错误: 未找到 LINE 消息配置\n");
}

$config = json_decode($configRow['values'], true);

if (empty($config['is_enable']) || $config['is_enable'] != '1') {
    die("❌ 错误: LINE 消息通知未启用\n");
}

if (empty($config['channel_id']) || empty($config['access_token'])) {
    die("❌ 错误: LINE 配置不完整\n");
}

echo "✅ 配置加载成功\n";
echo "   Channel ID: {$config['channel_id']}\n";
echo "   Access Token: " . substr($config['access_token'], 0, 20) . "...\n\n";

// 3. 检查入库通知模板
echo "步骤 3: 检查入库通知模板配置...\n";
$template = $config['templates']['inwarehouse'] ?? null;

if (!$template) {
    die("❌ 错误: 未找到入库通知模板配置\n");
}

if (empty($template['is_enable']) || $template['is_enable'] != '1') {
    die("❌ 错误: 入库通知模板未启用\n");
}

echo "✅ 模板配置正常\n";
echo "   模板名称: " . ($template['name'] ?? '入库通知') . "\n";
echo "   替代文本: {$template['alt_text']}\n";
echo "   发送图片: " . (isset($template['send_images']) && $template['send_images'] == '1' ? '是' : '否') . "\n";
echo "   最大图片数: " . ($template['max_images'] ?? 3) . "\n\n";

// 4. 准备测试数据
echo "步骤 4: 准备测试数据...\n";
$testData = [
    'shop_name' => '泰国仓库',
    'express_num' => 'TEST' . date('YmdHis'),
    'entering_warehouse_time' => date('Y-m-d H:i:s'),
    'weight' => '1.5',
    'remark' => '这是一条测试消息（带图片）',
    'detail_url' => 'https://example.com/package/detail?id=999'
];

echo "✅ 测试数据准备完成\n";
echo "   仓库名称: {$testData['shop_name']}\n";
echo "   快递单号: {$testData['express_num']}\n";
echo "   入库时间: {$testData['entering_warehouse_time']}\n";
echo "   重量: {$testData['weight']} kg\n\n";

// 5. 渲染 Flex Message 模板
echo "步骤 5: 渲染 Flex Message 模板...\n";
$flexTemplate = $template['flex_template'];

// 解码 HTML 实体
if (is_string($flexTemplate)) {
    $decoded = $flexTemplate;
    for ($i = 0; $i < 5; $i++) {
        $temp = html_entity_decode($decoded);
        if ($temp === $decoded) break;
        $decoded = $temp;
    }
    $flexTemplate = json_decode($decoded, true);
}

if (!is_array($flexTemplate)) {
    die("❌ 错误: Flex 模板解析失败\n");
}

// 替换变量
$json = json_encode($flexTemplate, JSON_UNESCAPED_UNICODE);
foreach ($testData as $key => $value) {
    $json = str_replace("{{" . $key . "}}", $value, $json);
}
$flexContents = json_decode($json, true);

echo "✅ Flex Message 模板渲染成功\n\n";

// 6. 准备消息数组
echo "步骤 6: 准备消息数组...\n";
$messages = [];

// 添加 Flex Message
$messages[] = [
    'type' => 'flex',
    'altText' => $template['alt_text'],
    'contents' => $flexContents
];
echo "✅ 已添加 Flex Message\n";

// 添加测试图片（如果启用）
$imageCount = 0;
if (isset($template['send_images']) && $template['send_images'] == '1') {
    $testImages = [
        'https://via.placeholder.com/800x600/1DB446/FFFFFF?text=Package+Photo+1',
        'https://via.placeholder.com/800x600/0066CC/FFFFFF?text=Package+Photo+2',
        'https://via.placeholder.com/800x600/FF6B00/FFFFFF?text=Package+Photo+3',
    ];
    
    $maxImages = isset($template['max_images']) ? (int)$template['max_images'] : 3;
    
    foreach ($testImages as $imageUrl) {
        if ($imageCount >= $maxImages || $imageCount >= 4) break; // LINE 限制
        
        $messages[] = [
            'type' => 'image',
            'originalContentUrl' => $imageUrl,
            'previewImageUrl' => $imageUrl
        ];
        $imageCount++;
        echo "✅ 已添加图片 {$imageCount}: {$imageUrl}\n";
    }
    
    echo "\n总共准备了 " . (count($messages)) . " 条消息（1条Flex + {$imageCount}张图片）\n\n";
} else {
    echo "⚠️  图片发送未启用，仅发送 Flex Message\n\n";
}

// 7. 发送消息到 LINE API
echo "步骤 7: 发送消息到 LINE API...\n";
echo "正在发送...\n";

$url = 'https://api.line.me/v2/bot/message/push';
$data = [
    'to' => $lineUserId,
    'messages' => $messages
];

$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $config['access_token']
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "\nHTTP 状态码: {$httpCode}\n";

if ($httpCode == 200) {
    echo "\n✅✅✅ 消息发送成功！✅✅✅\n\n";
    echo "请检查 LINE 用户 {$lineUserId} 的消息\n";
    echo "应该收到:\n";
    echo "  1. 📦 包裹入库通知（Flex Message）\n";
    if ($imageCount > 0) {
        echo "  2. {$imageCount} 张包裹图片\n";
    }
    echo "\n响应内容: {$response}\n";
} else {
    echo "\n❌ 消息发送失败\n";
    echo "HTTP 状态码: {$httpCode}\n";
    echo "错误信息: {$error}\n";
    echo "响应内容: {$response}\n";
    
    $result = json_decode($response, true);
    if (isset($result['message'])) {
        echo "\nLINE API 错误: {$result['message']}\n";
        if (isset($result['details'])) {
            echo "详细信息: " . json_encode($result['details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
}

echo "\n=== 测试完成 ===\n";
