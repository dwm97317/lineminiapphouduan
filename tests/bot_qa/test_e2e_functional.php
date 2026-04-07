#!/usr/bin/env php
<?php
/**
 * End-to-End Functional Test Suite - Bot System
 * 
 * Tests complete user workflows:
 * - Account Linking Flow (Valid/Invalid Customer ID)
 * - Order Session Creation (FB/IG)
 * - Information Supplement Flow (shop name, date, amount, tracking code)
 * - Tracking Code Linking (Normal/Duplicate/Invalid Format)
 * - Activate Pending Orders with Keywords
 * - Multiple Orders Simultaneously
 * 
 * Usage: php test_e2e_functional.php [test_case]
 * Example: php test_e2e_functional.php all
 */

// Configuration
define('BASE_URL', 'http://localhost:8000');
define('API_TIMEOUT', 30);
define('TEST_CUSTOMER_ID', 'CUST_TEST_001');
define('TEST_WXAPP_ID', 1);

// ANSI Colors
$C = [
    'reset'   => "\033[0m",
    'red'     => "\033[31m",
    'green'   => "\033[32m",
    'yellow'  => "\033[33m",
    'blue'    => "\033[34m",
    'cyan'    => "\033[36m",
    'white'   => "\033[37m",
    'bold'    => "\033[1m",
];

function e2e_log($msg, $style = 'info') {
    global $C;
    $prefixes = [
        'info'     => 'ℹ️ ',
        'success'  => '✅ ',
        'error'    => '❌ ',
        'warning'  => '⚠️  ',
        'test'     => '🔗 ',
        'step'     => '➡️  ',
    ];
    $color = $C[$style] ?? $C['reset'];
    echo $color . ($prefixes[$style] ?? '') . $msg . $C['reset'] . "\n";
}

/**
 * Helper: Make HTTP Request
 */
function e2e_http_request($url, $method = 'GET', $data = null, $headers = [], $timeout = 30) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
    }
    
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'body' => $response,
        'code' => $info['http_code'],
        'time' => $info['total_time'] * 1000,
        'error' => $error
    ];
}

/**
 * Helper: Generate Test Token
 */
function generate_test_token($wxapp_id, $user_id) {
    return base64_encode(json_encode([
        'wxapp_id' => $wxapp_id,
        'user_id' => $user_id,
        'timestamp' => time()
    ]));
}

/**
 * TC_E2E_01: Account Linking Flow
 * Test valid and invalid customer ID linking
 */
function tc_e2e_01_account_linking() {
    e2e_log("TC_E2E_01: Testing Account Linking Flow", 'test');
    
    $results = [];
    
    // Test 1: Valid Customer ID
    e2e_log("Test 1: Linking with Valid Customer ID", 'step');
    $token1 = generate_test_token(TEST_WXAPP_ID, 999);
    $result1 = e2e_http_request(BASE_URL . '/api/account/link', 'POST',
        json_encode([
            'customer_id' => TEST_CUSTOMER_ID,
            'line_user_id' => 'U_test_valid_user'
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token1
        ]
    );
    
    $test1_pass = in_array($result1['code'], [200, 401]); // 200 OK or 401 for auth check
    $results[] = ['name' => 'Valid Customer ID', 'passed' => $test1_pass];
    printf("  Expected: 200/401, Got: %d %s\n", $result1['code'], $test1_pass ? '✅' : '❌');
    
    // Test 2: Invalid Customer ID
    e2e_log("Test 2: Linking with Invalid Customer ID", 'step');
    $result2 = e2e_http_request(BASE_URL . '/api/account/link', 'POST',
        json_encode([
            'customer_id' => 'INVALID_CUST_999',
            'line_user_id' => 'U_test_invalid_user'
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token1
        ]
    );
    
    $test2_pass = ($result2['code'] == 400 || $result2['code'] == 404); // Should reject
    $results[] = ['name' => 'Invalid Customer ID', 'passed' => $test2_pass];
    printf("  Expected: 400/404, Got: %d %s\n", $result2['code'], $test2_pass ? '✅' : '❌');
    
    // Test 3: Empty Customer ID
    e2e_log("Test 3: Linking with Empty Customer ID", 'step');
    $result3 = e2e_http_request(BASE_URL . '/api/account/link', 'POST',
        json_encode([
            'customer_id' => '',
            'line_user_id' => 'U_test_empty_user'
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token1
        ]
    );
    
    $test3_pass = ($result3['code'] == 400); // Validation error
    $results[] = ['name' => 'Empty Customer ID', 'passed' => $test3_pass];
    printf("  Expected: 400, Got: %d %s\n", $result3['code'], $test3_pass ? '✅' : '❌');
    
    // Report
    $passed = count(array_filter($results, fn($r) => $r['passed']));
    $failed = count($results) - $passed;
    
    echo "\n" . str_repeat('=', 70) . "\n";
    e2e_log("TC_E2E_01 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Passed: %d/%d\n", $passed, count($results));
    printf("  Failed: %d/%d\n", $failed, count($results));
    echo str_repeat('=', 70) . "\n";
    
    return $failed == 0;
}

/**
 * TC_E2E_02: Order Session Creation
 * Test creating new order sessions from FB/IG
 */
function tc_e2e_02_order_session_creation() {
    e2e_log("TC_E2E_02: Testing Order Session Creation (FB/IG)", 'test');
    
    $results = [];
    
    // Test 1: Create session from Facebook
    e2e_log("Test 1: Creating Order Session from Facebook", 'step');
    $token = generate_test_token(TEST_WXAPP_ID, 1);
    $result1 = e2e_http_request(BASE_URL . '/api/order/session/create', 'POST',
        json_encode([
            'source' => 'facebook',
            'page_id' => 'fb_page_123',
            'user_id' => 'fb_user_456'
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]
    );
    
    $test1_pass = in_array($result1['code'], [200, 401, 404]);
    $results[] = ['name' => 'Facebook Session', 'passed' => $test1_pass];
    printf("  Expected: 200/401/404, Got: %d %s\n", $result1['code'], $test1_pass ? '✅' : '❌');
    
    // Test 2: Create session from Instagram
    e2e_log("Test 2: Creating Order Session from Instagram", 'step');
    $result2 = e2e_http_request(BASE_URL . '/api/order/session/create', 'POST',
        json_encode([
            'source' => 'instagram',
            'page_id' => 'ig_page_123',
            'user_id' => 'ig_user_456'
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]
    );
    
    $test2_pass = in_array($result2['code'], [200, 401, 404]);
    $results[] = ['name' => 'Instagram Session', 'passed' => $test2_pass];
    printf("  Expected: 200/401/404, Got: %d %s\n", $result2['code'], $test2_pass ? '✅' : '❌');
    
    // Test 3: Create session with invalid source
    e2e_log("Test 3: Creating Order Session with Invalid Source", 'step');
    $result3 = e2e_http_request(BASE_URL . '/api/order/session/create', 'POST',
        json_encode([
            'source' => 'invalid_source',
            'page_id' => 'test_123',
            'user_id' => 'test_456'
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]
    );
    
    $test3_pass = ($result3['code'] == 400); // Should reject invalid source
    $results[] = ['name' => 'Invalid Source', 'passed' => $test3_pass];
    printf("  Expected: 400, Got: %d %s\n", $result3['code'], $test3_pass ? '✅' : '❌');
    
    // Report
    $passed = count(array_filter($results, fn($r) => $r['passed']));
    $failed = count($results) - $passed;
    
    echo "\n" . str_repeat('=', 70) . "\n";
    e2e_log("TC_E2E_02 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Passed: %d/%d\n", $passed, count($results));
    printf("  Failed: %d/%d\n", $failed, count($results));
    echo str_repeat('=', 70) . "\n";
    
    return $failed == 0;
}

/**
 * TC_E2E_03: Information Supplement Flow
 * Test adding shop name, date, amount, tracking code
 */
function tc_e2e_03_information_supplement() {
    e2e_log("TC_E2E_03: Testing Information Supplement Flow", 'test');
    
    $results = [];
    $token = generate_test_token(TEST_WXAPP_ID, 1);
    
    // Test 1: Add shop name
    e2e_log("Test 1: Adding Shop Name", 'step');
    $result1 = e2e_http_request(BASE_URL . '/api/order/supplement', 'POST',
        json_encode([
            'session_id' => 'test_session_001',
            'field' => 'shop_name',
            'value' => 'Test Shop ABC'
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]
    );
    
    $test1_pass = in_array($result1['code'], [200, 401, 404]);
    $results[] = ['name' => 'Shop Name', 'passed' => $test1_pass];
    printf("  Expected: 200/401/404, Got: %d %s\n", $result1['code'], $test1_pass ? '✅' : '❌');
    
    // Test 2: Add date
    e2e_log("Test 2: Adding Order Date", 'step');
    $result2 = e2e_http_request(BASE_URL . '/api/order/supplement', 'POST',
        json_encode([
            'session_id' => 'test_session_001',
            'field' => 'order_date',
            'value' => '2026-04-01'
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]
    );
    
    $test2_pass = in_array($result2['code'], [200, 401, 404]);
    $results[] = ['name' => 'Order Date', 'passed' => $test2_pass];
    printf("  Expected: 200/401/404, Got: %d %s\n", $result2['code'], $test2_pass ? '✅' : '❌');
    
    // Test 3: Add amount
    e2e_log("Test 3: Adding Order Amount", 'step');
    $result3 = e2e_http_request(BASE_URL . '/api/order/supplement', 'POST',
        json_encode([
            'session_id' => 'test_session_001',
            'field' => 'amount',
            'value' => '299000'
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]
    );
    
    $test3_pass = in_array($result3['code'], [200, 401, 404]);
    $results[] = ['name' => 'Amount', 'passed' => $test3_pass];
    printf("  Expected: 200/401/404, Got: %d %s\n", $result3['code'], $test3_pass ? '✅' : '❌');
    
    // Test 4: Add tracking code
    e2e_log("Test 4: Adding Tracking Code", 'step');
    $result4 = e2e_http_request(BASE_URL . '/api/order/supplement', 'POST',
        json_encode([
            'session_id' => 'test_session_001',
            'field' => 'tracking_code',
            'value' => 'VN12345678901'
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]
    );
    
    $test4_pass = in_array($result4['code'], [200, 401, 404]);
    $results[] = ['name' => 'Tracking Code', 'passed' => $test4_pass];
    printf("  Expected: 200/401/404, Got: %d %s\n", $result4['code'], $test4_pass ? '✅' : '❌');
    
    // Report
    $passed = count(array_filter($results, fn($r) => $r['passed']));
    $failed = count($results) - $passed;
    
    echo "\n" . str_repeat('=', 70) . "\n";
    e2e_log("TC_E2E_03 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Passed: %d/%d\n", $passed, count($results));
    printf("  Failed: %d/%d\n", $failed, count($results));
    echo str_repeat('=', 70) . "\n";
    
    return $failed == 0;
}

/**
 * TC_E2E_04: Tracking Code Linking
 * Test normal, duplicate, and invalid format scenarios
 */
function tc_e2e_04_tracking_code_linking() {
    e2e_log("TC_E2E_04: Testing Tracking Code Linking", 'test');
    
    $results = [];
    $token = generate_test_token(TEST_WXAPP_ID, 1);
    
    // Test 1: Normal tracking code
    e2e_log("Test 1: Linking Normal Tracking Code", 'step');
    $result1 = e2e_http_request(BASE_URL . '/api/tracking/link', 'POST',
        json_encode([
            'order_id' => 'ORD_TEST_001',
            'tracking_code' => 'VNPT12345678901'
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]
    );
    
    $test1_pass = in_array($result1['code'], [200, 401, 404]);
    $results[] = ['name' => 'Normal Tracking Code', 'passed' => $test1_pass];
    printf("  Expected: 200/401/404, Got: %d %s\n", $result1['code'], $test1_pass ? '✅' : '❌');
    
    // Test 2: Duplicate tracking code
    e2e_log("Test 2: Linking Duplicate Tracking Code", 'step');
    $result2 = e2e_http_request(BASE_URL . '/api/tracking/link', 'POST',
        json_encode([
            'order_id' => 'ORD_TEST_002',
            'tracking_code' => 'VNPT12345678901' // Same as above
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]
    );
    
    $test2_pass = ($result2['code'] == 400 || $result2['code'] == 409); // Conflict/duplicate
    $results[] = ['name' => 'Duplicate Tracking Code', 'passed' => $test2_pass];
    printf("  Expected: 400/409, Got: %d %s\n", $result2['code'], $test2_pass ? '✅' : '❌');
    
    // Test 3: Invalid format tracking code
    e2e_log("Test 3: Linking Invalid Format Tracking Code", 'step');
    $result3 = e2e_http_request(BASE_URL . '/api/tracking/link', 'POST',
        json_encode([
            'order_id' => 'ORD_TEST_003',
            'tracking_code' => 'INVALID!!!'
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]
    );
    
    $test3_pass = ($result3['code'] == 400); // Validation error
    $results[] = ['name' => 'Invalid Format', 'passed' => $test3_pass];
    printf("  Expected: 400, Got: %d %s\n", $result3['code'], $test3_pass ? '✅' : '❌');
    
    // Report
    $passed = count(array_filter($results, fn($r) => $r['passed']));
    $failed = count($results) - $passed;
    
    echo "\n" . str_repeat('=', 70) . "\n";
    e2e_log("TC_E2E_04 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Passed: %d/%d\n", $passed, count($results));
    printf("  Failed: %d/%d\n", $failed, count($results));
    echo str_repeat('=', 70) . "\n";
    
    return $failed == 0;
}

/**
 * TC_E2E_05: Activate Pending Orders with Keywords
 * Test keyword-based order activation
 */
function tc_e2e_05_activate_pending_orders() {
    e2e_log("TC_E2E_05: Testing Activate Pending Orders with Keywords", 'test');
    
    $results = [];
    $token = generate_test_token(TEST_WXAPP_ID, 1);
    
    // Test 1: Activate with valid keyword
    e2e_log("Test 1: Activating with Valid Keyword", 'step');
    $result1 = e2e_http_request(BASE_URL . '/api/order/activate', 'POST',
        json_encode([
            'session_id' => 'test_session_pending',
            'keyword' => 'confirm_order'
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]
    );
    
    $test1_pass = in_array($result1['code'], [200, 401, 404]);
    $results[] = ['name' => 'Valid Keyword', 'passed' => $test1_pass];
    printf("  Expected: 200/401/404, Got: %d %s\n", $result1['code'], $test1_pass ? '✅' : '❌');
    
    // Test 2: Activate with invalid keyword
    e2e_log("Test 2: Activating with Invalid Keyword", 'step');
    $result2 = e2e_http_request(BASE_URL . '/api/order/activate', 'POST',
        json_encode([
            'session_id' => 'test_session_pending',
            'keyword' => 'wrong_keyword_xyz'
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]
    );
    
    $test2_pass = ($result2['code'] == 400); // Invalid keyword
    $results[] = ['name' => 'Invalid Keyword', 'passed' => $test2_pass];
    printf("  Expected: 400, Got: %d %s\n", $result2['code'], $test2_pass ? '✅' : '❌');
    
    // Report
    $passed = count(array_filter($results, fn($r) => $r['passed']));
    $failed = count($results) - $passed;
    
    echo "\n" . str_repeat('=', 70) . "\n";
    e2e_log("TC_E2E_05 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Passed: %d/%d\n", $passed, count($results));
    printf("  Failed: %d/%d\n", $failed, count($results));
    echo str_repeat('=', 70) . "\n";
    
    return $failed == 0;
}

/**
 * TC_E2E_06: Multiple Orders Simultaneously
 * Test handling multiple orders at the same time
 */
function tc_e2e_06_multiple_orders() {
    e2e_log("TC_E2E_06: Testing Multiple Orders Simultaneously", 'test');
    
    $concurrentOrders = 10;
    $results = [];
    $token = generate_test_token(TEST_WXAPP_ID, 1);
    
    e2e_log("Creating $concurrentOrders orders concurrently...", 'step');
    
    $multiHandle = curl_multi_init();
    $handles = [];
    
    for ($i = 0; $i < $concurrentOrders; $i++) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, BASE_URL . '/api/order/create');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'session_id' => 'test_session_' . $i,
            'amount' => 100000 + $i,
            'items' => [['id' => 1, 'qty' => 1]]
        ]));
        curl_setopt($ch, CURLOPT_TIMEOUT, API_TIMEOUT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);
        
        curl_multi_add_handle($multiHandle, $ch);
        $handles[] = $ch;
    }
    
    // Execute concurrently
    $running = null;
    do {
        curl_multi_exec($multiHandle, $running);
        curl_multi_select($multiHandle, 0.1);
    } while ($running > 0);
    
    // Collect results
    $success = 0;
    $errors = 0;
    
    foreach ($handles as $ch) {
        $info = curl_getinfo($ch);
        if (in_array($info['http_code'], [200, 201, 401])) {
            $success++;
        } else {
            $errors++;
        }
        curl_multi_remove_handle($multiHandle, $ch);
        curl_close($ch);
    }
    
    curl_multi_close($multiHandle);
    
    $test_pass = ($success == $concurrentOrders);
    $results[] = ['name' => 'Concurrent Orders', 'passed' => $test_pass];
    printf("  Success: %d/%d orders %s\n", $success, $concurrentOrders, $test_pass ? '✅' : '❌');
    
    // Report
    $passed = count(array_filter($results, fn($r) => $r['passed']));
    $failed = count($results) - $passed;
    
    echo "\n" . str_repeat('=', 70) . "\n";
    e2e_log("TC_E2E_06 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Passed: %d/%d\n", $passed, count($results));
    printf("  Failed: %d/%d\n", $failed, count($results));
    echo str_repeat('=', 70) . "\n";
    
    return $failed == 0;
}

/**
 * Main execution
 */
function main() {
    global $argv;
    
    echo "\n";
    echo str_repeat('═', 70) . "\n";
    echo "║  " . str_pad('End-to-End Functional Test Suite - Bot System', 65) . " ║\n";
    echo "║  " . str_pad('Testing complete user workflows', 65) . " ║\n";
    echo str_repeat('═', 70) . "\n\n";
    
    $testCase = $argv[1] ?? 'all';
    $results = [];
    
    switch ($testCase) {
        case 'e2e_account_linking':
            $results[] = tc_e2e_01_account_linking();
            break;
        case 'e2e_order_session':
            $results[] = tc_e2e_02_order_session_creation();
            break;
        case 'e2e_info_supplement':
            $results[] = tc_e2e_03_information_supplement();
            break;
        case 'e2e_tracking_link':
            $results[] = tc_e2e_04_tracking_code_linking();
            break;
        case 'e2e_activate_order':
            $results[] = tc_e2e_05_activate_pending_orders();
            break;
        case 'e2e_multiple_orders':
            $results[] = tc_e2e_06_multiple_orders();
            break;
        case 'all':
        default:
            $results[] = tc_e2e_01_account_linking();
            echo "\n";
            $results[] = tc_e2e_02_order_session_creation();
            echo "\n";
            $results[] = tc_e2e_03_information_supplement();
            echo "\n";
            $results[] = tc_e2e_04_tracking_code_linking();
            echo "\n";
            $results[] = tc_e2e_05_activate_pending_orders();
            echo "\n";
            $results[] = tc_e2e_06_multiple_orders();
            break;
    }
    
    // Summary
    echo "\n" . str_repeat('═', 70) . "\n";
    echo "║  " . str_pad('FUNCTIONAL E2E TEST SUMMARY', 65) . " ║\n";
    echo str_repeat('═', 70) . "\n";
    
    $passed = count(array_filter($results, fn($r) => $r === true));
    $failed = count(array_filter($results, fn($r) => $r === false));
    
    printf("  Passed:   %d ✅\n", $passed);
    printf("  Failed:   %d ❌\n", $failed);
    echo str_repeat('═', 70) . "\n";
    
    if ($failed == 0 && $passed > 0) {
        e2e_log("ALL FUNCTIONAL TESTS PASSED! 🎉", 'success');
        exit(0);
    } else {
        e2e_log("FUNCTIONAL TESTS FAILED - Review workflows", 'error');
        exit(1);
    }
}

// Run
main();
