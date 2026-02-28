<?php
// 模拟接收到的 POST 数据
$testCases = [
    'case1_json_string' => '{"zhongtong":{"api_url":"123123","api_key":"123123","api_secret":"123123","company_code":"ZTO"},"shunfeng":{"api_url":"https://123123","api_key":"123123123","api_secret":"123123","custid":"123123123","pay_method":"1","company_code":"SF"}}',
    
    'case2_url_encoded' => '%7B%22zhongtong%22%3A%7B%22api_url%22%3A%22123123%22%2C%22api_key%22%3A%22123123%22%2C%22api_secret%22%3A%22123123%22%2C%22company_code%22%3A%22ZTO%22%7D%2C%22shunfeng%22%3A%7B%22api_url%22%3A%22https%3A%2F%2F123123%22%2C%22api_key%22%3A%22123123123%22%2C%22api_secret%22%3A%22123123%22%2C%22custid%22%3A%22123123123%22%2C%22pay_method%22%3A%221%22%2C%22company_code%22%3A%22SF%22%7D%7D',
    
    'case3_array' => [
        'zhongtong' => [
            'api_url' => '123123',
            'api_key' => '123123',
            'api_secret' => '123123',
            'company_code' => 'ZTO'
        ],
        'shunfeng' => [
            'api_url' => 'https://123123',
            'api_key' => '123123123',
            'api_secret' => '123123',
            'custid' => '123123123',
            'pay_method' => '1',
            'company_code' => 'SF'
        ]
    ]
];

echo "========== 测试不同数据格式的处理 ==========\n\n";

foreach ($testCases as $caseName => $config) {
    echo "测试场景: $caseName\n";
    echo str_repeat('-', 50) . "\n";
    
    // 1. 检查原始类型
    echo "原始类型: " . gettype($config) . "\n";
    
    if (is_string($config)) {
        echo "原始值（前100字符）: " . substr($config, 0, 100) . "...\n";
        
        // 2. 尝试 URL 解码
        $urlDecoded = urldecode($config);
        if ($urlDecoded !== $config) {
            echo "URL 解码后: " . substr($urlDecoded, 0, 100) . "...\n";
            $config = $urlDecoded;
        }
        
        // 3. 尝试 JSON 解码
        $jsonDecoded = json_decode($config, true);
        $jsonError = json_last_error();
        
        if ($jsonError === JSON_ERROR_NONE) {
            echo "✓ JSON 解码成功\n";
            echo "解码后类型: " . gettype($jsonDecoded) . "\n";
            $config = $jsonDecoded;
        } else {
            echo "✗ JSON 解码失败: " . json_last_error_msg() . "\n";
            echo "\n";
            continue;
        }
    }
    
    // 4. 验证数组结构
    if (is_array($config)) {
        echo "✓ 是数组类型\n";
        
        if (isset($config['zhongtong']) && isset($config['shunfeng'])) {
            echo "✓ 包含 zhongtong 和 shunfeng 配置\n";
            
            // 显示配置内容
            echo "\n中通配置:\n";
            foreach ($config['zhongtong'] as $key => $value) {
                echo "  - $key: $value\n";
            }
            
            echo "\n顺丰配置:\n";
            foreach ($config['shunfeng'] as $key => $value) {
                echo "  - $key: $value\n";
            }
            
            echo "\n✓✓✓ 此格式可以正常保存 ✓✓✓\n";
        } else {
            echo "✗ 缺少必要的配置项\n";
        }
    } else {
        echo "✗ 不是数组类型，无法保存\n";
    }
    
    echo "\n" . str_repeat('=', 50) . "\n\n";
}

echo "\n总结:\n";
echo "1. 如果前端发送的是 JSON 字符串，需要 json_decode() 解码\n";
echo "2. 如果前端发送的是 URL 编码的 JSON，需要先 urldecode() 再 json_decode()\n";
echo "3. 如果前端直接发送数组（PHP 自动解析），无需额外处理\n";
echo "\n建议的处理逻辑:\n";
echo "  1. 检查是否为字符串\n";
echo "  2. 如果是字符串，尝试 URL 解码\n";
echo "  3. 尝试 JSON 解码\n";
echo "  4. 验证是否为数组并包含必要字段\n";
