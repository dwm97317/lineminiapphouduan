<?php
/**
 * 更新入库通知模板 - 添加尺寸和唛头字段
 */

define('APP_PATH', __DIR__ . '/source/application/');
require __DIR__ . '/source/thinkphp/start.php';

use app\common\model\Setting as SettingModel;

echo "==================== 更新入库通知模板 ====================\n\n";

$wxappId = 10001;

// 获取当前配置
$config = SettingModel::getItem('line_messaging', $wxappId);

if (empty($config)) {
    die("❌ 未找到 LINE 配置\n");
}

// 新的模板（添加尺寸和唛头字段）
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

// 更新模板
$config['templates']['inwarehouse']['flex_template'] = json_encode($newTemplate, JSON_UNESCAPED_UNICODE);
$config['templates']['inwarehouse']['variables'] = '["shop_name","express_num","entering_warehouse_time","weight","size","mark","remark","detail_url"]';

// 保存配置
$model = SettingModel::where(['key' => 'line_messaging', 'wxapp_id' => $wxappId])->find();

if ($model) {
    $model->save([
        'values' => json_encode($config, JSON_UNESCAPED_UNICODE),
        'update_time' => time()
    ]);
    
    echo "✅ 模板更新成功\n\n";
    echo "新增字段:\n";
    echo "- 尺寸 (size): {{size}}\n";
    echo "- 唛头 (mark): {{mark}}\n\n";
    echo "模板预览:\n";
    echo json_encode($newTemplate, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
} else {
    echo "❌ 未找到配置记录\n";
}

echo "\n==================== 完成 ====================\n";
