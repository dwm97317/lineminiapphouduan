<?php
/**
 * 修正 GD 扩展配置
 */

echo "========================================\n";
echo "修正 GD 扩展配置\n";
echo "========================================\n\n";

$phpIniPath = php_ini_loaded_file();
echo "php.ini 路径: {$phpIniPath}\n\n";

// 读取 php.ini
$content = file_get_contents($phpIniPath);

// 备份
$backupPath = $phpIniPath . '.backup.' . date('YmdHis');
file_put_contents($backupPath, $content);
echo "✅ 已备份到: {$backupPath}\n\n";

// 替换错误的配置
$modified = false;

// 移除错误的 extension=gd
if (preg_match('/^extension\s*=\s*gd\s*$/m', $content)) {
    $content = preg_replace('/^extension\s*=\s*gd\s*$/m', ';extension=gd (已禁用，使用 php_gd2.dll)', $content);
    echo "✅ 已移除错误的配置: extension=gd\n";
    $modified = true;
}

// 启用正确的 php_gd2.dll
if (preg_match('/^;\s*extension\s*=\s*php_gd2\.dll\s*$/m', $content)) {
    $content = preg_replace('/^;\s*extension\s*=\s*php_gd2\.dll\s*$/m', 'extension=php_gd2.dll', $content);
    echo "✅ 已启用: extension=php_gd2.dll\n";
    $modified = true;
} elseif (!preg_match('/^extension\s*=\s*php_gd2\.dll\s*$/m', $content)) {
    // 如果没有找到，添加配置
    $content = preg_replace(
        '/(^extension\s*=\s*[^\n]+)/m',
        "$1\nextension=php_gd2.dll",
        $content,
        1
    );
    echo "✅ 已添加: extension=php_gd2.dll\n";
    $modified = true;
} else {
    echo "✅ extension=php_gd2.dll 已经启用\n";
}

// 保存
if ($modified) {
    file_put_contents($phpIniPath, $content);
    echo "\n✅ php.ini 已更新\n\n";
} else {
    echo "\n";
}

echo "========================================\n";
echo "下一步操作\n";
echo "========================================\n\n";

echo "请重启 PHP 服务，然后运行:\n";
echo "   php check_php_extensions.php\n\n";

echo "如果使用宝塔面板:\n";
echo "1. 登录宝塔面板\n";
echo "2. 软件商店 -> PHP 7.3 -> 设置\n";
echo "3. 服务 -> 重启\n\n";

echo "如果使用 Apache/Nginx:\n";
echo "1. 打开服务管理器: Win+R -> services.msc\n";
echo "2. 找到对应服务并重启\n\n";
