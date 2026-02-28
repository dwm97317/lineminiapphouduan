<?php
/**
 * 发送真实包裹入库通知（带图片）
 * 包裹ID: 752123
 * LINE用户: Ud4e37d68c438cc70350957039add98d8
 */

echo "=== 发送真实包裹入库通知 ===\n\n";

// 加载项目数据库配置
$dbConfig = require __DIR__ . '/source/application/database.php';

$packageId = 752123;
$lineUserId = 'Ud4e37d68c438cc70350957039add98d8';
$wxappId = 10001;

echo "包裹ID: {$packageId}\n";
echo "LINE用户: {$lineUserId}\n";
echo "小程序ID: {$wxappId}\n\n";

// 连接数据库
echo "步骤 1: 连接数据库...\n";
try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['hostname']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbConfig['charset']}"
        ]
    );
    echo "✅ 数据库连接成功\n\n";
} catch (PDOException $e) {
    die("❌ 数据库连接失败: " . $e->getMessage() . "\n");
}

$prefix = $dbConfig['prefix'];

// 2. 获取包裹信息
echo "步骤 2: 获取包裹信息...\n";
$stmt = $pdo->prepare("
    SELECT 
        p.*,
        s.shop_name
    FROM {$prefix}package p
    LEFT JOIN {$prefix}store_shop s ON p.storage_id = s.shop_id
    WHERE p.id = ?
");
$stmt->execute([$packageId]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    die("❌ 错误: 未找到包裹 ID {$packageId}\n");
}

echo "✅ 包裹信息获取成功\n";
echo "   快递单号: {$package['express_num']}\n";
echo "   仓库名称: " . ($package['shop_name'] ?? '未知') . "\n";
echo "   重量: " . ($package['weight'] ?? '0') . " kg\n";
// 处理入库时间 - 可能是时间戳或日期字符串
$enteringTime = $package['entering_warehouse_time'] ?? null;
if ($enteringTime) {
    if (is_numeric($enteringTime) && $enteringTime > 1000000000) {
        // 是时间戳
        $enteringTimeStr = date('Y-m-d H:i:s', $enteringTime);
    } else {
        // 可能是日期字符串
        $enteringTimeStr = $enteringTime;
    }
} else {
    $enteringTimeStr = '未入库';
}
echo "   入库时间: {$enteringTimeStr}\n\n";

// 3. 获取包裹图片
echo "步骤 3: 获取包裹图片...\n";
$stmt = $pdo->prepare("
    SELECT 
        pi.image_id,
        pi.package_id,
        uf.file_id,
        uf.storage,
        uf.file_url,
        uf.file_name
    FROM {$prefix}package_image pi
    LEFT JOIN {$prefix}upload_file uf ON pi.image_id = uf.file_id
    WHERE pi.package_id = ?
    ORDER BY pi.id ASC
");
$stmt->execute([$packageId]);
$packageImages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$images = [];
if (!empty($packageImages)) {
    echo "✅ 找到 " . count($packageImages) . " 张包裹图片\n";
    
    foreach ($packageImages as $img) {
        // 构建完整的图片URL
        if (!empty($img['file_url']) && !empty($img['file_name'])) {
            // 拼接 file_url + file_name
            $imageUrl = $img['file_url'];
            if (substr($imageUrl, -1) !== '/') {
                $imageUrl .= '/';
            }
            $imageUrl .= $img['file_name'];
        } else {
            continue;
        }
        
        // 确保是完整URL
        if (strpos($imageUrl, 'http') !== 0) {
            // 如果是相对路径，添加域名
            $imageUrl = 'https://your-domain.com' . $imageUrl;
        }
        
        // 确保是HTTPS（LINE要求）
        $imageUrl = str_replace('http://', 'https://', $imageUrl);
        
        $images[] = $imageUrl;
        echo "   图片 " . count($images) . ": {$imageUrl}\n";
    }
} else {
    echo "⚠️  未找到包裹图片，将使用测试图片\n";
    // 使用测试图片
    $images = [
        'https://via.placeholder.com/800x600/1DB446/FFFFFF?text=Package+' . $packageId,
    ];
}
echo "\n";

// 4. 获取 LINE 消息配置
echo "步骤 4: 获取 LINE 消息配置...\n";
$stmt = $pdo->prepare("SELECT `values` FROM {$prefix}setting WHERE `key` = 'line_messaging' AND wxapp_id = ?");
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
echo "   Channel ID: {$config['channel_id']}\n\n";

// 5. 检查入库通知模板
echo "步骤 5: 检查入库通知模板配置...\n";
$template = $config['templates']['inwarehouse'] ?? null;

if (!$template) {
    die("❌ 错误: 未找到入库通知模板配置\n");
}

if (empty($template['is_enable']) || $template['is_enable'] != '1') {
    die("❌ 错误: 入库通知模板未启用\n");
}

echo "✅ 模板配置正常\n";
echo "   发送图片: " . (isset($template['send_images']) && $template['send_images'] == '1' ? '是' : '否') . "\n";
echo "   最大图片数: " . ($template['max_images'] ?? 3) . "\n\n";

// 6. 准备消息数据
echo "步骤 6: 准备消息数据...\n";
// 处理入库时间
$enteringTime = $package['entering_warehouse_time'] ?? null;
if ($enteringTime) {
    if (is_numeric($enteringTime) && $enteringTime > 1000000000) {
        $enteringTimeStr = date('Y-m-d H:i:s', $enteringTime);
    } else {
        $enteringTimeStr = $enteringTime;
    }
} else {
    $enteringTimeStr = date('Y-m-d H:i:s');
}

$data = [
    'shop_name' => $package['shop_name'] ?? '仓库',
    'express_num' => $package['express_num'] ?? '未知',
    'entering_warehouse_time' => $enteringTimeStr,
    'weight' => $package['weight'] ?? '0',
    'remark' => !empty($package['remark']) ? $package['remark'] : '包裹已入库，请及时查看',
    'detail_url' => 'https://example.com/package/detail?id=' . $packageId
];

echo "✅ 消息数据准备完成\n";
echo "   快递单号: {$data['express_num']}\n";
echo "   仓库: {$data['shop_name']}\n";
echo "   重量: {$data['weight']} kg\n";
echo "   备注: {$data['remark']}\n\n";

// 7. 渲染 Flex Message 模板
echo "步骤 7: 渲染 Flex Message 模板...\n";
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
foreach ($data as $key => $value) {
    // 确保值不为空
    if ($value === null || $value === '') {
        $value = '-';
    }
    $json = str_replace("{{" . $key . "}}", $value, $json);
}

// 清理可能剩余的未替换变量（替换为空格或默认值）
$json = preg_replace('/\{\{[^}]+\}\}/', '-', $json);

$flexContents = json_decode($json, true);

echo "✅ Flex Message 模板渲染成功\n\n";

// 8. 准备消息数组
echo "步骤 8: 准备消息数组...\n";
$messages = [];

// 添加 Flex Message
$messages[] = [
    'type' => 'flex',
    'altText' => $template['alt_text'],
    'contents' => $flexContents
];
echo "✅ 已添加 Flex Message\n";

// 添加包裹图片（如果启用）
$imageCount = 0;
if (isset($template['send_images']) && $template['send_images'] == '1' && !empty($images)) {
    $maxImages = isset($template['max_images']) ? (int)$template['max_images'] : 3;
    
    foreach ($images as $imageUrl) {
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
    echo "⚠️  图片发送未启用或无图片，仅发送 Flex Message\n\n";
}

// 9. 发送消息到 LINE API
echo "步骤 9: 发送消息到 LINE API...\n";
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
    echo "已发送包裹 {$packageId} 的入库通知到 LINE 用户 {$lineUserId}\n";
    echo "消息内容:\n";
    echo "  - 📦 包裹入库通知（Flex Message）\n";
    echo "  - 快递单号: {$package['express_num']}\n";
    echo "  - 仓库: " . ($package['shop_name'] ?? '未知') . "\n";
    echo "  - 重量: " . ($package['weight'] ?? '0') . " kg\n";
    if ($imageCount > 0) {
        echo "  - {$imageCount} 张包裹图片\n";
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
