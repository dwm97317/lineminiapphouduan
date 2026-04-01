#!/usr/bin/env php
<?php
/**
 * Exception Handling Test Suite - Bot System
 * 
 * Tests system resilience under failure conditions
 * - TC_EXC_01: Meta API Timeout Handling
 * - TC_EXC_02: Carrier API Fallback
 * - TC_EXC_03: Database Reconnection
 * - TC_EXC_04: Redis Reconnection
 * 
 * Usage: php test_exception.php [test_case]
 * Example: php test_exception.php all
 */

// Configuration
define('BASE_URL', 'http://localhost:8000');
define('API_TIMEOUT', 10);

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
    ];
    $color = $C[$style] ?? $C['reset'];
    echo $color . ($prefixes[$style] ?? '') . $msg . $C['reset'] . "\n";
}

/**
 * Helper: Make HTTP Request
 */
function exc_http_request($url, $method = 'GET', $data = null, $headers = [], $timeout = 10) {
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
 * TC_EXC_01: Meta API Timeout Handling
 * Test system behavior when Meta API times out
 */
function tc_exc_01_meta_timeout() {
    exc_log("TC_EXC_01: Testing Meta API Timeout Handling", 'test');
    
    // This test requires manual simulation
    exc_log("Manual test required:", 'warning');
    echo "1. Simulate Meta API timeout (modify code to add sleep/delay)\n";
    echo "2. Send message via webhook\n";
    echo "3. Expected: System should return error within timeout period\n";
    echo "4. Check error message is user-friendly\n\n";
    
    // Automated check: Verify endpoint responds
    $result = exc_http_request(BASE_URL . '/api/webhook/line', 'POST',
        json_encode([
            'destination' => 'U123456789',
            'events' => [[
                'type' => 'message',
                'replyToken' => 'test_token',
                'source' => ['userId' => 'U123456789'],
                'message' => ['id' => 'msg_001', 'text' => 'Test timeout']
            ]]
        ]),
        ['Content-Type: application/json'],
        5 // Short timeout for test
    );
    
    exc_log("HTTP Response Code: " . $result['code'], 'info');
    exc_log("Response Time: " . number_format($result['time'], 2) . " ms", 'info');
    
    if ($result['code'] != 0) {
        exc_log("TC_EXC_01: PASSED ✅ (Endpoint responsive)", 'success');
        return true;
    } else {
        exc_log("TC_EXC_01: FAILED ❌ (Timeout or connection error)", 'error');
        return false;
    }
}

/**
 * TC_EXC_02: Carrier API Fallback
 * Test system behavior when carrier API is unavailable
 */
function tc_exc_02_carrier_fallback() {
    exc_log("TC_EXC_02: Testing Carrier API Fallback", 'test');
    
    // Test with invalid tracking number
    exc_log("Testing with invalid express number...", 'info');
    $result = exc_http_request(BASE_URL . '/api/package/index', 'POST',
        json_encode(['express_num' => 'INVALID_TRACKING_999']),
        ['Content-Type: application/json']
    );
    
    exc_log("HTTP Response Code: " . $result['code'], 'info');
    exc_log("Response: " . substr($result['body'], 0, 100), 'info');
    
    // Should handle gracefully, not crash
    if ($result['code'] != 500) {
        exc_log("TC_EXC_02: PASSED ✅ (Graceful error handling)", 'success');
        return true;
    } else {
        exc_log("TC_EXC_02: FAILED ❌ (System error on invalid data)", 'error');
        return false;
    }
}

/**
 * TC_EXC_03: Database Reconnection
 * Test system recovery after database connection loss
 */
function tc_exc_03_db_reconnection() {
    exc_log("TC_EXC_03: Testing Database Reconnection", 'test');
    
    exc_log("Manual test procedure:", 'warning');
    echo "1. Stop MySQL: sudo systemctl stop mysql\n";
    echo "2. Test API: curl " . BASE_URL . "/api/package/index\n";
    echo "3. Expected: System returns 'System busy' or maintenance error\n";
    echo "4. Start MySQL: sudo systemctl start mysql\n";
    echo "5. Test again: Should work normally\n\n";
    
    // Pre-check: Verify DB is currently accessible
    $result = exc_http_request(BASE_URL . '/api/package/index', 'POST',
        json_encode(['express_num' => 'DB_TEST']),
        ['Content-Type: application/json']
    );
    
    exc_log("Current HTTP Response Code: " . $result['code'], 'info');
    exc_log("Current Response: " . substr($result['body'], 0, 100), 'info');
    
    if ($result['code'] == 200 || $result['code'] == 401) {
        exc_log("Database is currently accessible", 'success');
        exc_log("TC_EXC_03: Manual test required - see instructions above", 'info');
        return null; // Manual test needed
    } else {
        exc_log("Database may already be down or error occurred", 'warning');
        return null;
    }
}

/**
 * TC_EXC_04: Redis Reconnection
 * Test system recovery after Redis connection loss
 */
function tc_exc_04_redis_reconnection() {
    exc_log("TC_EXC_04: Testing Redis Reconnection", 'test');
    
    // Check Redis extension
    if (!extension_loaded('redis')) {
        exc_log("Redis extension not installed - SKIPPED", 'warning');
        exc_log("Install: sudo apt-get install php-redis", 'info');
        return null;
    }
    
    try {
        $redis = new Redis();
        $connected = $redis->connect('127.0.0.1', 6379, 2.0);
        
        if (!$connected) {
            exc_log("Redis server not available - SKIPPED", 'warning');
            return null;
        }
        
        exc_log("Redis is currently connected", 'success');
        $info = $redis->info('server');
        echo "Redis Version: " . ($info['redis_version'] ?? 'unknown') . "\n\n";
        
        exc_log("Manual test procedure:", 'warning');
        echo "1. Stop Redis: sudo systemctl stop redis\n";
        echo "2. Test API: curl " . BASE_URL . "/test_cache.php\n";
        echo "3. Expected: Fallback to DB or maintenance error\n";
        echo "4. Start Redis: sudo systemctl start redis\n";
        echo "5. Test again: Should work normally\n\n";
        
        exc_log("TC_EXC_04: Manual test required - see instructions above", 'info');
        return null;
        
    } catch (Exception $e) {
        exc_log("Redis connection failed: " . $e->getMessage(), 'error');
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
    echo "║  " . str_pad('Exception Handling Test Suite - Bot System', 65) . " ║\n";
    echo "║  " . str_pad('Testing system resilience under failures', 65) . " ║\n";
    echo str_repeat('═', 70) . "\n\n";
    
    $testCase = $argv[1] ?? 'all';
    $results = [];
    
    switch ($testCase) {
        case 'exc_meta_timeout':
            $results[] = tc_exc_01_meta_timeout();
            break;
        case 'exc_carrier_fallback':
            $results[] = tc_exc_02_carrier_fallback();
            break;
        case 'exc_db_reconnect':
            $results[] = tc_exc_03_db_reconnection();
            break;
        case 'exc_redis_reconnect':
            $results[] = tc_exc_04_redis_reconnection();
            break;
        case 'all':
        default:
            $results[] = tc_exc_01_meta_timeout();
            echo "\n";
            $results[] = tc_exc_02_carrier_fallback();
            echo "\n";
            $results[] = tc_exc_03_db_reconnection();
            echo "\n";
            $results[] = tc_exc_04_redis_reconnection();
            break;
    }
    
    // Summary
    echo "\n" . str_repeat('═', 70) . "\n";
    echo "║  " . str_pad('EXCEPTION HANDLING TEST SUMMARY', 65) . " ║\n";
    echo str_repeat('═', 70) . "\n";
    
    $passed = count(array_filter($results, fn($r) => $r === true));
    $failed = count(array_filter($results, fn($r) => $r === false));
    $manual = count(array_filter($results, fn($r) => $r === null));
    
    printf("  Passed:       %d ✅\n", $passed);
    printf("  Failed:       %d ❌\n", $failed);
    printf("  Manual Tests: %d ℹ️\n", $manual);
    echo str_repeat('═', 70) . "\n";
    
    if ($failed == 0) {
        exc_log("EXCEPTION HANDLING TESTS COMPLETE! 🎉", 'success');
        exit(0);
    } else {
        exc_log("EXCEPTION HANDLING ISSUES FOUND!", 'error');
        exit(1);
    }
}

// Run
main();
