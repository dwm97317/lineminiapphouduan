<?php
/**
 * 自动更新入库通知模板 - 添加尺寸和唛头字段
 * 模拟后台界面操作
 */

define('APP_PATH', __DIR__ . '/source/application/');
require __DIR__ . '/source/thinkphp/start.php';

use app\store\model\Setting as SettingModel;

echo "==================== 自动更新模板 ====================\n\n";

$wxappId = 10001;

// 1. 获取当前配置
echo "【1】读取当前配置...\n";
$config = SettingModel::getItem('line_messaging', $wxappId);

if (empty($config)) {
    die("❌ 未找到 LINE 配置\n");
}

echo "✅ 配置读取成功\n";
echo "- 当前启用状态: " . ($config['is_enable'] ?? '未设置') . "\n";
echo "- 入库通知启用: " . ($config['templates']['inwarehouse']['is_enable'] ?? '未设置') . "\n\n";

// 2. 更新入库通知模板
echo "【2】更新入库通知模板...\n";

// 新的 Flex Message 模板（包含尺寸和唛头）
$newFlexTemplate = [
    "type" => "bubble",
    "header" => [
        "type" => "box",
        "layout" => "vertical",
        "contents" => [
            [
                "type" => "text",
                "text" => "📦 包裹入库通知",
                "weight" => "bold",
                "size" => "lg",
                "color" => "#1DB446"
            ]
        ],
        "backgroundColor" => "#F0FFF0"
    ],
    "body" => [
        "type" => "box",
        "layout" => "vertical",
        "contents" => [
            [
                "type" => "text",
                "text" => "仓库：{{shop_name}}",
                "size" => "sm",
                "wrap" => true
            ],
            [
                "type" => "text",
                "text" => "快递单号：{{express_num}}",
                "size" => "sm",
                "wrap" => true
            ],
            [
                "type" => "text",
                "text" => "入库时间：{{entering_warehouse_time}}",
                "size" => "sm",
                "wrap" => true
            ],
            [
                "type" => "text",
                "text" => "重量：{{weight}}kg",
                "size" => "sm",
                "wrap" => true
            ],
            [
                "type" => "text",
                "text" => "尺寸：{{size}}",
                "size" => "sm",
                "wrap" => true
            ],
            [
                "type" => "text",
                "text" => "唛头：{{mark}}",
                "size" => "sm",
                "wrap" => true
            ],
            [
                "type" => "separator",
                "margin" => "md"
            ],
            [
                "type" => "text",
                "text" => "{{remark}}",
                "size" => "sm",
                "color" => "#888888",
                "margin" => "md",
                "wrap" => true
            ]
        ],
        "spacing" => "sm"
    ],
    "footer" => [
        "type" => "box",
        "layout" => "vertical",
        "contents" => [
            [
                "type" => "button",
                "action" => [
                    "type" => "uri",
                    "label" => "查看详情",
                    "uri" => "{{detail_url}}"
                ],
                "style" => "primary",
                "color" => "#1DB446"
            ]
        ]
    ]
];

// 更新模板
$config['templates']['inwarehouse']['flex_template'] = json_encode($newFlexTemplate, JSON_UNESCAPED_UNICODE);
$config['templates']['inwarehouse']['variables'] = '["shop_name","express_num","entering_warehouse_time","weight","size","mark","remark","detail_url"]';

echo "✅ 模板内容已更新\n";
echo "- 新增字段: size (尺寸)\n";
echo "- 新增字段: mark (唛头)\n\n";

// 3. 保存配置（使用 SettingModel::edit 方法）
echo "【3】保存配置到数据库...\n";

// 设置 wxapp_id（直接设置静态属性）
\app\common\model\BaseModel::$wxapp_id = $wxappId;

$model = new SettingModel();
$result = $model->edit('line_messaging', $config);

if ($result) {
    echo "✅ 配置保存成功\n\n";
    
    // 4. 验证更新
    echo "【4】验证更新结果...\n";
    
    // 清除缓存
    cache('setting_line_messaging_' . $wxappId, null);
    
    // 重新读取配置
    $newConfig = SettingModel::getItem('line_messaging', $wxappId);
    $variables = $newConfig['templates']['inwarehouse']['variables'];
    
    if (is_string($variables)) {
        $variablesArray = json_decode($variables, true);
    } else {
        $variablesArray = $variables;
    }
    
    echo "✅ 配置验证成功\n";
    echo "- 模板变量: " . implode(', ', $variablesArray) . "\n";
    
    // 检查是否包含新字段
    if (in_array('size', $variablesArray) && in_array('mark', $variablesArray)) {
        echo "✅ 新字段已成功添加\n";
    } else {
        echo "⚠️ 警告：新字段可能未正确添加\n";
    }
    
} else {
    echo "❌ 配置保存失败\n";
    $error = $model->getError();
    if ($error) {
        echo "错误信息: {$error}\n";
    }
}

echo "\n==================== 完成 ====================\n";
echo "\n下一步：\n";
echo "1. 运行测试脚本验证: php test_complete_notification.php\n";
echo "2. 在后台录入包裹，检查 LINE 消息是否显示尺寸和唛头\n";
echo "3. 如果仍未显示，请清除浏览器缓存后重试\n";
