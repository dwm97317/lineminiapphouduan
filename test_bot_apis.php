<?php
/**
 * Bot API 集成测试脚本
 * 
 * 测试所有 Bot 相关 API:
 * 1. Customer ID 验证
 * 2. 包裹创建/更新
 * 3. 包裹状态查询
 * 
 * 使用方法：
 * php test_bot_apis.php
 */

// ============================================
// 配置区域 (请根据实际情况修改)
// ============================================
$BASE_URL = 'http://localhost/web/index.php'; // 后端地址
$WXAPP_ID = '10001';                          // 小程序 ID
$API_KEY = 'sk_test_1234567890abcdef';       // Bot API Key (需要先配置)
$TEST_CUSTOMER_ID = 'CUST_001';              // 测试 Customer ID
$TEST_PACKAGE_CODE = 'PKG_TEST_' . time();   // 测试包裹编号 (唯一)

echo "========================================\n";
echo "Bot API 集成测试\n";
echo "========================================\n\n";

/**
 * 发送 HTTP GET 请求
 */
function sendGetRequest($url, $params = [], $headers = []) {
    return sendHttpRequest('GET', $url, $params, $headers);
}

/**
 * 发送 HTTP POST 请求
 */
function sendPostRequest($url, $data = [], $headers = []) {
    return sendHttpRequest('POST', $url, $data, $headers);
}

/**
 * 发送 HTTP 请求
 */
function sendHttpRequest($method, $url, $data = [], $headers = []) {
    $ch = curl_init();
    
    if ($method === 'GET' && !empty($data)) {
        $url .= '?' . http_build_query($data);
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    // 设置请求头
    $defaultHeaders = [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-Bot-API-Key: ' . $API_KEY,
    ];
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'http_code' => 0,
            'response' => null,
            'error' => $error,
        ];
    }
    
    return [
        'http_code' => $httpCode,
        'response' => json_decode($response, true),
    ];
}

// ============================================
// Test 1: Customer ID 验证
// ============================================
echo "Test 1: Customer ID 验证 (GET /api/bot/customer/verify)\n";
echo "----------------------------------------\n";

$params = [
    'wxapp_id' => $WXAPP_ID,
    'customer_id' => $TEST_CUSTOMER_ID,
    'platform' => 'facebook',
];

$result = sendGetRequest($BASE_URL . '?s=/api/bot/customer/verify', $params);
echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']) {
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if (isset($result['response']['code']) && $result['response']['code'] == 1) {
        echo "✅ Customer ID 验证成功\n";
        if (isset($result['response']['data'])) {
            echo "   - User ID: " . ($result['response']['data']['user_id'] ?? 'N/A') . "\n";
            echo "   - Name: " . ($result['response']['data']['customer_name_anonymized'] ?? 'N/A') . "\n";
        }
    } else {
        echo "⚠️  Customer ID 验证失败：" . ($result['response']['msg'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ 请求失败：" . ($result['error'] ?? 'Unknown error') . "\n";
}
echo "\n";

// ============================================
// Test 2: 获取客户信息
// ============================================
echo "Test 2: 获取客户信息 (GET /api/bot/customer/info)\n";
echo "----------------------------------------\n";

$params = [
    'wxapp_id' => $WXAPP_ID,
    'customer_id' => $TEST_CUSTOMER_ID,
];

$result = sendGetRequest($BASE_URL . '?s=/api/bot/customer/info', $params);
echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']) {
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if (isset($result['response']['code']) && $result['response']['code'] == 1) {
        echo "✅ 获取客户信息成功\n";
    } else {
        echo "⚠️  获取客户信息失败：" . ($result['response']['msg'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ 请求失败：" . ($result['error'] ?? 'Unknown error') . "\n";
}
echo "\n";

// ============================================
// Test 3: 创建包裹
// ============================================
echo "Test 3: 创建包裹 (POST /api/bot/package/create)\n";
echo "----------------------------------------\n";

$data = [
    'wxapp_id' => $WXAPP_ID,
    'package_code' => $TEST_PACKAGE_CODE,
    'customer_id' => $TEST_CUSTOMER_ID,
    'weight' => 1.5,
    'volume' => 0.02,
    'length' => 30,
    'width' => 20,
    'height' => 15,
    'status' => 1,
    'remark' => '测试包裹',
];

$result = sendPostRequest($BASE_URL . '?s=/api/bot/package/create', $data);
echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']) {
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if (isset($result['response']['code']) && $result['response']['code'] == 1) {
        echo "✅ 包裹创建成功\n";
        if (isset($result['response']['data'])) {
            echo "   - Package ID: " . ($result['response']['data']['package_id'] ?? 'N/A') . "\n";
            echo "   - Action: " . ($result['response']['data']['action'] ?? 'N/A') . "\n";
        }
    } else {
        echo "⚠️  包裹创建失败：" . ($result['response']['msg'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ 请求失败：" . ($result['error'] ?? 'Unknown error') . "\n";
}
echo "\n";

// ============================================
// Test 4: 查询包裹状态
// ============================================
echo "Test 4: 查询包裹状态 (GET /api/bot/package/status)\n";
echo "----------------------------------------\n";

$params = [
    'wxapp_id' => $WXAPP_ID,
    'package_code' => $TEST_PACKAGE_CODE,
];

$result = sendGetRequest($BASE_URL . '?s=/api/bot/package/status', $params);
echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']) {
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if (isset($result['response']['code']) && $result['response']['code'] == 1) {
        echo "✅ 查询包裹状态成功\n";
        if (isset($result['response']['data'])) {
            echo "   - Order SN: " . ($result['response']['data']['order_sn'] ?? 'N/A') . "\n";
            echo "   - Status: " . ($result['response']['data']['status_text'] ?? 'N/A') . "\n";
            echo "   - Warehouse: " . ($result['response']['data']['warehouse_status'] ?? 'N/A') . "\n";
        }
    } else {
        echo "⚠️  查询包裹状态失败：" . ($result['response']['msg'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ 请求失败：" . ($result['error'] ?? 'Unknown error') . "\n";
}
echo "\n";

// ============================================
// Test 5: 重复创建包裹 (应返回更新)
// ============================================
echo "Test 5: 重复创建包裹 (测试去重逻辑)\n";
echo "----------------------------------------\n";

$data = [
    'wxapp_id' => $WXAPP_ID,
    'package_code' => $TEST_PACKAGE_CODE,
    'customer_id' => $TEST_CUSTOMER_ID,
    'weight' => 2.0, // 修改重量
    'remark' => '更新测试包裹',
];

$result = sendPostRequest($BASE_URL . '?s=/api/bot/package/create', $data);
echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']) {
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if (isset($result['response']['code']) && $result['response']['code'] == 1) {
        echo "✅ 包裹更新成功\n";
        if (isset($result['response']['data'])) {
            echo "   - Action: " . ($result['response']['data']['action'] ?? 'N/A') . "\n";
            if (($result['response']['data']['action'] ?? '') === 'updated') {
                echo "   ✓ 正确识别为更新操作\n";
            }
        }
    } else {
        echo "⚠️  包裹更新失败：" . ($result['response']['msg'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ 请求失败：" . ($result['error'] ?? 'Unknown error') . "\n";
}
echo "\n";

// ============================================
// Test 6: 缺少必要参数
// ============================================
echo "Test 6: 缺少必要参数 (错误处理)\n";
echo "----------------------------------------\n";

$data = [
    'wxapp_id' => $WXAPP_ID,
    // 缺少 package_code 和 customer_id
];

$result = sendPostRequest($BASE_URL . '?s=/api/bot/package/create', $data);
echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']) {
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if (isset($result['response']['code']) && $result['response']['code'] == 0) {
        echo "✅ 正确捕获参数缺失错误\n";
        echo "   - Error: " . ($result['response']['msg'] ?? 'Unknown error') . "\n";
    } else {
        echo "⚠️  未正确捕获错误\n";
    }
} else {
    echo "❌ 请求失败：" . ($result['error'] ?? 'Unknown error') . "\n";
}
echo "\n";

// ============================================
// Test 7: 无效 API Key
// ============================================
echo "Test 7: 无效 API Key (认证测试)\n";
echo "----------------------------------------\n";

$oldApiKey = $API_KEY;
$API_KEY = 'invalid_api_key';

$params = [
    'wxapp_id' => $WXAPP_ID,
    'customer_id' => $TEST_CUSTOMER_ID,
];

$result = sendGetRequest($BASE_URL . '?s=/api/bot/customer/verify', $params);
echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']) {
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if ($result['http_code'] == 401 || (isset($result['response']['code']) && $result['response']['code'] == 0)) {
        echo "✅ 正确拒绝无效 API Key\n";
    } else {
        echo "⚠️  未正确拒绝无效 API Key\n";
    }
} else {
    echo "❌ 请求失败：" . ($result['error'] ?? 'Unknown error') . "\n";
}

// 恢复 API Key
$API_KEY = $oldApiKey;
echo "\n";

// ============================================
// 测试完成
// ============================================
echo "========================================\n";
echo "测试完成\n";
echo "========================================\n";
echo "\n提示：\n";
echo "1. 请确保已配置正确的 API Key\n";
echo "2. 检查 BotAuth middleware 中的 ALLOWED_API_KEYS\n";
echo "3. 确认数据库中有测试用户数据\n";
echo "4. 查看日志文件获取更多错误信息\n";
echo "\n";
