<?php
/**
 * 查看最新的日志文件
 */

$logDir = __DIR__ . '/source/runtime/log';

if (!is_dir($logDir)) {
    echo "日志目录不存在: {$logDir}\n";
    exit;
}

// 获取最新的日志文件
$files = glob($logDir . '/*.log');
if (empty($files)) {
    echo "没有找到日志文件\n";
    exit;
}

// 按修改时间排序
usort($files, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

$latestFile = $files[0];
echo "=== 最新日志文件: " . basename($latestFile) . " ===\n";
echo "修改时间: " . date('Y-m-d H:i:s', filemtime($latestFile)) . "\n\n";

// 读取最后 100 行
$lines = file($latestFile);
$totalLines = count($lines);
$startLine = max(0, $totalLines - 100);

echo "显示最后 " . ($totalLines - $startLine) . " 行:\n";
echo str_repeat('=', 80) . "\n";

for ($i = $startLine; $i < $totalLines; $i++) {
    echo $lines[$i];
}
