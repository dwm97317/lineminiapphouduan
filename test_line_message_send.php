<?php
/**
 * 测试 LINE 消息发送
 */

// 引入框架
require __DIR__ . '/source/application/common.php';

use app\common\model\Setting as SettingModel;
use app\common\library\line\LineMessage;

// 测试参数
$wxappId = 10001;
$lineUserId = 'Ud4e37d68c438cc70350957039add98d8';
$messageType = 'inwarehouse';

echo "<h2>LINE 消息发送测试</h2>";

// 1. 获取配置
echo "<h3>1. 获取配置</h3>";
$config = SettingModel::getItem('line_messaging', $wxappId);

if (empty($config)) {
    echo "<p style='color:red'>❌ 配置不存在</p>";
    exit;
}

echo "<p>✅ 配置已加载</p>";
echo "<ul>";
echo "<li>is_enable: " . ($config['is_enable'] ?? 'N/A') . "</li>";
echo "<li>channel_id: " . ($config['channel_id'] ?? 'N/A') . "</li>";
echo "<li>access_token: " . (isset($config['access_token']) ? substr($config['access_token'], 0, 20) . '...' : 'N/A') . "</li>";
echo "</ul>";

// 2. 检查模板
echo "<h3>2. 检查模板</h3>";
$template = $config['templates'][$messageType] ?? null;

if (empty($template)) {
    echo "<p style='color:red'>❌ 模板不存在</p>";
    exit;
}

echo "<p>✅ 模板已找到</p>";
echo "<ul>";
echo "<li>is_enable: " . ($template['is_enable'] ?? 'N/A') . "</li>";
echo "<li>alt_text: " . ($template['alt_text'] ?? 'N/A') . "</li>";
echo "<li>flex_template 类型: " . gettype($template['flex_template']) . "</li>";
echo "</ul>";

// 3. 解析 flex_template
echo "<h3>3. 解析 Flex Template</h3>";
$flexTemplate = $template['flex_template'];

if (is_string($flexTemplate)) {
    echo "<p>flex_template 是字符串，尝试解码...</p>";
    // 先解码 HTML 实体
    $flexTemplate = html_entity_decode($flexTemplate);
    echo "<p>解码后的字符串（前100字符）: " . htmlspecialchars(substr($flexTemplate, 0, 100)) . "...</p>";
    
    $flexTemplate = json_decode($flexTemplate, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "<p style='color:red'>❌ JSON 解码失败: " . json_last_error_msg() . "</p>";
        exit;
    }
    echo "<p>✅ JSON 解码成功</p>";
} else if (is_array($flexTemplate)) {
    echo "<p>✅ flex_template 已经是数组</p>";
} else {
    echo "<p style='color:red'>❌ flex_template 类型错误: " . gettype($flexTemplate) . "</p>";
    exit;
}

// 4. 准备测试数据
echo "<h3>4. 准备测试数据</h3>";
$testData = [
    'shop_name' => '泰国仓库',
    'express_num' => 'TEST' . date('YmdHis'),
    'entering_warehouse_time' => date('Y-m-d H:i:s'),
    'weight' => 1.5,
    'remark' => '这是一条测试消息',
    'detail_url' => 'https://example.com/package/detail?id=999'
];

echo "<pre>";
print_r($testData);
echo "</pre>";

// 5. 渲染模板
echo "<h3>5. 渲染模板</h3>";
$json = json_encode($flexTemplate, JSON_UNESCAPED_UNICODE);
foreach ($testData as $key => $value) {
    $json = str_replace("{{" . $key . "}}", $value, $json);
}
$renderedTemplate = json_decode($json, true);

echo "<p>✅ 模板渲染完成</p>";
echo "<details><summary>查看渲染后的模板</summary><pre>";
print_r($renderedTemplate);
echo "</pre></details>";

// 6. 发送消息
echo "<h3>6. 发送消息</h3>";

try {
    $lineMessage = new LineMessage(
        $config['channel_id'],
        $config['channel_secret'] ?? '',
        $config['access_token']
    );
    
    echo "<p>✅ LineMessage 实例已创建</p>";
    echo "<p>发送到用户: $lineUserId</p>";
    echo "<p>替代文本: " . $template['alt_text'] . "</p>";
    
    $result = $lineMessage->sendFlexMessage($lineUserId, $template['alt_text'], $renderedTemplate);
    
    if ($result) {
        echo "<p style='color:green'>✅ 消息发送成功！</p>";
    } else {
        echo "<p style='color:red'>❌ 消息发送失败</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ 异常: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h3>完成</h3>";
