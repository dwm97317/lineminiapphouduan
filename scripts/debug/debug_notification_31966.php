<?php
/**
 * 详细调试用户31966的通知问题
 */

// 引入ThinkPHP框架
define('APP_PATH', __DIR__ . '/source/application/');
require __DIR__ . '/source/thinkphp/start.php';

use app\common\model\Setting as SettingModel;
use app\common\model\User;
use app\common\library\line\LineMessage;

echo "==================== 详细调试用户31966 ====================\n\n";

$userId = 31966;

// 1. 获取用户信息
echo "【1】用户信息\n";
$user = User::where(['user_id' => $userId])->find();
echo "LINE ID：{$user['line_openid']}\n";
echo "wxapp_id：{$user['wxapp_id']}\n\n";

// 2. 获取LINE配置
echo "【2】LINE配置\n";
$config = SettingModel::getItem('line_messaging', $user['wxapp_id']);

echo "全局启用：" . ($config['is_enable'] == '1' ? '✅ 是' : '❌ 否') . "\n";
echo "Channel ID：" . ($config['channel_id'] ?: '❌ 未设置') . "\n";
echo "Access Token：" . (strlen($config['access_token']) > 0 ? '✅ 已设置' : '❌ 未设置') . "\n\n";

// 3. 检查入库模板
echo "【3】入库模板配置\n";
if (!isset($config['templates']['inwarehouse'])) {
    die("❌ 入库模板不存在！\n");
}

$template = $config['templates']['inwarehouse'];
echo "模板启用：" . ($template['is_enable'] == '1' ? '✅ 是' : '❌ 否') . "\n";
echo "Alt Text：" . ($template['alt_text'] ?: '未设置') . "\n";
echo "Flex Template：" . (isset($template['flex_template']) ? '✅ 已设置' : '❌ 未设置') . "\n";

if ($template['is_enable'] != '1') {
    die("\n❌ 模板未启用！\n");
}

// 检查flex_template是否为空
if (empty($template['flex_template'])) {
    die("\n❌ Flex模板内容为空！\n");
}

echo "Flex模板类型：" . gettype($template['flex_template']) . "\n";
if (is_string($template['flex_template'])) {
    echo "Flex模板长度：" . strlen($template['flex_template']) . " 字符\n";
    echo "Flex模板前100字符：" . substr($template['flex_template'], 0, 100) . "...\n";
}

echo "\n";

// 4. 测试好友关系验证
echo "【4】测试好友关系\n";
$lineMessage = new LineMessage(
    $config['channel_id'],
    $config['channel_secret'] ?? '',
    $config['access_token']
);

try {
    $profile = $lineMessage->getUserProfile($user['line_openid']);
    if ($profile && isset($profile['userId'])) {
        echo "✅ 用户是好友：{$profile['displayName']}\n\n";
    } else {
        echo "❌ 用户不是好友\n";
        echo "响应：" . json_encode($profile, JSON_UNESCAPED_UNICODE) . "\n\n";
    }
} catch (\Exception $e) {
    echo "❌ 好友验证失败：" . $e->getMessage() . "\n\n";
}

// 5. 测试模板渲染
echo "【5】测试模板渲染\n";
$testData = [
    'shop_name' => '泰国仓库',
    'express_num' => 'TEST123',
    'entering_warehouse_time' => date('Y-m-d H:i:s'),
    'weight' => 1.5,
    'remark' => '测试备注',
    'detail_url' => 'https://example.com'
];

// 解码模板
$flexTemplate = $template['flex_template'];
if (is_string($flexTemplate)) {
    $flexTemplate = html_entity_decode($flexTemplate);
    $flexTemplate = json_decode($flexTemplate, true);
}

if (!is_array($flexTemplate)) {
    echo "❌ 模板解析失败！\n";
    echo "模板类型：" . gettype($flexTemplate) . "\n";
} else {
    echo "✅ 模板解析成功\n";
    echo "模板结构：" . json_encode(array_keys($flexTemplate), JSON_UNESCAPED_UNICODE) . "\n";
    
    // 渲染模板
    $json = json_encode($flexTemplate, JSON_UNESCAPED_UNICODE);
    foreach ($testData as $key => $value) {
        $json = str_replace("{{" . $key . "}}", $value, $json);
    }
    $rendered = json_decode($json, true);
    
    if ($rendered) {
        echo "✅ 模板渲染成功\n";
    } else {
        echo "❌ 模板渲染失败\n";
    }
}

echo "\n";

// 6. 尝试发送测试消息
echo "【6】发送测试消息\n";
echo "准备发送...\n";

try {
    $messages = [
        [
            'type' => 'flex',
            'altText' => $template['alt_text'],
            'contents' => $rendered
        ]
    ];
    
    $result = $lineMessage->sendMultipleMessages($user['line_openid'], $messages);
    
    if ($result) {
        echo "✅ 消息发送成功！请检查LINE\n";
    } else {
        echo "❌ 消息发送失败\n";
    }
} catch (\Exception $e) {
    echo "❌ 发送异常：" . $e->getMessage() . "\n";
}

echo "\n==================== 调试完成 ====================\n";
