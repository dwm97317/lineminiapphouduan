<?php
/**
 * 检查 PHP 图像处理扩展
 */

echo "=== PHP 图像处理扩展检查 ===\n\n";

// 检查 GD 库
echo "1. GD 库:\n";
if (extension_loaded('gd')) {
    echo "   ✅ GD 库已安装\n";
    $gdInfo = gd_info();
    echo "   版本: " . ($gdInfo['GD Version'] ?? '未知') . "\n";
    echo "   支持 PNG: " . ($gdInfo['PNG Support'] ? '是' : '否') . "\n";
    echo "   支持 JPEG: " . ($gdInfo['JPEG Support'] ?? $gdInfo['JPG Support'] ?? false ? '是' : '否') . "\n";
} else {
    echo "   ❌ GD 库未安装\n";
}

echo "\n";

// 检查 Imagick
echo "2. Imagick 扩展:\n";
if (extension_loaded('imagick')) {
    echo "   ✅ Imagick 已安装\n";
    $imagick = new Imagick();
    echo "   版本: " . $imagick->getVersion()['versionString'] . "\n";
} else {
    echo "   ❌ Imagick 未安装\n";
}

echo "\n";

// 检查相关函数
echo "3. 相关函数检查:\n";
echo "   imagecreate: " . (function_exists('imagecreate') ? '✅ 存在' : '❌ 不存在') . "\n";
echo "   imagepng: " . (function_exists('imagepng') ? '✅ 存在' : '❌ 不存在') . "\n";
echo "   imagecolorallocate: " . (function_exists('imagecolorallocate') ? '✅ 存在' : '❌ 不存在') . "\n";

echo "\n";

// 给出建议
echo "=== 解决方案建议 ===\n\n";

if (!extension_loaded('gd') && !extension_loaded('imagick')) {
    echo "⚠️ 两个扩展都未安装，需要安装其中一个\n\n";
    
    echo "方案 1: 安装 GD 库（推荐）\n";
    echo "   Windows: 在 php.ini 中启用 extension=gd\n";
    echo "   Linux: sudo apt-get install php-gd 或 sudo yum install php-gd\n";
    echo "   然后重启 PHP 服务\n\n";
    
    echo "方案 2: 安装 Imagick\n";
    echo "   Windows: 下载 php_imagick.dll 并配置 php.ini\n";
    echo "   Linux: sudo apt-get install php-imagick 或 sudo yum install php-imagick\n";
    echo "   然后重启 PHP 服务\n\n";
    
    echo "方案 3: 使用 SVG 格式（临时方案，不需要安装扩展）\n";
    echo "   修改代码使用 BarcodeGeneratorSVG 替代 BarcodeGeneratorPNG\n";
} else {
    echo "✅ 至少有一个图像处理扩展可用\n";
}

echo "\n";

// PHP 信息
echo "=== PHP 环境信息 ===\n";
echo "PHP 版本: " . PHP_VERSION . "\n";
echo "操作系统: " . PHP_OS . "\n";
echo "PHP.ini 位置: " . php_ini_loaded_file() . "\n";
