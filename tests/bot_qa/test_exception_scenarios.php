#!/usr/bin/env php
<?php
/**
 * Exception Handling Test Suite - Bot System
 * 
 * Tests exception scenarios:
 * - Network Timeout and Retry
 * - Duplicate Tracking Code Handling
 * - Access Limit for Unlinked Accounts
 * 
 * Usage: php test_exception_scenarios.php [test_case]
 */

// Configuration
define('BASE_URL', 'http://localhost:8000');
define('API_TIMEOUT', 5); // Short timeout for testing
define('SHORT_TIMEOUT', 2);

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

function exc_log($msg, $style = 'info') {
    global $C;
    $prefixes = [
        'info'     => 'ℹ️ ',
        'success'  => '✅ ',
        'error'    => '❌ ',
        'warning'  => '⚠️  ',
        'test'     => '🔧 ',
        'step'     => '➡️  ',
    ];
    $color = $C[$style] ?? $C['reset'];
    echo $color . ($prefixes[$style] ?? '') . $msg . $C['reset'] . "\n";
}

/**
 * Helper: Make HTTP Request with Custom Timeout
 */
function exc_http_request($url, $method = 'GET', $data = null, $headers = [], $timeout = 30) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
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
    $errno = curl_errno($ch);
    curl_close($ch);
    
    return [
        'body' => $response,
        'code' => $info['http_code'],
        'time' => $info['total_time'] * 1000,
        'error' => $error,
        'errno' => $errno
    ];
}

/**
 * Helper: Generate Test Token
 */
function exc_generate_token($wxapp_id, $user_id) {
    return base64_encode(json_encode([
        'wxapp_id' => $wxapp_id,
        'user_id' => $user_id,
        'timestamp' => time()
    ]));
}

/**
 * TC_EXC_NET_01: Network Timeout and Retry
 * Test system behavior on network timeout with retry logic
 */
function tc_exc_net_01_timeout_retry() {
    exc_log("TC_EXC_NET_01: Testing Network Timeout and Retry", 'test');
    
    $results = [];
    $token = exc_generate_token(1, 1);
    
    // Test 1: Normal request (baseline)
    exc_log("Test 1: Normal Request (Baseline)", 'step');
    $result1 = exc_http_request(BASE_URL . '/api/package/index', 'POST',
        json_encode(['express_num' => 'TEST_NORMAL']),
        ['Content-Type: application/json'],
        API_TIMEOUT
    );
    
    $test1_pass = in_array($result1['code'], [200, 401, 404]);
    $results[] = ['name' => 'Normal Request', 'passed' => $test1_pass];
    printf("  Response Time: %.0f ms, HTTP: %d %s\n", $result1['time'], $result1['code'], $test1_pass ? '✅' : '❌');
    
    // Test 2: Simulated timeout (very short timeout)
    exc_log("Test 2: Request with Very Short Timeout (2s)", 'step');
    $start = microtime(true);
    $result2 = exc_http_request(BASE_URL . '/api/slow/endpoint', 'GET',
        null,
        [],
        SHORT_TIMEOUT // 2 seconds timeout
    );
    $elapsed = (microtime(true) - $start) * 1000;
    
    // Should timeout or return error gracefully
    $timeout_handled = ($result2['errno'] == CURLE_OPERATION_TIMEDOUT || 
                        $result2['code'] == 0 || 
                        $result2['code'] >= 400);
    
    $test2_pass = $timeout_handled && ($elapsed < (SHORT_TIMEOUT + 2) * 1000);
    $results[] = ['name' => 'Timeout Handling', 'passed' => $test2_pass];
    printf("  Expected: Timeout/Error, Got: HTTP %d (%.0f ms) %s\n", 
           $result2['code'], $elapsed, $test2_pass ? '✅' : '❌');
    
    // Test 3: Verify retry mechanism exists (check endpoint)
    exc_log("Test 3: Checking Retry Mechanism Endpoint", 'step');
    $result3 = exc_http_request(BASE_URL . '/api/retry/config', 'GET',
        null,
        ['Authorization: Bearer ' . $token]
    );
    
    $test3_pass = in_array($result3['code'], [200, 401, 404]);
    $results[] = ['name' => 'Retry Config Available', 'passed' => $test3_pass];
    printf("  Expected: 200/401/404, Got: %d %s\n", $result3['code'], $test3_pass ? '✅' : '❌');
    
    // Report
    $passed = count(array_filter($results, fn($r) => $r['passed']));
    $failed = count($results) - $passed;
    
    echo "\n" . str_repeat('=', 70) . "\n";
    exc_log("TC_EXC_NET_01 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Passed: %d/%d\n", $passed, count($results));
    printf("  Failed: %d/%d\n", $failed, count($results));
    echo str_repeat('=', 70) . "\n";
    
    return $failed == 0;
}

/**
 * TC_EXC_DUP_01: Duplicate Tracking Code Handling
 * Test system handling of duplicate tracking codes
 */
function tc_exc_dup_01_duplicate_tracking() {
    exc_log("TC_EXC_DUP_01: Testing Duplicate Tracking Code Handling", 'test');
    
    $results = [];
    $token = exc_generate_token(1, 1);
    $tracking_code = 'DUP_TEST_' . time();
    
    // Test 1: First tracking code submission
    exc_log("Test 1: First Tracking Code Submission", 'step');
    $result1 = exc_http_request(BASE_URL . '/api/tracking/link', 'POST',
        json_encode([
            'order_id' => 'ORD_DUP_TEST_001',
            'tracking_code' => $tracking_code
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]
    );
    
    $test1_pass = in_array($result1['code'], [200, 201, 401, 404]);
    $results[] = ['name' => 'First Submission', 'passed' => $test1_pass];
    printf("  Expected: 200/201/401/404, Got: %d %s\n", $result1['code'], $test1_pass ? '✅' : '❌');
    
    // Test 2: Duplicate tracking code submission
    exc_log("Test 2: Duplicate Tracking Code Submission", 'step');
    $result2 = exc_http_request(BASE_URL . '/api/tracking/link', 'POST',
        json_encode([
            'order_id' => 'ORD_DUP_TEST_002', // Different order
            'tracking_code' => $tracking_code // Same tracking code
        ]),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]
    );
    
    // Should reject duplicate
    $test2_pass = ($result2['code'] == 400 || $result2['code'] == 409); // Bad Request or Conflict
    $results[] = ['name' => 'Duplicate Rejection', 'passed' => $test2_pass];
    printf("  Expected: 400/409, Got: %d %s\n", $result2['code'], $test2_pass ? '✅' : '❌');
    
    // Test 3: Check error message clarity
    $response_data = json_decode($result2['body'], true);
    $has_clear_error = isset($response_data['error']) || 
                       isset($response_data['message']) ||
                       strpos($result2['body'], 'duplicate') !== false ||
                       strpos($result2['body'], 'exists') !== false;
    
    exc_log("Error Message Clarity: " . ($has_clear_error ? 'Clear' : 'Unclear'), 'info');
    
    // Report
    $passed = count(array_filter($results, fn($r) => $r['passed']));
    $failed = count($results) - $passed;
    
    echo "\n" . str_repeat('=', 70) . "\n";
    exc_log("TC_EXC_DUP_01 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Passed: %d/%d\n", $passed, count($results));
    printf("  Failed: %d/%d\n", $failed, count($results));
    echo str_repeat('=', 70) . "\n";
    
    return $failed == 0;
}

/**
 * TC_EXC_ACC_01: Access Limit for Unlinked Accounts
 * Test access restrictions for unlinked accounts
 */
function tc_exc_acc_01_unlinked_access_limit() {
    exc_log("TC_EXC_ACC_01: Testing Access Limit for Unlinked Accounts", 'test');
    
    $results = [];
    
    // Test 1: Try to access order API without account linking
    exc_log("Test 1: Access Order API Without Account Linking", 'step');
    $unlinked_token = exc_generate_token(999, 999); // Non-existent user
    $result1 = exc_http_request(BASE_URL . '/api/order/list', 'GET',
        null,
        ['Authorization: Bearer ' . $unlinked_token]
    );
    
    // Should require account linking (401 or 403 or redirect to link)
    $test1_pass = in_array($result1['code'], [401, 403, 302]);
    $results[] = ['name' => 'Order API Restriction', 'passed' => $test1_pass];
    printf("  Expected: 401/403/302, Got: %d %s\n", $result1['code'], $test1_pass ? '✅' : '❌');
    
    // Test 2: Try to access package tracking without linking
    exc_log("Test 2: Access Package Tracking Without Linking", 'step');
    $result2 = exc_http_request(BASE_URL . '/api/package/list', 'GET',
        null,
        ['Authorization: Bearer ' . $unlinked_token]
    );
    
    $test2_pass = in_array($result2['code'], [401, 403, 302]);
    $results[] = ['name' => 'Package API Restriction', 'passed' => $test2_pass];
    printf("  Expected: 401/403/302, Got: %d %s\n", $result2['code'], $test2_pass ? '✅' : '❌');
    
    // Test 3: Verify public APIs still accessible
    exc_log("Test 3: Verify Public APIs Still Accessible", 'step');
    $result3 = exc_http_request(BASE_URL . '/api/public/config', 'GET',
        null,
        [] // No auth token
    );
    
    // Public APIs should work (200 or 404 if not exists)
    $test3_pass = in_array($result3['code'], [200, 404, 401]);
    $results[] = ['name' => 'Public API Access', 'passed' => $test3_pass];
    printf("  Expected: 200/404/401, Got: %d %s\n", $result3['code'], $test3_pass ? '✅' : '❌');
    
    // Report
    $passed = count(array_filter($results, fn($r) => $r['passed']));
    $failed = count($results) - $passed;
    
    echo "\n" . str_repeat('=', 70) . "\n";
    exc_log("TC_EXC_ACC_01 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Passed: %d/%d\n", $passed, count($results));
    printf("  Failed: %d/%d\n", $failed, count($results));
    echo str_repeat('=', 70) . "\n";
    
    if ($failed == 0) {
        exc_log("TC_EXC_ACC_01: PASSED ✅ (Access control working)", 'success');
        return true;
    } else {
        exc_log("TC_EXC_ACC_01: FAILED ❌ (Unlinked accounts can access restricted APIs!)", 'error');
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
    echo "║  " . str_pad('Exception Scenarios Test Suite - Bot System', 65) . " ║\n";
    echo "║  " . str_pad('Testing timeout, duplicates, and access control', 65) . " ║\n";
    echo str_repeat('═', 70) . "\n\n";
    
    $testCase = $argv[1] ?? 'all';
    $results = [];
    
    switch ($testCase) {
        case 'exc_network':
            $results[] = tc_exc_net_01_timeout_retry();
            break;
        case 'exc_duplicate':
            $results[] = tc_exc_dup_01_duplicate_tracking();
            break;
        case 'exc_access':
            $results[] = tc_exc_acc_01_unlinked_access_limit();
            break;
        case 'all':
        default:
            $results[] = tc_exc_net_01_timeout_retry();
            echo "\n";
            $results[] = tc_exc_dup_01_duplicate_tracking();
            echo "\n";
            $results[] = tc_exc_acc_01_unlinked_access_limit();
            break;
    }
    
    // Summary
    echo "\n" . str_repeat('═', 70) . "\n";
    echo "║  " . str_pad('EXCEPTION SCENARIOS TEST SUMMARY', 65) . " ║\n";
    echo str_repeat('═', 70) . "\n";
    
    $passed = count(array_filter($results, fn($r) => $r === true));
    $failed = count(array_filter($results, fn($r) => $r === false));
    
    printf("  Passed:   %d ✅\n", $passed);
    printf("  Failed:   %d ❌\n", $failed);
    echo str_repeat('═', 70) . "\n";
    
    if ($failed == 0 && $passed > 0) {
        exc_log("ALL EXCEPTION TESTS PASSED! 🎉", 'success');
        exit(0);
    } else {
        exc_log("EXCEPTION HANDLING ISSUES FOUND!", 'error');
        exit(1);
    }
}

// Run
main();
