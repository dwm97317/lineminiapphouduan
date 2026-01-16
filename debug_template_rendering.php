<?php
/**
 * 调试模板渲染
 */

// 引入ThinkPHP框架
define('APP_PATH', __DIR__ . '/source/application/');
require __DIR__ . '/source/thinkphp/start.php';

use app\common\model\Setting as SettingModel;

echo "==================== 模板渲染调试 ====================\n\n";

$wxappId = 10001;
$config = SettingModel::getItem('line_messaging', $wxappId);
$template = $config['templates']['inwarehouse'];

echo "原始模板:\n";
$flexTemplate = html_entity_decode($template['flex_template']);
$flexArray = json_decode($flexTemplate, true);
echo json_encode($flexArray, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";

// 测试数据
$data = [
    'shop_name' => '武汉',
    'express_num' => '31966asdsadas',
    'entering_warehouse_time' => '2026-01-15 10:30:00',
    'weight' => '1.5',
    'remark' => '包裹已入库，可提交打包',
    'detail_url' => 'https://liff.line.me/2008873580-2xOUaLCU/package/detail?id=752127&rtype=10'
];

echo "测试数据:\n";
print_r($data);
echo "\n";

// 渲染模板
$json = json_encode($flexArray, JSON_UNESCAPED_UNICODE);

foreach ($data as $key => $value) {
    if (is_array($value) || is_object($value)) {
        continue;
    }
    $value = (string)$value;
    $json = str_replace("{{" . $key . "}}", $value, $json);
}

$rendered = json_decode($json, true);

echo "渲染后的模板:\n";
echo json_encode($rendered, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";

// 检查是否有空文本字段
echo "检查空文本字段:\n";
function checkEmptyText($arr, $path = '') {
    foreach ($arr as $key => $value) {
        $currentPath = $path ? "$path/$key" : $key;
        
        if (is_array($value)) {
            checkEmptyText($value, $currentPath);
        } elseif ($key === 'text' && (empty($value) || trim($value) === '')) {
            echo "❌ 发现空文本: $currentPath = '$value'\n";
        }
    }
}

checkEmptyText($rendered);

echo "\n==================== 完成 ====================\n";
