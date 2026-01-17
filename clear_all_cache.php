<?php
// 清空ThinkPHP所有缓存

echo "=== 开始清空缓存 ===\n\n";

// 1. 清空runtime/temp目录（模板缓存）
$tempDir = __DIR__ . '/runtime/temp';
if (is_dir($tempDir)) {
    echo "清空模板缓存: {$tempDir}\n";
    deleteDirectory($tempDir);
    mkdir($tempDir, 0755, true);
    echo "✓ 模板缓存已清空\n\n";
} else {
    echo "模板缓存目录不存在\n\n";
}

// 2. 清空runtime/cache目录（数据缓存）
$cacheDir = __DIR__ . '/runtime/cache';
if (is_dir($cacheDir)) {
    echo "清空数据缓存: {$cacheDir}\n";
    deleteDirectory($cacheDir);
    mkdir($cacheDir, 0755, true);
    echo "✓ 数据缓存已清空\n\n";
} else {
    echo "数据缓存目录不存在\n\n";
}

// 3. 清空runtime/log目录（日志文件）
$logDir = __DIR__ . '/runtime/log';
if (is_dir($logDir)) {
    echo "清空日志文件: {$logDir}\n";
    $files = glob($logDir . '/*.log');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    echo "✓ 日志文件已清空 (删除了 " . count($files) . " 个文件)\n\n";
} else {
    echo "日志目录不存在\n\n";
}

echo "=== 缓存清空完成 ===\n";
echo "请刷新浏览器页面并重新提交表单\n";

/**
 * 递归删除目录
 */
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return;
    }
    
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }
    
    // 不删除目录本身，只清空内容
    // rmdir($dir);
}
