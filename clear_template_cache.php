<?php
/**
 * 清除模板缓存
 */

$runtimePath = __DIR__ . '/runtime/temp';

function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }
    
    return rmdir($dir);
}

echo "清除模板缓存...\n";

if (deleteDirectory($runtimePath)) {
    echo "✓ 模板缓存已清除\n";
    
    // 重新创建目录
    mkdir($runtimePath, 0755, true);
    echo "✓ 缓存目录已重建\n";
} else {
    echo "✗ 清除失败\n";
}
