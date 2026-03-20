<?php
/**
 * 多账户管理功能测试脚本
 * 
 * 测试场景：
 * 1. 一个 Customer ID 绑定多个用户账户
 * 2. 达到绑定上限（10 个）时的处理
 * 3. 查看已关联账户列表
 * 4. 解绑账户（带确认）
 * 5. Bot 命令：查看关联账户、待入库包裹、订单历史
 */

// ============================================
// 配置区域
// ============================================
$BASE_URL = 'http://localhost/web/index.php';
$WXAPP_ID = '10001';
$API_KEY = 'sk_test_1234567890abcdef'; // 需要先配置
$TEST_CUSTOMER_ID = 'CUST_MULTI_' . time(); // 唯一的 Customer ID

echo "========================================\n";
echo "多账户管理功能测试\n";
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
    
    $defaultHeaders = [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-Bot-API-Key: ' . $GLOBALS['API_KEY'],
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
    
    return [
        'http_code' => $httpCode,
        'response' => json_decode($response, true),
        'error' => $error,
    ];
}

/**
 * 模拟绑定操作
 */
function bindAccount($userId, $token, $customerId) {
    global $BASE_URL, $WXAPP_ID;
    
    $data = [
        'wxapp_id' => $WXAPP_ID,
        'token' => $token,
        'customer_id' => $customerId,
        'platform_type' => 'FACEBOOK',
    ];
    
    return sendPostRequest($BASE_URL . '?s=/api/v1/account/bind', $data);
}

/**
 * 模拟查看绑定列表
 */
function listAccounts($token) {
    global $BASE_URL, $WXAPP_ID;
    
    $params = [
        'wxapp_id' => $WXAPP_ID,
        'token' => $token,
    ];
    
    return sendGetRequest($BASE_URL . '?s=/api/v1/account/list', $params);
}

// ============================================
// Test 1: 第一个用户绑定 Customer ID
// ============================================
echo "Test 1: 第一个用户绑定 Customer ID\n";
echo "----------------------------------------\n";

// 注意：需要使用真实的 token
$token1 = 'YOUR_TEST_TOKEN_USER1'; // 替换为实际用户 Token
$result = bindAccount(1, $token1, $TEST_CUSTOMER_ID);

echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']) {
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if (isset($result['response']['code']) && $result['response']['code'] == 1) {
        echo "✅ 第一个用户绑定成功\n";
        echo "   - Binding Count: " . ($result['response']['data']['binding_count'] ?? 'N/A') . "\n";
    } else {
        echo "⚠️  绑定失败：" . ($result['response']['msg'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ 请求失败：" . ($result['error'] ?? 'Unknown error') . "\n";
}
echo "\n";

// ============================================
// Test 2: 第二个用户绑定同一个 Customer ID
// ============================================
echo "Test 2: 第二个用户绑定同一个 Customer ID\n";
echo "----------------------------------------\n";

$token2 = 'YOUR_TEST_TOKEN_USER2'; // 替换为实际用户 Token
$result = bindAccount(2, $token2, $TEST_CUSTOMER_ID);

echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']) {
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if (isset($result['response']['code']) && $result['response']['code'] == 1) {
        echo "✅ 第二个用户绑定成功（支持多账户）\n";
        echo "   - Binding Count: " . ($result['response']['data']['binding_count'] ?? 'N/A') . "\n";
    } else {
        echo "⚠️  绑定失败：" . ($result['response']['msg'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ 请求失败：" . ($result['error'] ?? 'Unknown error') . "\n";
}
echo "\n";

// ============================================
// Test 3: 查看已关联的账户列表
// ============================================
echo "Test 3: 查看已关联的账户列表 (GET /api/v1/account/list)\n";
echo "----------------------------------------\n";

$result = listAccounts($token1);

echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']) {
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if (isset($result['response']['code']) && $result['response']['code'] == 1) {
        echo "✅ 获取绑定列表成功\n";
        echo "   - Total: " . ($result['response']['data']['total'] ?? 'N/A') . "\n";
        echo "   - Max Allowed: " . ($result['response']['data']['max_allowed'] ?? 'N/A') . "\n";
        
        if (isset($result['response']['data']['list'])) {
            foreach ($result['response']['data']['list'] as $index => $account) {
                echo "   [" . ($index + 1) . "] " . $account['platform_name'] . " - " . $account['customer_name_anonymized'] . "\n";
            }
        }
    } else {
        echo "⚠️  获取列表失败：" . ($result['response']['msg'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ 请求失败：" . ($result['error'] ?? 'Unknown error') . "\n";
}
echo "\n";

// ============================================
// Test 4: Bot 端查看关联账户列表
// ============================================
echo "Test 4: Bot 端查看关联账户 (GET /api/bot/account/list-linked)\n";
echo "----------------------------------------\n";

$params = [
    'wxapp_id' => $WXAPP_ID,
    'customer_id' => $TEST_CUSTOMER_ID,
];

$result = sendGetRequest($BASE_URL . '?s=/api/bot/account/list-linked', $params);

echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']) {
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if (isset($result['response']['code']) && $result['response']['code'] == 1) {
        echo "✅ Bot 端获取关联账户成功\n";
        echo "   - Total: " . ($result['response']['data']['total'] ?? 'N/A') . "\n";
        echo "   - Message: " . ($result['response']['msg'] ?? '') . "\n";
    } else {
        echo "⚠️  获取失败：" . ($result['response']['msg'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ 请求失败：" . ($result['error'] ?? 'Unknown error') . "\n";
}
echo "\n";

// ============================================
// Test 5: Bot 端查看待入库包裹
// ============================================
echo "Test 5: Bot 端查看待入库包裹 (GET /api/bot/package/waiting-list)\n";
echo "----------------------------------------\n";

$result = sendGetRequest($BASE_URL . '?s=/api/bot/package/waiting-list', $params);

echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']) {
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if (isset($result['response']['code']) && $result['response']['code'] == 1) {
        echo "✅ 获取待入库包裹成功\n";
        echo "   - Total: " . ($result['response']['data']['total'] ?? 'N/A') . "\n";
        
        if (isset($result['response']['data']['list']) && count($result['response']['data']['list']) > 0) {
            foreach ($result['response']['data']['list'] as $index => $pkg) {
                echo "   [" . ($index + 1) . "] " . $pkg['package_code'] . " - " . $pkg['weight'] . "kg\n";
            }
        }
    } else {
        echo "⚠️  获取失败：" . ($result['response']['msg'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ 请求失败：" . ($result['error'] ?? 'Unknown error') . "\n";
}
echo "\n";

// ============================================
// Test 6: Bot 端查看订单历史
// ============================================
echo "Test 6: Bot 端查看订单历史 (GET /api/bot/order/history)\n";
echo "----------------------------------------\n";

$params['limit'] = 10;
$result = sendGetRequest($BASE_URL . '?s=/api/bot/order/history', $params);

echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']) {
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if (isset($result['response']['code']) && $result['response']['code'] == 1) {
        echo "✅ 获取订单历史成功\n";
        echo "   - Total: " . ($result['response']['data']['total'] ?? 'N/A') . "\n";
        
        if (isset($result['response']['data']['list']) && count($result['response']['data']['list']) > 0) {
            foreach ($result['response']['data']['list'] as $index => $order) {
                echo "   [" . ($index + 1) . "] " . $order['order_sn'] . " - " . $order['order_status_text'] . "\n";
            }
        }
    } else {
        echo "⚠️  获取失败：" . ($result['response']['msg'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ 请求失败：" . ($result['error'] ?? 'Unknown error') . "\n";
}
echo "\n";

// ============================================
// Test 7: 解绑账户（需要确认）
// ============================================
echo "Test 7: 解绑账户（需要确认）\n";
echo "----------------------------------------\n";

// 首先获取要解绑的记录 ID
$bindings = listAccounts($token1);
if (isset($bindings['response']['data']['list'][0]['id'])) {
    $unbindId = $bindings['response']['data']['list'][0]['id'];
    
    // 第一次请求（不带确认码，会返回确认提示）
    $data = [
        'wxapp_id' => $WXAPP_ID,
        'token' => $token1,
        'id' => $unbindId,
    ];
    
    $result = sendPostRequest($BASE_URL . '?s=/api/v1/account/unbind', $data);
    
    echo "第一次请求（未提供确认码）:\n";
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if (isset($result['response']['data']['require_confirmation'])) {
        echo "✅ 系统要求二次确认\n";
        
        // 第二次请求（带确认码）
        $data['confirm_code'] = 'CONFIRM';
        $result = sendPostRequest($BASE_URL . '?s=/api/v1/account/unbind', $data);
        
        echo "\n第二次请求（提供确认码 CONFIRM）:\n";
        echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        if (isset($result['response']['code']) && $result['response']['code'] == 1) {
            echo "✅ 解绑成功\n";
        } else {
            echo "⚠️  解绑失败：" . ($result['response']['msg'] ?? 'Unknown error') . "\n";
        }
    }
} else {
    echo "⚠️  没有可解绑的账户\n";
}
echo "\n";

// ============================================
// 测试完成
// ============================================
echo "========================================\n";
echo "测试完成\n";
echo "========================================\n";
echo "\n提示：\n";
echo "1. 请替换实际的 token 进行测试\n";
echo "2. 检查 Bot API Key 配置\n";
echo "3. 确保数据库中有测试数据\n";
echo "4. 多账户绑定功能已启用（最多 10 个账户/ Customer ID）\n";
echo "\n";
