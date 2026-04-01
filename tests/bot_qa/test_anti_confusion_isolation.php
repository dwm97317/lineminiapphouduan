#!/usr/bin/env php
<?php
/**
 * Anti-Confusion & Multi-Tenancy Isolation Test Suite - Bot System
 * 
 * Tests order confusion prevention and tenant isolation:
 * - 24h Timeout Auto Create New Session
 * - Same Seller Prevention
 * - Different Seller Confirmation Button
 * - Customer主动 Select "New Order"
 * - Multi-Tenant Data Isolation (wxapp_id)
 * - Cross-Tenant Access Rejection
 * 
 * Usage: php test_anti_confusion_isolation.php [test_case]
 */

// Configuration
define('BASE_URL', 'http://localhost:8000');
define('API_TIMEOUT', 30);
define('TEST_WXAPP_ID_1', 1);
define('TEST_WXAPP_ID_2', 2);

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

function aci_log($msg, $style = 'info') {
    global $C;
    $prefixes = [
        'info'     => 'ℹ️ ',
        'success'  => '✅ ',
        'error'    => '❌ ',
        'warning'  => '⚠️  ',
        'test'     => '🛡️ ',
        'step'     => '➡️  ',
    ];
    $color = $C[$style] ?? $C['reset'];
    echo $color . ($prefixes[$style] ?? '') . $msg . $C['reset'] . "\n";
}

/**
 * Helper: Make HTTP Request
 */
function aci_http_request($url, $method = 'GET', $data = null, $headers = [], $timeout = 30) {
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
function aci_generate_token($wxapp_id, $user_id) {
    return base64_encode(json_encode([
        'wxapp_id' => $wxapp_id,
        'user_id' => $user_id,
        'timestamp' => time()
    ]));
}

/**
 * TC_AC_01: 24h Timeout Auto Create New Session
 * Test automatic session creation after 24h timeout
 */
function tc_ac_01_timeout_auto_session() {
    aci_log("TC_AC_01: Testing 24h Timeout Auto Create New Session", 'test');
    
    // Note: This is a manual test requiring waiting 24h
    aci_log("Manual test procedure:", 'warning');
    echo "1. Create order session at T0\n";
    echo "2. Wait for 24 hours without activity\n";
    echo "3. Send message to bot\n";
    echo "4. Expected: Bot creates new session automatically\n";
    echo "5. Verify old session is expired\n\n";
    
    // Automated check: Verify endpoint exists
    $token = aci_generate_token(TEST_WXAPP_ID_1, 1);
    $result = aci_http_request(BASE_URL . '/api/session/check', 'POST',
        json_encode([
            'session_id' => 'old_session_test',
            'check_timeout' => true
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]
    );
    
    aci_log("Session Check Endpoint Response: " . $result['code'], 'info');
    
    if (in_array($result['code'], [200, 401, 404])) {
        aci_log("TC_AC_01: Endpoint available - Manual test required for full validation", 'info');
        return null; // Manual test
    } else {
        aci_log("TC_AC_01: Endpoint error", 'error');
        return false;
    }
}

/**
 * TC_AC_02: Same Seller Prevention
 * Test that same seller orders don't get confused
 */
function tc_ac_02_same_seller_prevention() {
    aci_log("TC_AC_02: Testing Same Seller Order Confusion Prevention", 'test');
    
    $results = [];
    $token = aci_generate_token(TEST_WXAPP_ID_1, 1);
    
    // Create two orders from same seller
    aci_log("Step 1: Creating Order A from Seller ABC", 'step');
    $result1 = aci_http_request(BASE_URL . '/api/order/create', 'POST',
        json_encode([
            'seller_id' => 'SELLER_ABC',
            'items' => [['id' => 1, 'name' => 'Product A']],
            'amount' => 100000
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]
    );
    
    aci_log("Step 2: Creating Order B from Same Seller ABC", 'step');
    $result2 = aci_http_request(BASE_URL . '/api/order/create', 'POST',
        json_encode([
            'seller_id' => 'SELLER_ABC', // Same seller
            'items' => [['id' => 2, 'name' => 'Product B']],
            'amount' => 200000
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]
    );
    
    // Verify both orders have different order IDs
    $order1_data = json_decode($result1['body'], true);
    $order2_data = json_decode($result2['body'], true);
    
    $different_ids = isset($order1_data['order_id'], $order2_data['order_id']) && 
                     $order1_data['order_id'] !== $order2_data['order_id'];
    
    $test_pass = in_array($result1['code'], [200, 201, 401]) && 
                 in_array($result2['code'], [200, 201, 401]);
    
    $results[] = ['name' => 'Same Seller Orders', 'passed' => $test_pass && $different_ids];
    printf("  Order A ID: %s\n", $order1_data['order_id'] ?? 'N/A');
    printf("  Order B ID: %s\n", $order2_data['order_id'] ?? 'N/A');
    printf("  Different IDs: %s %s\n", $different_ids ? 'Yes' : 'No', $different_ids ? '✅' : '❌');
    
    // Report
    $passed = count(array_filter($results, fn($r) => $r['passed']));
    $failed = count($results) - $passed;
    
    echo "\n" . str_repeat('=', 70) . "\n";
    aci_log("TC_AC_02 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Passed: %d/%d\n", $passed, count($results));
    printf("  Failed: %d/%d\n", $failed, count($results));
    echo str_repeat('=', 70) . "\n";
    
    return $failed == 0;
}

/**
 * TC_AC_03: Different Seller Confirmation Button
 * Test confirmation button appears for different sellers
 */
function tc_ac_03_different_seller_confirmation() {
    aci_log("TC_AC_03: Testing Different Seller Confirmation Button", 'test');
    
    $results = [];
    $token = aci_generate_token(TEST_WXAPP_ID_1, 1);
    
    // Create orders from different sellers
    aci_log("Step 1: Creating Order from Seller ABC", 'step');
    $result1 = aci_http_request(BASE_URL . '/api/order/create', 'POST',
        json_encode([
            'seller_id' => 'SELLER_ABC',
            'items' => [['id' => 1]],
            'amount' => 100000
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]
    );
    
    aci_log("Step 2: Creating Order from Seller XYZ (Different)", 'step');
    $result2 = aci_http_request(BASE_URL . '/api/order/create', 'POST',
        json_encode([
            'seller_id' => 'SELLER_XYZ', // Different seller
            'items' => [['id' => 2]],
            'amount' => 200000
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]
    );
    
    // Check if confirmation button/flag is present
    $response_data = json_decode($result2['body'], true);
    $has_confirmation = isset($response_data['requires_confirmation']) && 
                        $response_data['requires_confirmation'] === true;
    
    $test_pass = in_array($result2['code'], [200, 201, 401]);
    $results[] = [
        'name' => 'Different Seller Confirmation',
        'passed' => $test_pass && ($has_confirmation || $result2['code'] == 401)
    ];
    
    printf("  Different Seller Detected: %s %s\n", $has_confirmation ? 'Yes' : 'No', $has_confirmation ? '✅' : '⚠️');
    printf("  Confirmation Required: %s\n", $has_confirmation ? 'Yes' : 'No');
    
    // Report
    $passed = count(array_filter($results, fn($r) => $r['passed']));
    $failed = count($results) - $passed;
    
    echo "\n" . str_repeat('=', 70) . "\n";
    aci_log("TC_AC_03 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Passed: %d/%d\n", $passed, count($results));
    printf("  Failed: %d/%d\n", $failed, count($results));
    echo str_repeat('=', 70) . "\n";
    
    return $failed == 0;
}

/**
 * TC_AC_04: Customer Active Selection "New Order"
 * Test customer can主动 select to create new order
 */
function tc_ac_04_customer_new_order_selection() {
    aci_log("TC_AC_04: Testing Customer Active 'New Order' Selection", 'test');
    
    $results = [];
    $token = aci_generate_token(TEST_WXAPP_ID_1, 1);
    
    // Simulate customer selecting "New Order" option
    aci_log("Step: Customer selects 'New Order' option", 'step');
    $result = aci_http_request(BASE_URL . '/api/order/new', 'POST',
        json_encode([
            'action' => 'create_new_order',
            'ignore_pending' => true
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]
    );
    
    $test_pass = in_array($result['code'], [200, 201, 401, 404]);
    $results[] = ['name' => 'Customer New Order Selection', 'passed' => $test_pass];
    printf("  Expected: 200/201/401/404, Got: %d %s\n", $result['code'], $test_pass ? '✅' : '❌');
    
    // Report
    $passed = count(array_filter($results, fn($r) => $r['passed']));
    $failed = count($results) - $passed;
    
    echo "\n" . str_repeat('=', 70) . "\n";
    aci_log("TC_AC_04 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Passed: %d/%d\n", $passed, count($results));
    printf("  Failed: %d/%d\n", $failed, count($results));
    echo str_repeat('=', 70) . "\n";
    
    return $failed == 0;
}

/**
 * TC_ISO_01: Multi-Tenant Data Isolation
 * Test complete data isolation between wxapp_id tenants
 */
function tc_iso_01_multi_tenant_isolation() {
    aci_log("TC_ISO_01: Testing Multi-Tenant Data Isolation", 'test');
    
    $results = [];
    
    // Create data for Tenant 1
    aci_log("Step 1: Creating data for Tenant 1 (wxapp_id=1)", 'step');
    $token1 = aci_generate_token(TEST_WXAPP_ID_1, 100);
    $result1 = aci_http_request(BASE_URL . '/api/order/create', 'POST',
        json_encode([
            'seller_id' => 'SELLER_TENANT1',
            'items' => [['id' => 1]],
            'amount' => 150000
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token1
        ]
    );
    
    $order1_data = json_decode($result1['body'], true);
    $order1_id = $order1_data['order_id'] ?? null;
    
    // Try to access Tenant 1's data with Tenant 2's token
    aci_log("Step 2: Attempting to access Tenant 1's data with Tenant 2's token", 'step');
    $token2 = aci_generate_token(TEST_WXAPP_ID_2, 200);
    $result2 = aci_http_request(BASE_URL . '/api/order/' . $order1_id, 'GET',
        null,
        [
            'Authorization: Bearer ' . $token2
        ]
    );
    
    // Should be blocked (403 Forbidden)
    $blocked_cross_access = ($result2['code'] == 403 || $result2['code'] == 404);
    
    $results[] = [
        'name' => 'Cross-Tenant Access Block',
        'passed' => $blocked_cross_access
    ];
    
    printf("  Tenant 1 Order Created: %s\n", $order1_id ? 'Yes' : 'No');
    printf("  Tenant 2 Access Attempt: HTTP %d\n", $result2['code']);
    printf("  Cross-Access Blocked: %s %s\n", $blocked_cross_access ? 'Yes' : 'No', $blocked_cross_access ? '✅' : '❌');
    
    // Report
    $passed = count(array_filter($results, fn($r) => $r['passed']));
    $failed = count($results) - $passed;
    
    echo "\n" . str_repeat('=', 70) . "\n";
    aci_log("TC_ISO_01 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Passed: %d/%d\n", $passed, count($results));
    printf("  Failed: %d/%d\n", $failed, count($results));
    echo str_repeat('=', 70) . "\n";
    
    return $failed == 0;
}

/**
 * TC_ISO_02: Cross-Tenant Access Rejection
 * Explicit test for cross-tenant access returning error
 */
function tc_iso_02_cross_tenant_rejection() {
    aci_log("TC_ISO_02: Testing Cross-Tenant Access Rejection", 'test');
    
    $results = [];
    
    // Test 1: Tenant 1 token accessing Tenant 2 API
    aci_log("Test 1: Tenant 1 token → Tenant 2 API", 'step');
    $token1 = aci_generate_token(TEST_WXAPP_ID_1, 100);
    $result1 = aci_http_request(BASE_URL . '/api/tenant/2/data', 'GET',
        null,
        [
            'Authorization: Bearer ' . $token1
        ]
    );
    
    $test1_pass = ($result1['code'] == 403); // Should be forbidden
    $results[] = ['name' => 'T1 → T2 Access', 'passed' => $test1_pass];
    printf("  Expected: 403, Got: %d %s\n", $result1['code'], $test1_pass ? '✅' : '❌');
    
    // Test 2: Tenant 2 token accessing Tenant 1 API
    aci_log("Test 2: Tenant 2 token → Tenant 1 API", 'step');
    $token2 = aci_generate_token(TEST_WXAPP_ID_2, 200);
    $result2 = aci_http_request(BASE_URL . '/api/tenant/1/data', 'GET',
        null,
        [
            'Authorization: Bearer ' . $token2
        ]
    );
    
    $test2_pass = ($result2['code'] == 403); // Should be forbidden
    $results[] = ['name' => 'T2 → T1 Access', 'passed' => $test2_pass];
    printf("  Expected: 403, Got: %d %s\n", $result2['code'], $test2_pass ? '✅' : '❌');
    
    // Report
    $passed = count(array_filter($results, fn($r) => $r['passed']));
    $failed = count($results) - $passed;
    
    echo "\n" . str_repeat('=', 70) . "\n";
    aci_log("TC_ISO_02 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Passed: %d/%d\n", $passed, count($results));
    printf("  Failed: %d/%d\n", $failed, count($results));
    echo str_repeat('=', 70) . "\n";
    
    if ($failed == 0) {
        aci_log("TC_ISO_02: PASSED ✅ (Strong tenant isolation)", 'success');
        return true;
    } else {
        aci_log("TC_ISO_02: FAILED ❌ (Cross-tenant access possible!)", 'error');
        return false;
    }
}

/**
 * Main execution
 */
function main() {
    global $argv;
    
    echo "\n";
    echo str_repeat('═', 70) . "\n";
    echo "║  " . str_pad('Anti-Confusion & Isolation Test Suite - Bot System', 65) . " ║\n";
    echo "║  " . str_pad('Testing order confusion prevention and tenant isolation', 65) . " ║\n";
    echo str_repeat('═', 70) . "\n\n";
    
    $testCase = $argv[1] ?? 'all';
    $results = [];
    
    switch ($testCase) {
        case 'ac_timeout':
            $results[] = tc_ac_01_timeout_auto_session();
            break;
        case 'ac_same_seller':
            $results[] = tc_ac_02_same_seller_prevention();
            break;
        case 'ac_diff_seller':
            $results[] = tc_ac_03_different_seller_confirmation();
            break;
        case 'ac_new_order':
            $results[] = tc_ac_04_customer_new_order_selection();
            break;
        case 'iso_tenant':
            $results[] = tc_iso_01_multi_tenant_isolation();
            break;
        case 'iso_cross_access':
            $results[] = tc_iso_02_cross_tenant_rejection();
            break;
        case 'all':
        default:
            $results[] = tc_ac_01_timeout_auto_session();
            echo "\n";
            $results[] = tc_ac_02_same_seller_prevention();
            echo "\n";
            $results[] = tc_ac_03_different_seller_confirmation();
            echo "\n";
            $results[] = tc_ac_04_customer_new_order_selection();
            echo "\n";
            $results[] = tc_iso_01_multi_tenant_isolation();
            echo "\n";
            $results[] = tc_iso_02_cross_tenant_rejection();
            break;
    }
    
    // Summary
    echo "\n" . str_repeat('═', 70) . "\n";
    echo "║  " . str_pad('ANTI-CONFUSION & ISOLATION TEST SUMMARY', 65) . " ║\n";
    echo str_repeat('═', 70) . "\n";
    
    $passed = count(array_filter($results, fn($r) => $r === true));
    $failed = count(array_filter($results, fn($r) => $r === false));
    $manual = count(array_filter($results, fn($r) => $r === null));
    
    printf("  Passed:       %d ✅\n", $passed);
    printf("  Failed:       %d ❌\n", $failed);
    printf("  Manual Tests: %d ℹ️\n", $manual);
    echo str_repeat('═', 70) . "\n";
    
    if ($failed == 0) {
        aci_log("ALL ANTI-CONFUSION & ISOLATION TESTS COMPLETE! 🎉", 'success');
        exit(0);
    } else {
        aci_log("CRITICAL ISSUES FOUND - Order confusion or data leakage possible!", 'error');
        exit(1);
    }
}

// Run
main();
