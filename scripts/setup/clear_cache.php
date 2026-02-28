<?php
/**
 * 清除PHP缓存脚本
 * 用于解决 "Cannot redeclare" 错误
 */

echo "=== 清除PHP缓存 ===\n\n";

// 1. 清除 OPcache
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "✓ OPcache 已清除\n";
    } else {
        echo "✗ OPcache 清除失败\n";
    }
} else {
    echo "- OPcache 未启用\n";
}

// 2. 清除 APC 缓存
if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    echo "✓ APC 缓存已清除\n";
} else {
    echo "- APC 未启用\n";
}

// 3. 清除文件状态缓存
clearstatcache();
echo "✓ 文件状态缓存已清除\n";

// 4. 清除 ThinkPHP 运行时缓存
$runtimePath = __DIR__ . '/runtime';
if (is_dir($runtimePath)) {
    $deleted = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($runtimePath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            if (unlink($file->getPathname())) {
                $deleted++;
            }
        }
    }
    echo "✓ ThinkPHP 运行时缓存已清除 ($deleted 个文件)\n";
} else {
    echo "- ThinkPHP runtime 目录不存在\n";
}

echo "\n=== 缓存清除完成 ===\n";
echo "\n请刷新浏览器重试\n";
