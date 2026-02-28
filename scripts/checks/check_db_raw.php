<?php
define('APP_PATH', __DIR__ . '/source/application/');
require __DIR__ . '/source/thinkphp/start.php';

use think\Db;

$setting = Db::name('setting')
    ->where(['key' => 'line_messaging', 'wxapp_id' => 10001])
    ->find();

echo "数据库原始数据:\n";
echo "- key: {$setting['key']}\n";
echo "- wxapp_id: {$setting['wxapp_id']}\n";
echo "- update_time: {$setting['update_time']}\n";
echo "- values 类型: " . gettype($setting['values']) . "\n";
echo "- values 长度: " . strlen($setting['values']) . "\n\n";

echo "values 前500字符:\n";
echo substr($setting['values'], 0, 500) . "\n\n";

$config = json_decode($setting['values'], true);
echo "JSON 解码结果: " . (is_array($config) ? 'Array' : gettype($config)) . "\n";

if (is_array($config)) {
    echo "顶级键: " . implode(', ', array_keys($config)) . "\n";
}
