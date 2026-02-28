<?php
/**
 * 自动启用 PHP GD 扩展
 * 此脚本会自动修改 php.ini 文件以启用 GD 扩展
 */

echo "========================================\n";
echo "PHP GD 扩展自动启用脚本\n";
echo "========================================\n\n";

// 获取 php.ini 路径
$phpIniPath = php_ini_loaded_file();

if (!$phpIniPath) {
    echo "❌ 错误: 无法找到 php.ini 文件\n";
    exit(1);
}

echo "✅ 找到 php.ini: {$phpIniPath}\n\n";

// 检查文件是否可写
if (!is_writable($phpIniPath)) {
    echo "❌ 错误: php.ini 文件不可写\n";
    echo "   请以管理员身份运行此脚本\n";
    echo "   或手动编辑文件: {$phpIniPath}\n\n";
    echo "手动操作步骤:\n";
    echo "1. 用记事本打开: {$phpIniPath}\n";
    echo "2. 查找: ;extension=gd 或 ;extension=php_gd2.dll\n";
    echo "3. 删除前面的分号 ;\n";
    echo "4. 保存文件\n";
    echo "5. 重启 PHP 服务\n";
    exit(1);
}

// 读取 php.ini 内容
$content = file_get_contents($phpIniPath);
$originalContent = $content;

// 备份 php.ini
$backupPath = $phpIniPath . '.backup.' . date('YmdHis');
if (file_put_contents($backupPath, $content)) {
    echo "✅ 已备份 php.ini 到: {$backupPath}\n\n";
} else {
    echo "⚠️ 警告: 无法创建备份文件\n\n";
}

// 检查 GD 扩展配置
$modified = false;

// PHP 7.x 和 8.x 的 GD 扩展配置
$patterns = [
    '/^;\s*extension\s*=\s*gd\s*$/m' => 'extension=gd',
    '/^;\s*extension\s*=\s*php_gd2\.dll\s*$/m' => 'extension=php_gd2.dll',
    '/^;\s*extension\s*=\s*php_gd\.dll\s*$/m' => 'extension=php_gd.dll',
];

foreach ($patterns as $pattern => $replacement) {
    if (preg_match($pattern, $content)) {
        $content = preg_replace($pattern, $replacement, $content);
        $modified = true;
        echo "✅ 找到并启用: {$replacement}\n";
        break;
    }
}

// 如果没有找到注释的扩展，检查是否已经启用
if (!$modified) {
    if (preg_match('/^extension\s*=\s*(gd|php_gd2\.dll|php_gd\.dll)\s*$/m', $content)) {
        echo "✅ GD 扩展已经启用\n";
    } else {
        // 尝试添加扩展配置
        echo "⚠️ 未找到 GD 扩展配置，尝试添加...\n";
        
        // 查找 extension 配置区域
        if (preg_match('/^extension\s*=\s*/m', $content)) {
            // 在第一个 extension 配置后添加
            $content = preg_replace(
                '/(^extension\s*=\s*[^\n]+)/m',
                "$1\nextension=gd",
                $content,
                1
            );
            $modified = true;
            echo "✅ 已添加: extension=gd\n";
        } else {
            echo "❌ 无法自动添加 GD 扩展配置\n";
            echo "   请手动在 php.ini 中添加: extension=gd\n";
        }
    }
}

// 保存修改
if ($modified && $content !== $originalContent) {
    if (file_put_contents($phpIniPath, $content)) {
        echo "\n✅ php.ini 已更新\n\n";
    } else {
        echo "\n❌ 错误: 无法保存 php.ini\n";
        exit(1);
    }
} else {
    echo "\n";
}

echo "========================================\n";
echo "下一步操作\n";
echo "========================================\n\n";

echo "1. 重启 PHP 服务:\n";
echo "   - 如果使用 Apache: 重启 Apache 服务\n";
echo "   - 如果使用 Nginx: 重启 PHP-FPM 服务\n";
echo "   - 如果使用宝塔面板: 在面板中重启 PHP\n\n";

echo "2. 验证 GD 扩展:\n";
echo "   运行: php check_php_extensions.php\n\n";

echo "3. 如果仍然无法使用 GD:\n";
echo "   - 检查 PHP 扩展目录中是否有 php_gd2.dll 或 gd.dll\n";
echo "   - 扩展目录通常在: " . ini_get('extension_dir') . "\n";
echo "   - 如果缺少 DLL 文件，需要重新安装 PHP 或下载对应版本的扩展\n\n";

echo "========================================\n";
