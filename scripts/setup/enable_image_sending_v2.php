<?php
/**
 * 启用入库通知的图片发送功能 v2
 * 使用项目数据库配置
 */

echo "=== 启用 LINE 消息图片发送功能 ===\n\n";

// 加载项目数据库配置
$dbConfig = require __DIR__ . '/source/application/database.php';

$wxappId = 10001;

// 连接数据库
echo "连接数据库...\n";
echo "主机: {$dbConfig['hostname']}\n";
echo "数据库: {$dbConfig['database']}\n";
echo "字符集: {$dbConfig['charset']}\n\n";

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['hostname']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbConfig['charset']}"
        ]
    );
    echo "✅ 数据库连接成功\n\n";
} catch (PDOException $e) {
    die("❌ 数据库连接失败: " . $e->getMessage() . "\n");
}

$prefix = $dbConfig['prefix'];

// 获取当前配置
echo "获取当前配置...\n";
$stmt = $pdo->prepare("SELECT `values` FROM {$prefix}setting WHERE `key` = 'line_messaging' AND wxapp_id = ?");
$stmt->execute([$wxappId]);
$configRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$configRow) {
    die("❌ 错误: 未找到 LINE 消息配置\n");
}

$config = json_decode($configRow['values'], true);
echo "✅ 配置加载成功\n\n";

// 显示当前入库通知配置
echo "当前入库通知配置:\n";
if (isset($config['templates']['inwarehouse'])) {
    $template = $config['templates']['inwarehouse'];
    echo "  模板名称: " . ($template['name'] ?? '未设置') . "\n";
    echo "  是否启用: " . ($template['is_enable'] ?? '0') . "\n";
    echo "  发送图片: " . ($template['send_images'] ?? '未设置') . "\n";
    echo "  最大图片数: " . ($template['max_images'] ?? '未设置') . "\n\n";
} else {
    die("❌ 错误: 未找到入库通知模板\n");
}

// 修改配置
echo "修改入库通知模板配置...\n";
$config['templates']['inwarehouse']['send_images'] = '1';
$config['templates']['inwarehouse']['max_images'] = 3;

echo "✅ 已设置:\n";
echo "   send_images = 1 (启用)\n";
echo "   max_images = 3 (最多3张)\n\n";

// 保存配置 - 使用 serialize 避免字符集问题
echo "保存配置到数据库...\n";

// 方法1: 尝试直接更新（如果表支持utf8mb4）
try {
    $pdo->exec("SET NAMES utf8mb4");
    $newValues = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $stmt = $pdo->prepare("UPDATE {$prefix}setting SET `values` = ? WHERE `key` = 'line_messaging' AND wxapp_id = ?");
    $result = $stmt->execute([$newValues, $wxappId]);
    
    if ($result) {
        echo "✅✅✅ 配置保存成功（方法1：直接更新）！✅✅✅\n\n";
        $success = true;
    }
} catch (PDOException $e) {
    echo "⚠️  方法1失败: " . $e->getMessage() . "\n";
    echo "尝试方法2...\n\n";
    $success = false;
}

// 方法2: 如果方法1失败，使用序列化
if (!$success) {
    try {
        // 将emoji等特殊字符转换为HTML实体
        $newValues = json_encode($config, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        $stmt = $pdo->prepare("UPDATE {$prefix}setting SET `values` = ? WHERE `key` = 'line_messaging' AND wxapp_id = ?");
        $result = $stmt->execute([$newValues, $wxappId]);
        
        if ($result) {
            echo "✅✅✅ 配置保存成功（方法2：转义特殊字符）！✅✅✅\n\n";
            $success = true;
        }
    } catch (PDOException $e) {
        echo "❌ 方法2也失败: " . $e->getMessage() . "\n\n";
    }
}

if ($success) {
    echo "入库通知模板现在已启用图片发送功能\n";
    echo "最多发送 3 张图片\n\n";
    
    // 验证保存
    echo "验证保存结果...\n";
    $stmt = $pdo->prepare("SELECT `values` FROM {$prefix}setting WHERE `key` = 'line_messaging' AND wxapp_id = ?");
    $stmt->execute([$wxappId]);
    $verifyRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $verifyConfig = json_decode($verifyRow['values'], true);
    
    if (isset($verifyConfig['templates']['inwarehouse']['send_images']) && 
        $verifyConfig['templates']['inwarehouse']['send_images'] == '1') {
        echo "✅ 验证成功！配置已正确保存\n";
        echo "   send_images = " . $verifyConfig['templates']['inwarehouse']['send_images'] . "\n";
        echo "   max_images = " . $verifyConfig['templates']['inwarehouse']['max_images'] . "\n";
    } else {
        echo "⚠️  验证失败，配置可能未正确保存\n";
    }
} else {
    echo "❌ 所有保存方法都失败了\n";
    echo "\n建议：请通过后台界面手动配置\n";
    echo "访问: http://localhost:8080/index.php?s=/store/setting.line_config/index\n";
    echo "找到\"📦 包裹入库通知\"\n";
    echo "勾选\"启用图片发送\"\n";
    echo "选择\"最大图片数量\": 3张\n";
    echo "点击\"提交保存\"\n";
}

echo "\n=== 完成 ===\n";
