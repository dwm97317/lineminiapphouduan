<?php
/**
 * 捕获表单提交数据
 * 将此文件内容添加到 Referral.php 的 saveConfig() 方法开头
 */

// 在 saveConfig() 方法开头添加这段代码来记录提交的数据
$logFile = __DIR__ . '/../../../form_submit_log.txt';
$logData = [
    'time' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'post_data' => $_POST,
    'raw_input' => file_get_contents('php://input'),
];

file_put_contents($logFile, print_r($logData, true) . "\n\n", FILE_APPEND);

echo "表单数据已记录到: $logFile\n";
echo "\n提交的数据:\n";
print_r($_POST);
