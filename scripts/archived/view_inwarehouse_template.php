<?php
/**
 * 查看入库通知模板
 */

define('APP_PATH', __DIR__ . '/source/application/');
require __DIR__ . '/source/thinkphp/start.php';

use app\common\model\Setting as SettingModel;

$wxappId = 10001;
$config = SettingModel::getItem('line_messaging', $wxappId);

if (empty($config['templates']['inwarehouse'])) {
    die("❌ 未找到入库通知模板\n");
}

$template = $config['templates']['inwarehouse'];

echo "==================== 入库通知模板 ====================\n\n";

echo "【1】基本信息\n";
echo "- 名称: {$template['name']}\n";
echo "- 启用状态: {$template['is_enable']}\n";
echo "- 发送图片: {$template['send_images']}\n";
echo "- 最大图片数: {$template['max_images']}\n\n";

echo "【2】模板变量\n";
if (is_array($template['variables'])) {
    foreach ($template['variables'] as $var) {
        echo "- {$var}\n";
    }
} else {
    $variables = json_decode($template['variables'], true);
    if (is_array($variables)) {
        foreach ($variables as $var) {
            echo "- {$var}\n";
        }
    } else {
        echo $template['variables'] . "\n";
    }
}

echo "\n【3】Flex Message 模板\n";
$flexTemplate = html_entity_decode($template['flex_template']);
$flexArray = json_decode($flexTemplate, true);

if ($flexArray) {
    echo json_encode($flexArray, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
} else {
    echo "❌ 模板解析失败\n";
    echo "原始内容: " . substr($template['flex_template'], 0, 200) . "...\n";
}

echo "\n==================== 完成 ====================\n";
