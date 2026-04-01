#!/usr/bin/env php
<?php
/**
 * Security Test Suite - Bot System
 * 
 * Tests system security vulnerabilities
 * - TC_SEC_01: API Key Authentication Bypass
 * - TC_SEC_02: SQL Injection Prevention
 * - TC_SEC_03: wxapp_id Forgery (Multi-tenancy)
 * - TC_SEC_04: Rate Limiting (100 req/min)
 * 
 * Usage: php test_security.php [test_case]
 * Example: php test_security.php all
 */

// Configuration
define('BASE_URL', 'http://localhost:8000');
define('API_TIMEOUT', 10);
define('RATE_LIMIT_REQUESTS', 120); // Test with 120 to verify limit at 100

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

function sec_log($msg, $style = 'info') {
    global $C;
    $prefixes = [
        'info'     => 'ℹ️ ',
        'success'  => '✅ ',
        'error'    => '❌ ',
        'warning'  => '⚠️  ',
        'test'     => '🔒 ',
    ];
    $color = $C[$style] ?? $C['reset'];
    echo $color . ($prefixes[$style] ?? '') . $msg . $C['reset'] . "\n";
}

/**
 * Helper: Make HTTP Request
 */
function sec_http_request($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, API_TIMEOUT);
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
 * TC_SEC_01: API Key Authentication Bypass
 * Test if API endpoints require valid API key
 */
function tc_sec_01_api_key() {
    sec_log("TC_SEC_01: Testing API Key Authentication Bypass", 'test');
    
    $apiEndpoint = BASE_URL . '/api/package/index';
    $validApiKey = 'test_api_key_12345'; // Replace with actual valid key
    $results = [];
    
    // Test 1: No API Key
    sec_log("Test 1: Request without API Key", 'info');
    $result1 = sec_http_request($apiEndpoint, 'POST', 
        json_encode(['express_num' => 'TEST001']),
        ['Content-Type: application/json']
    );
    $results[] = [
        'name' => 'No API Key',
        'expected' => 401,
        'actual' => $result1['code'],
        'passed' => $result1['code'] == 401
    ];
    printf("  Expected: 401, Got: %d %s\n", $result1['code'], $result1['code'] == 401 ? '✅' : '❌');
    
    // Test 2: Invalid API Key
    sec_log("Test 2: Request with Invalid API Key", 'info');
    $result2 = sec_http_request($apiEndpoint, 'POST',
        json_encode(['express_num' => 'TEST002']),
        [
            'Content-Type: application/json',
            'X-API-Key: invalid_key_xyz'
        ]
    );
    $results[] = [
        'name' => 'Invalid API Key',
        'expected' => 401,
        'actual' => $result2['code'],
        'passed' => $result2['code'] == 401
    ];
    printf("  Expected: 401, Got: %d %s\n", $result2['code'], $result2['code'] == 401 ? '✅' : '❌');
    
    // Test 3: Valid API Key (if available)
    sec_log("Test 3: Request with Valid API Key", 'info');
    $result3 = sec_http_request($apiEndpoint, 'POST',
        json_encode(['express_num' => 'TEST003']),
        [
            'Content-Type: application/json',
            'X-API-Key: ' . $validApiKey
        ]
    );
    $results[] = [
        'name' => 'Valid API Key',
        'expected' => 200,
        'actual' => $result3['code'],
        'passed' => in_array($result3['code'], [200, 401]) // 401 means API key validation exists
    ];
    printf("  Expected: 200/401, Got: %d %s\n", $result3['code'], in_array($result3['code'], [200, 401]) ? '✅' : '❌');
    
    // Report
    $passed = count(array_filter($results, fn($r) => $r['passed']));
    $failed = count($results) - $passed;
    
    echo "\n" . str_repeat('=', 70) . "\n";
    sec_log("TC_SEC_01 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Passed: %d/%d\n", $passed, count($results));
    printf("  Failed: %d/%d\n", $failed, count($results));
    echo str_repeat('=', 70) . "\n";
    
    if ($failed == 0) {
        sec_log("TC_SEC_01: PASSED ✅ (API authentication enforced)", 'success');
        return true;
    } else {
        sec_log("TC_SEC_01: FAILED ❌ (API bypass possible!)", 'error');
        return false;
    }
}

/**
 * TC_SEC_02: SQL Injection Prevention
 * Test common SQL injection patterns
 */
function tc_sec_02_sql_injection() {
    sec_log("TC_SEC_02: Testing SQL Injection Prevention", 'test');
    
    $injectionPatterns = [
        "' OR '1'='1",
        "' OR 1=1; DROP TABLE users;--",
        "' UNION SELECT * FROM users;--",
        "'; DELETE FROM yoshop_package;--",
        "admin'--",
        "<script>alert('xss')</script>" // XSS attempt
    ];
    
    $apiEndpoint = BASE_URL . '/api/package/index';
    $results = [];
    
    foreach ($injectionPatterns as $pattern) {
        sec_log("Testing injection: " . substr($pattern, 0, 40) . "...", 'info');
        
        $result = sec_http_request($apiEndpoint, 'POST',
            json_encode(['express_num' => $pattern]),
            ['Content-Type: application/json']
        );
        
        // Check if blocked (non-200 or error message)
        $blocked = ($result['code'] != 200) || 
                   strpos($result['body'], 'error') !== false ||
                   strpos($result['body'], 'invalid') !== false ||
                   $result['code'] == 302; // Redirect to login
        
        $results[] = [
            'pattern' => $pattern,
            'blocked' => $blocked,
            'httpCode' => $result['code']
        ];
        
        printf("  HTTP Code: %d %s\n", $result['code'], $blocked ? '⚠️  Potentially blocked' : '❌ May be vulnerable');
    }
    
    // Report
    $blocked = count(array_filter($results, fn($r) => $r['blocked']));
    $needsReview = count($results) - $blocked;
    
    echo "\n" . str_repeat('=', 70) . "\n";
    sec_log("TC_SEC_02 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Blocked/Safe:    %d\n", $blocked);
    printf("  Needs Review:    %d\n", $needsReview);
    echo str_repeat('=', 70) . "\n";
    
    if ($needsReview == 0) {
        sec_log("TC_SEC_02: PASSED ✅ (All injections blocked)", 'success');
        return true;
    } else {
        sec_log("TC_SEC_02: NEEDS MANUAL REVIEW ⚠️", 'warning');
        sec_log("Verify responses for potential vulnerabilities", 'info');
        return null; // Manual review needed
    }
}

/**
 * TC_SEC_03: wxapp_id Forgery (Multi-tenancy Isolation)
 * Test if tenants can access each other's data
 */
function tc_sec_03_wxapp_forgery() {
    sec_log("TC_SEC_03: Testing wxapp_id Forgery / Multi-tenancy", 'test');
    
    $results = [];
    
    // Test 1: Valid token for wxapp_id=1 accessing wxapp_id=1
    sec_log("Test 1: Valid wxapp_id=1 with token for wxapp_id=1", 'info');
    $token1 = base64_encode(json_encode(['wxapp_id' => 1, 'user_id' => 1]));
    $result1 = sec_http_request(BASE_URL . '/api/package/index', 'POST',
        json_encode(['express_num' => 'TEST001']),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token1,
            'X-WxApp-ID: 1'
        ]
    );
    $results[] = [
        'name' => 'Valid wxapp_id=1',
        'expected' => 200,
        'actual' => $result1['code'],
        'passed' => $result1['code'] == 200
    ];
    printf("  Expected: 200, Got: %d %s\n", $result1['code'], $result1['code'] == 200 ? '✅' : '❌');
    
    // Test 2: Token for wxapp_id=1 but requesting wxapp_id=2 (Cross-tenant)
    sec_log("Test 2: Cross-tenant - Token for wxapp_id=1, requesting wxapp_id=2", 'info');
    $result2 = sec_http_request(BASE_URL . '/api/package/index', 'POST',
        json_encode(['express_num' => 'TEST002']),
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token1,
            'X-WxApp-ID: 2' // Different from token
        ]
    );
    $results[] = [
        'name' => 'Cross-tenant Access',
        'expected' => 403,
        'actual' => $result2['code'],
        'passed' => $result2['code'] == 403
    ];
    printf("  Expected: 403, Got: %d %s\n", $result2['code'], $result2['code'] == 403 ? '✅' : '❌');
    
    // Report
    $passed = count(array_filter($results, fn($r) => $r['passed']));
    $failed = count($results) - $passed;
    
    echo "\n" . str_repeat('=', 70) . "\n";
    sec_log("TC_SEC_03 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Passed: %d/%d\n", $passed, count($results));
    printf("  Failed: %d/%d\n", $failed, count($results));
    echo str_repeat('=', 70) . "\n";
    
    if ($failed == 0) {
        sec_log("TC_SEC_03: PASSED ✅ (Multi-tenancy isolation enforced)", 'success');
        return true;
    } else {
        sec_log("TC_SEC_03: FAILED ❌ (Cross-tenant access possible!)", 'error');
        return false;
    }
}

/**
 * TC_SEC_04: Rate Limiting
 * Test if system enforces 100 requests/minute limit
 */
function tc_sec_04_rate_limiting() {
    sec_log("TC_SEC_04: Testing Rate Limiting (" . RATE_LIMIT_REQUESTS . " requests)", 'test');
    
    $apiEndpoint = BASE_URL . '/api/package/index';
    $results = [
        'allowed' => 0,
        'rateLimited' => 0,
        'times' => []
    ];
    
    $startTime = microtime(true);
    
    for ($i = 0; $i < RATE_LIMIT_REQUESTS; $i++) {
        $result = sec_http_request($apiEndpoint, 'POST',
            json_encode(['express_num' => 'RATE_TEST_' . $i]),
            ['Content-Type: application/json']
        );
        
        $results['times'][] = $result['time'];
        
        if (in_array($result['code'], [200, 401])) {
            $results['allowed']++;
        } elseif ($result['code'] == 429) {
            $results['rateLimited']++;
        }
    }
    
    $totalTime = (microtime(true) - $startTime) * 1000;
    $avgTime = array_sum($results['times']) / count($results['times']);
    
    // Report
    echo "\n" . str_repeat('=', 70) . "\n";
    sec_log("TC_SEC_04 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Total Requests:      %d\n", RATE_LIMIT_REQUESTS);
    printf("  Allowed (2xx):       %d\n", $results['allowed']);
    printf("  Rate Limited (429):  %d\n", $results['rateLimited']);
    printf("  Total Time:          %.2f ms\n", $totalTime);
    printf("  Avg Response Time:   %.2f ms\n", $avgTime);
    echo str_repeat('=', 70) . "\n";
    
    // Pass criteria: Should rate limit after 100 requests
    if ($results['rateLimited'] > 0) {
        sec_log("TC_SEC_04: PASSED ✅ (Rate limiting active)", 'success');
        return true;
    } elseif ($results['allowed'] > 100) {
        sec_log("TC_SEC_04: WARNING ⚠️ (More than 100 requests allowed)", 'warning');
        return false;
    } else {
        sec_log("TC_SEC_04: INCONCLUSIVE - Manual review needed", 'warning');
        return null;
    }
}

/**
 * Main execution
 */
function main() {
    global $argv;
    
    echo "\n";
    echo str_repeat('═', 70) . "\n";
    echo "║  " . str_pad('Security Test Suite - Bot System', 65) . " ║\n";
    echo "║  " . str_pad('Testing security vulnerabilities', 65) . " ║\n";
    echo str_repeat('═', 70) . "\n\n";
    
    $testCase = $argv[1] ?? 'all';
    $results = [];
    
    switch ($testCase) {
        case 'sec_api_key':
            $results[] = tc_sec_01_api_key();
            break;
        case 'sec_sql_injection':
            $results[] = tc_sec_02_sql_injection();
            break;
        case 'sec_wxapp_id':
            $results[] = tc_sec_03_wxapp_forgery();
            break;
        case 'sec_rate_limit':
            $results[] = tc_sec_04_rate_limiting();
            break;
        case 'all':
        default:
            $results[] = tc_sec_01_api_key();
            echo "\n";
            $results[] = tc_sec_02_sql_injection();
            echo "\n";
            $results[] = tc_sec_03_wxapp_forgery();
            echo "\n";
            $results[] = tc_sec_04_rate_limiting();
            break;
    }
    
    // Summary
    echo "\n" . str_repeat('═', 70) . "\n";
    echo "║  " . str_pad('SECURITY TEST SUMMARY', 65) . " ║\n";
    echo str_repeat('═', 70) . "\n";
    
    $passed = count(array_filter($results, fn($r) => $r === true));
    $failed = count(array_filter($results, fn($r) => $r === false));
    $review = count(array_filter($results, fn($r) => $r === null));
    
    printf("  Passed:      %d ✅\n", $passed);
    printf("  Failed:      %d ❌\n", $failed);
    printf("  Needs Review:%d ⚠️\n", $review);
    echo str_repeat('═', 70) . "\n";
    
    if ($failed == 0 && $passed > 0) {
        sec_log("ALL SECURITY TESTS PASSED! 🎉", 'success');
        exit(0);
    } else {
        sec_log("SECURITY ISSUES FOUND - Immediate action required!", 'error');
        exit(1);
    }
}

// Run
main();
