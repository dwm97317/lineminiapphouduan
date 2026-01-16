<?php
/**
 * 强制更新模板（清除缓存）
 */

define('APP_PATH', __DIR__ . '/source/application/');
require __DIR__ . '/source/thinkphp/start.php';

use think\Db;

echo "==================== 强制更新模板 ====================\n\n";

$wxappId = 10001;

// 直接从数据库读取
$setting = Db::name('setting')
    ->where(['key' => 'line_messaging', 'wxapp_id' => $wxappId])
    ->find();

if (!$setting) {
    die("❌ 未找到配置\n");
}

$config = json_decode($setting['values'], true);

if (!is_array($config) || !isset($config['templates']['inwarehouse'])) {
    die("❌ 配置格式错误\n");
}

echo "【1】当前模板变量: " . (isset($config['templates']['inwarehouse']['variables']) ? $config['templates']['inwarehouse']['variables'] : '无') . "\n\n";

// 新模板
$newTemplate = [
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

// 更新配置
$config['templates']['inwarehouse']['flex_template'] = json_encode($newTemplate, JSON_UNESCAPED_UNICODE);
$config['templates']['inwarehouse']['variables'] = '["shop_name","express_num","entering_warehouse_time","weight","size","mark","remark","detail_url"]';

// 直接更新数据库
$result = Db::name('setting')
    ->where(['key' => 'line_messaging', 'wxapp_id' => $wxappId])
    ->update([
        'values' => json_encode($config, JSON_UNESCAPED_UNICODE),
        'update_time' => time()
    ]);

if ($result !== false) {
    echo "✅ 数据库更新成功\n\n";
    
    // 清除缓存
    cache('setting_line_messaging_' . $wxappId, null);
    echo "✅ 缓存已清除\n\n";
    
    echo "【2】新模板变量: " . $config['templates']['inwarehouse']['variables'] . "\n";
} else {
    echo "❌ 更新失败\n";
}

echo "\n==================== 完成 ====================\n";
