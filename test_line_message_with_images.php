<?php
/**
 * 测试 LINE 消息发送（带图片）
 * 直接发送入库通知+包裹图片给指定用户
 */

// 引入 ThinkPHP 框架
define('APP_PATH', __DIR__ . '/source/application/');
define('ROOT_PATH', __DIR__ . '/');
require __DIR__ . '/source/thinkphp/start.php';

echo "=== LINE 消息发送测试（带图片） ===\n\n";

// 测试参数
$lineUserId = 'Ud4e37d68c438cc70350957039add98d8';
$wxappId = 10001; // 默认小程序ID

echo "目标用户: {$lineUserId}\n";
echo "小程序ID: {$wxappId}\n\n";

// 1. 获取 LINE 消息配置
echo "步骤 1: 获取 LINE 消息配置...\n";
$config = SettingModel::getItem('line_messaging', $wxappId);

if (empty($config)) {
    die("❌ 错误: 未找到 LINE 消息配置\n");
}

if (empty($config['is_enable']) || $config['is_enable'] != '1') {
    die("❌ 错误: LINE 消息通知未启用\n");
}

if (empty($config['channel_id']) || empty($config['access_token'])) {
    die("❌ 错误: LINE 配置不完整（缺少 Channel ID 或 Access Token）\n");
}

echo "✅ 配置加载成功\n";
echo "   Channel ID: {$config['channel_id']}\n";
echo "   Access Token: " . substr($config['access_token'], 0, 20) . "...\n\n";

// 2. 检查入库通知模板配置
echo "步骤 2: 检查入库通知模板配置...\n";
$template = $config['templates']['inwarehouse'] ?? null;

if (!$template) {
    die("❌ 错误: 未找到入库通知模板配置\n");
}

if (empty($template['is_enable']) || $template['is_enable'] != '1') {
    die("❌ 错误: 入库通知模板未启用\n");
}

echo "✅ 模板配置正常\n";
echo "   模板名称: {$template['name']}\n";
echo "   替代文本: {$template['alt_text']}\n";
echo "   发送图片: " . (isset($template['send_images']) && $template['send_images'] == '1' ? '是' : '否') . "\n";
echo "   最大图片数: " . ($template['max_images'] ?? 3) . "\n\n";

// 3. 创建 LINE 消息实例
echo "步骤 3: 创建 LINE 消息实例...\n";
$lineMessage = new LineMessage(
    $config['channel_id'],
    $config['channel_secret'] ?? '',
    $config['access_token']
);
echo "✅ LINE 消息实例创建成功\n\n";

// 4. 准备测试数据
echo "步骤 4: 准备测试数据...\n";
$testData = [
    'shop_name' => '泰国仓库',
    'express_num' => 'TEST' . date('YmdHis'),
    'entering_warehouse_time' => date('Y-m-d H:i:s'),
    'weight' => 1.5,
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
    $flexTemplate = html_entity_decode($flexTemplate);
    $flexTemplate = json_decode($flexTemplate, true);
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
if (isset($template['send_images']) && $template['send_images'] == '1') {
    $testImages = [
        'https://via.placeholder.com/800x600/1DB446/FFFFFF?text=Package+Photo+1',
        'https://via.placeholder.com/800x600/0066CC/FFFFFF?text=Package+Photo+2',
        'https://via.placeholder.com/800x600/FF6B00/FFFFFF?text=Package+Photo+3',
    ];
    
    $maxImages = isset($template['max_images']) ? (int)$template['max_images'] : 3;
    $imageCount = 0;
    
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

// 7. 发送消息
echo "步骤 7: 发送消息到 LINE...\n";
echo "正在发送...\n";

try {
    $result = $lineMessage->sendMultipleMessages($lineUserId, $messages);
    
    if ($result) {
        echo "\n✅✅✅ 消息发送成功！✅✅✅\n\n";
        echo "请检查 LINE 用户 {$lineUserId} 的消息\n";
        echo "应该收到:\n";
        echo "  1. 📦 包裹入库通知（Flex Message）\n";
        if (isset($template['send_images']) && $template['send_images'] == '1') {
            echo "  2. " . $imageCount . " 张包裹图片\n";
        }
    } else {
        echo "\n❌ 消息发送失败\n";
        echo "请检查:\n";
        echo "  1. LINE Access Token 是否有效\n";
        echo "  2. LINE User ID 是否正确\n";
        echo "  3. 网络连接是否正常\n";
    }
} catch (\Exception $e) {
    echo "\n❌ 发送异常: " . $e->getMessage() . "\n";
    echo "错误详情:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== 测试完成 ===\n";
