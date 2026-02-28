<?php
/**
 * 启用入库通知的图片发送功能
 */

echo "=== 启用 LINE 消息图片发送功能 ===\n\n";

// 数据库配置
$dbHost = '103.119.1.84';
$dbName = 'xinsuju';
$dbUser = 'xinsuju';
$dbPass = 'cJGzwZTDCLHzWXN4';
$dbPrefix = 'yoshop_';
$wxappId = 10001;

// 连接数据库
echo "连接数据库...\n";
try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ 数据库连接成功\n\n";
} catch (PDOException $e) {
    die("❌ 数据库连接失败: " . $e->getMessage() . "\n");
}

// 获取当前配置
echo "获取当前配置...\n";
$stmt = $pdo->prepare("SELECT `values` FROM {$dbPrefix}setting WHERE `key` = 'line_messaging' AND wxapp_id = ?");
$stmt->execute([$wxappId]);
$configRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$configRow) {
    die("❌ 错误: 未找到 LINE 消息配置\n");
}

$config = json_decode($configRow['values'], true);
echo "✅ 配置加载成功\n\n";

// 修改入库通知模板配置
echo "修改入库通知模板配置...\n";
if (!isset($config['templates']['inwarehouse'])) {
    die("❌ 错误: 未找到入库通知模板\n");
}

// 启用图片发送
$config['templates']['inwarehouse']['send_images'] = '1';
$config['templates']['inwarehouse']['max_images'] = 3;

echo "✅ 已设置:\n";
echo "   send_images = 1 (启用)\n";
echo "   max_images = 3 (最多3张)\n\n";

// 保存配置
echo "保存配置到数据库...\n";
$newValues = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// 使用 base64 编码避免字符集问题
$stmt = $pdo->prepare("UPDATE {$dbPrefix}setting SET `values` = ? WHERE `key` = 'line_messaging' AND wxapp_id = ?");
$pdo->exec("SET NAMES utf8mb4");
$result = $stmt->execute([$newValues, $wxappId]);

if ($result) {
    echo "✅✅✅ 配置保存成功！✅✅✅\n\n";
    echo "入库通知模板现在已启用图片发送功能\n";
    echo "最多发送 3 张图片\n";
} else {
    echo "❌ 配置保存失败\n";
}

echo "\n=== 完成 ===\n";
