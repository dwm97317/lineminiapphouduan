<?php
/**
 * 通过 HTTP 请求测试 saveconfig
 */

// 测试数据
$postData = [
    'config_type' => 'task',
    'task_config' => [
        'referrer' => [
            3 => [
                'is_enabled' => 1,
                'is_required' => 1,
            ]
        ],
        'referee' => [
            1 => [
                'is_enabled' => 1,
                'is_required' => 1,
            ]
        ]
    ]
];

echo "=== 测试 saveconfig HTTP 请求 ===\n\n";
echo "POST 数据:\n";
echo json_encode($postData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";

// 发送请求
$url = 'http://localhost:8080/index.php?s=/store/setting.referral/saveconfig';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'X-Requested-With: XMLHttpRequest'  // 标记为 AJAX 请求
]);
curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=your_session_id_here'); // 需要替换为实际的 session ID

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP 状态码: {$httpCode}\n";
echo "响应内容:\n";
echo $response . "\n\n";

// 尝试解析 JSON
$json = json_decode($response, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "解析后的 JSON:\n";
    print_r($json);
} else {
    echo "无法解析为 JSON，原始响应:\n";
    echo $response . "\n";
}
