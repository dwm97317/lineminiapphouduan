<?php
/**
 * 检查 GD 扩展 DLL 文件
 */

echo "========================================\n";
echo "GD 扩展 DLL 文件检查\n";
echo "========================================\n\n";

// 获取扩展目录
$extDir = ini_get('extension_dir');
echo "扩展目录: {$extDir}\n\n";

// 如果是相对路径，转换为绝对路径
if (!preg_match('/^[a-z]:/i', $extDir)) {
    $phpDir = dirname(php_ini_loaded_file());
    $extDir = $phpDir . DIRECTORY_SEPARATOR . $extDir;
}

echo "绝对路径: {$extDir}\n\n";

// 检查目录是否存在
if (!is_dir($extDir)) {
    echo "❌ 错误: 扩展目录不存在\n";
    exit(1);
}

echo "✅ 扩展目录存在\n\n";

// 检查 GD 相关的 DLL 文件
$gdFiles = [
    'php_gd2.dll',
    'php_gd.dll',
    'gd.dll',
    'php_gd2.so',
    'gd.so'
];

echo "查找 GD 扩展文件:\n";
$found = false;

foreach ($gdFiles as $file) {
    $fullPath = $extDir . DIRECTORY_SEPARATOR . $file;
    if (file_exists($fullPath)) {
        echo "   ✅ 找到: {$file}\n";
        echo "      路径: {$fullPath}\n";
        echo "      大小: " . number_format(filesize($fullPath)) . " 字节\n";
        $found = true;
    } else {
        echo "   ❌ 未找到: {$file}\n";
    }
}

echo "\n";

if (!$found) {
    echo "========================================\n";
    echo "⚠️ 未找到 GD 扩展 DLL 文件\n";
    echo "========================================\n\n";
    
    echo "解决方案:\n\n";
    
    echo "1. 检查 PHP 版本和架构:\n";
    echo "   PHP 版本: " . PHP_VERSION . "\n";
    echo "   架构: " . (PHP_INT_SIZE === 8 ? 'x64' : 'x86') . "\n\n";
    
    echo "2. 下载对应的 PHP 扩展:\n";
    echo "   访问: https://windows.php.net/download/\n";
    echo "   下载与你的 PHP 版本匹配的完整包\n\n";
    
    echo "3. 或者重新安装 PHP:\n";
    echo "   确保选择包含所有扩展的完整版本\n\n";
    
    echo "4. 或者使用 SVG 格式（临时方案）:\n";
    echo "   代码已经修改为支持 SVG 格式，不需要 GD 扩展\n\n";
} else {
    echo "========================================\n";
    echo "✅ 找到 GD 扩展文件\n";
    echo "========================================\n\n";
    
    echo "现在需要重启 PHP 服务以加载扩展\n\n";
    
    echo "重启方法:\n";
    echo "1. 如果使用 Apache:\n";
    echo "   - 打开服务管理器 (services.msc)\n";
    echo "   - 找到 Apache 服务\n";
    echo "   - 右键 -> 重新启动\n\n";
    
    echo "2. 如果使用 Nginx + PHP-FPM:\n";
    echo "   - 打开服务管理器 (services.msc)\n";
    echo "   - 找到 PHP-FPM 服务\n";
    echo "   - 右键 -> 重新启动\n\n";
    
    echo "3. 如果使用宝塔面板:\n";
    echo "   - 登录宝塔面板\n";
    echo "   - 软件商店 -> PHP -> 设置 -> 服务 -> 重启\n\n";
    
    echo "4. 如果使用 PHP 内置服务器:\n";
    echo "   - 停止当前服务器 (Ctrl+C)\n";
    echo "   - 重新启动服务器\n\n";
    
    echo "重启后运行: php check_php_extensions.php\n";
}

echo "========================================\n";

// 列出扩展目录中的所有文件
echo "\n扩展目录中的所有 DLL 文件:\n";
$files = glob($extDir . DIRECTORY_SEPARATOR . '*.dll');
if ($files) {
    foreach ($files as $file) {
        echo "   - " . basename($file) . "\n";
    }
} else {
    echo "   (未找到任何 DLL 文件)\n";
}
