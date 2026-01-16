<?php
/**
 * 直接测试 LINE API
 */

// 配置
$channelId = '2008892817';
$channelSecret = 'b151f49e637c6860418d241e37cf45c9';
$accessToken = '9r8TY3xWYI5hCh0CHu11qc8ho24RAgCQXV1ba8iQn8WwdnZdLIeGqTPZj202voSsr9R1FLaFAi1JzPaEYmPCC3JBKzEIiSAQqWbe/vHHBjeMqkzdn6yQYsSHLHwSueq87y4SAIo6dLZgEH1VaIUUugdB04t89/1O/w1cDnyilFU=';
$lineUserId = 'Ud4e37d68c438cc70350957039add98d8';

echo "<h2>LINE API 直接测试</h2>";

// Flex Message 模板
$flexTemplate = [
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
                "text" => "仓库：泰国仓库",
                "size" => "sm",
                "wrap" => true
            ],
            [
                "type" => "text",
                "text" => "快递单号：TEST" . date('YmdHis'),
                "size" => "sm",
                "wrap" => true
            ],
            [
                "type" => "text",
                "text" => "入库时间：" . date('Y-m-d H:i:s'),
                "size" => "sm",
                "wrap" => true
            ],
            [
                "type" => "text",
                "text" => "重量：1.5kg",
                "size" => "sm",
                "wrap" => true
            ],
            [
                "type" => "separator",
                "margin" => "md"
            ],
            [
                "type" => "text",
                "text" => "这是一条测试消息",
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
                    "uri" => "https://example.com/package/detail?id=999"
                ],
                "style" => "primary",
                "color" => "#1DB446"
            ]
        ]
    ]
];

// 构建消息
$message = [
    'type' => 'flex',
    'altText' => '📦 包裹入库通知',
    'contents' => $flexTemplate
];

$data = [
    'to' => $lineUserId,
    'messages' => [$message]
];

echo "<h3>1. 请求数据</h3>";
echo "<pre>";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre>";

// 发送请求
echo "<h3>2. 发送请求</h3>";
$url = 'https://api.line.me/v2/bot/message/push';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<p>HTTP 状态码: <strong>$httpCode</strong></p>";

if ($error) {
    echo "<p style='color:red'>❌ CURL 错误: $error</p>";
} else {
    echo "<p>✅ 请求成功</p>";
}

echo "<h3>3. 响应内容</h3>";
if (empty($response)) {
    echo "<p style='color:green'>✅ 响应为空（LINE API 成功时通常返回空响应）</p>";
} else {
    echo "<pre>";
    $responseData = json_decode($response, true);
    if ($responseData) {
        print_r($responseData);
        
        if (isset($responseData['message'])) {
            echo "\n<p style='color:red'>❌ LINE API 错误: " . $responseData['message'] . "</p>";
            if (isset($responseData['details'])) {
                echo "<p>详情:</p><pre>";
                print_r($responseData['details']);
                echo "</pre>";
            }
        }
    } else {
        echo htmlspecialchars($response);
    }
    echo "</pre>";
}

echo "<h3>完成</h3>";
