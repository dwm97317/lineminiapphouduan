#!/usr/bin/env php
<?php
/**
 * Performance Test Suite - Bot System
 * 
 * Tests system performance under high load
 * - TC_PERF_01: 100 Concurrent Users Message Processing
 * - TC_PERF_02: Redis Cache Hit Ratio (if Redis available)
 * - TC_PERF_03: Database Connection Pool Stress Test
 * - TC_PERF_04: Webhook Message Queue Processing
 * 
 * Usage: php test_performance.php [test_case]
 * Example: php test_performance.php all
 */

// Configuration
define('BASE_URL', 'http://localhost:8000');
define('API_TIMEOUT', 30);
define('CONCURRENT_USERS', 100);
define('MAX_RESPONSE_TIME_MS', 2000); // 2 seconds
define('CACHE_REQUESTS', 1000);
define('DB_CONNECTIONS', 200);

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

function test_log($msg, $style = 'info') {
    global $C;
    $prefixes = [
        'info'     => 'ℹ️ ',
        'success'  => '✅ ',
        'error'    => '❌ ',
        'warning'  => '⚠️  ',
        'test'     => '🧪 ',
    ];
    $color = $C[$style] ?? $C['reset'];
    echo $color . ($prefixes[$style] ?? '') . $msg . $C['reset'] . "\n";
}

/**
 * Helper: Make HTTP Request
 */
function httpRequest($url, $method = 'GET', $data = null, $headers = []) {
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
        'time' => $info['total_time'] * 1000, // ms
        'error' => $error
    ];
}

/**
 * TC_PERF_01: Concurrent Message Processing
 * Simulate 100 users sending messages simultaneously
 */
function tc_perf_01_concurrent() {
test_log("TC_PERF_01: Testing " . CONCURRENT_USERS . " Concurrent Users", 'test');
    
    $multiHandle = curl_multi_init();
    $handles = [];
    $startTime = microtime(true);
    
    // Create concurrent requests
    for ($i = 0; $i < CONCURRENT_USERS; $i++) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, BASE_URL . '/api/package/index');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'express_num' => 'TEST' . str_pad($i, 5, '0', STR_PAD_LEFT)
        ]));
        curl_setopt($ch, CURLOPT_TIMEOUT, API_TIMEOUT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
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
    $responseTimes = [];
    
    foreach ($handles as $ch) {
        $info = curl_getinfo($ch);
        $responseTime = $info['total_time'] * 1000;
        $responseTimes[] = $responseTime;
        
        if ($info['http_code'] == 200 || $info['http_code'] == 401) {
            $success++;
        } else {
            $errors++;
        }
        
        curl_multi_remove_handle($multiHandle, $ch);
        curl_close($ch);
    }
    
    curl_multi_close($multiHandle);
    
    $totalTime = (microtime(true) - $startTime) * 1000;
    $avgResponseTime = array_sum($responseTimes) / count($responseTimes);
    $maxResponseTime = max($responseTimes);
    $minResponseTime = min($responseTimes);
    $successRate = ($success / CONCURRENT_USERS) * 100;
    
    // Report
    echo "\n" . str_repeat('=', 70) . "\n";
test_log("TC_PERF_01 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Total Requests:      %d\n", CONCURRENT_USERS);
    printf("  Successful:          %d (%.1f%%)\n", $success, $successRate);
    printf("  Errors:              %d (%.1f%%)\n", $errors, 100 - $successRate);
    printf("  Total Time:          %.2f ms\n", $totalTime);
    printf("  Avg Response Time:   %.2f ms\n", $avgResponseTime);
    printf("  Max Response Time:   %.2f ms\n", $maxResponseTime);
    printf("  Min Response Time:   %.2f ms\n", $minResponseTime);
    echo str_repeat('=', 70) . "\n";
    
    // Pass criteria: 100% success rate AND avg response time < MAX_RESPONSE_TIME_MS
    if ($successRate == 100 && $avgResponseTime < MAX_RESPONSE_TIME_MS) {
test_log("TC_PERF_01: PASSED ✅", 'success');
        return true;
    } else {
test_log("TC_PERF_01: FAILED ❌", 'error');
        if ($successRate < 100) log("  → Error rate: " . (100 - $successRate) . "%", 'error');
        if ($avgResponseTime >= MAX_RESPONSE_TIME_MS) log("  → Response time >= {$maxResponseTime}ms (limit: {$maxResponseTime}ms)", 'error');
        return false;
    }
}

/**
 * TC_PERF_02: Redis Cache Hit Ratio
 * Test cache efficiency with repeated requests
 */
function tc_perf_02_cache() {
test_log("TC_PERF_02: Testing Redis Cache Hit Ratio", 'test');
    
    // Check Redis extension
    if (!extension_loaded('redis')) {
test_log("Redis extension not installed - SKIPPED", 'warning');
test_log("Install: sudo apt-get install php-redis", 'info');
        return null;
    }
    
    try {
        $redis = new Redis();
        $connected = $redis->connect('127.0.0.1', 6379, 2.0);
        
        if (!$connected) {
test_log("Redis server not available - SKIPPED", 'warning');
            return null;
        }
        
        // Clear stats
        $redis->config('RESETSTAT');
        
        // Simulate cache requests
        $cacheKeys = [];
        for ($i = 0; $i < CACHE_REQUESTS; $i++) {
            $key = 'test_cache_' . ($i % 100); // 100 unique keys, repeated access
            $cacheKeys[] = $key;
            
            if (!$redis->exists($key)) {
                $redis->setex($key, 3600, 'test_data_' . $i);
            }
            $redis->get($key);
        }
        
        // Get stats
        $stats = $redis->info('stats');
        $hits = $stats['keyspace_hits'] ?? 0;
        $misses = $stats['keyspace_misses'] ?? 0;
        $total = $hits + $misses;
        $hitRatio = $total > 0 ? ($hits / $total) * 100 : 0;
        
        // Report
        echo "\n" . str_repeat('=', 70) . "\n";
test_log("TC_PERF_02 Results:", 'bold');
        echo str_repeat('=', 70) . "\n";
        printf("  Total Requests:      %d\n", CACHE_REQUESTS);
        printf("  Cache Hits:          %d\n", $hits);
        printf("  Cache Misses:        %d\n", $misses);
        printf("  Hit Ratio:           %.2f%%\n", $hitRatio);
        echo str_repeat('=', 70) . "\n";
        
        // Cleanup
        for ($i = 0; $i < 100; $i++) {
            $redis->del('test_cache_' . $i);
        }
        
        // Pass criteria: hit ratio >= 80%
        if ($hitRatio >= 80) {
test_log("TC_PERF_02: PASSED ✅ (Hit ratio >= 80%)", 'success');
            return true;
        } else {
test_log("TC_PERF_02: FAILED ❌ (Hit ratio < 80%)", 'error');
            return false;
        }
        
    } catch (Exception $e) {
test_log("Redis error: " . $e->getMessage(), 'error');
        return null;
    }
}

/**
 * TC_PERF_03: Database Connection Pool
 * Test database connection handling under stress
 */
function tc_perf_03_db_pool() {
test_log("TC_PERF_03: Testing Database Connection Pool (" . DB_CONNECTIONS . " connections)", 'test');
    
    $success = 0;
    $tooManyConnections = 0;
    $otherErrors = 0;
    $responseTimes = [];
    
    $startTime = microtime(true);
    
    for ($i = 0; $i < DB_CONNECTIONS; $i++) {
        $start = microtime(true);
        $result = httpRequest(BASE_URL . '/api/package/index', 'POST', 
            json_encode(['express_num' => 'DB_TEST_' . $i]),
            ['Content-Type: application/json']
        );
        $elapsed = (microtime(true) - $start) * 1000;
        $responseTimes[] = $elapsed;
        
        if ($result['code'] == 200 || $result['code'] == 401) {
            $success++;
        } elseif (strpos($result['body'], 'Too many connections') !== false) {
            $tooManyConnections++;
        } else {
            $otherErrors++;
        }
    }
    
    $totalTime = (microtime(true) - $startTime) * 1000;
    $avgResponseTime = array_sum($responseTimes) / count($responseTimes);
    
    // Report
    echo "\n" . str_repeat('=', 70) . "\n";
test_log("TC_PERF_03 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Total Requests:         %d\n", DB_CONNECTIONS);
    printf("  Successful:             %d\n", $success);
    printf("  Too Many Connections:   %d\n", $tooManyConnections);
    printf("  Other Errors:           %d\n", $otherErrors);
    printf("  Total Time:             %.2f ms\n", $totalTime);
    printf("  Avg Response Time:      %.2f ms\n", $avgResponseTime);
    echo str_repeat('=', 70) . "\n";
    
    // Pass criteria: No "too many connections" errors
    if ($tooManyConnections == 0) {
test_log("TC_PERF_03: PASSED ✅ (No connection pool exhaustion)", 'success');
        return true;
    } else {
test_log("TC_PERF_03: FAILED ❌ (Connection pool exhausted)", 'error');
        return false;
    }
}

/**
 * TC_PERF_04: Webhook Message Queue
 * Test webhook processing under burst traffic
 */
function tc_perf_04_webhook() {
test_log("TC_PERF_04: Testing Webhook Message Queue Processing", 'test');
    
    $webhookUrl = BASE_URL . '/api/webhook/line';
    $messageCount = 50;
    $results = [];
    
    $startTime = microtime(true);
    
    // Simulate burst of webhook messages
    for ($i = 0; $i < $messageCount; $i++) {
        $payload = [
            'destination' => 'U' . str_pad($i, 10, '0', STR_PAD_LEFT),
            'events' => [[
                'type' => 'message',
                'replyToken' => 'test_token_' . $i,
                'source' => ['userId' => 'U' . $i],
                'message' => [
                    'id' => 'msg_' . $i,
                    'text' => 'Test message ' . $i
                ]
            ]]
        ];
        
        $result = httpRequest($webhookUrl, 'POST', json_encode($payload), [
            'Content-Type: application/json',
            'X-Line-Signature: test_signature_' . $i
        ]);
        
        $results[] = $result;
    }
    
    $totalTime = (microtime(true) - $startTime) * 1000;
    
    $processed = count(array_filter($results, fn($r) => $r['code'] == 200));
    $failed = $messageCount - $processed;
    $avgTime = array_sum(array_column($results, 'time')) / count($results);
    
    // Report
    echo "\n" . str_repeat('=', 70) . "\n";
test_log("TC_PERF_04 Results:", 'bold');
    echo str_repeat('=', 70) . "\n";
    printf("  Total Messages:      %d\n", $messageCount);
    printf("  Processed:           %d (%.1f%%)\n", $processed, ($processed/$messageCount)*100);
    printf("  Failed:              %d (%.1f%%)\n", $failed, ($failed/$messageCount)*100);
    printf("  Total Time:          %.2f ms\n", $totalTime);
    printf("  Avg Processing Time: %.2f ms\n", $avgTime);
    printf("  Messages/sec:        %.2f\n", ($messageCount / ($totalTime/1000)));
    echo str_repeat('=', 70) . "\n";
    
    // Pass criteria: 100% processed
    if ($processed == $messageCount) {
test_log("TC_PERF_04: PASSED ✅", 'success');
        return true;
    } else {
test_log("TC_PERF_04: FAILED ❌", 'error');
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
    echo "║  " . str_pad('Performance Test Suite - Bot System', 65) . " ║\n";
    echo "║  " . str_pad('Testing system stability under high load', 65) . " ║\n";
    echo str_repeat('═', 70) . "\n\n";
    
    $testCase = $argv[1] ?? 'all';
    $results = [];
    
    switch ($testCase) {
        case 'perf_concurrent':
            $results[] = tc_perf_01_concurrent();
            break;
        case 'perf_cache':
            $results[] = tc_perf_02_cache();
            break;
        case 'perf_db_pool':
            $results[] = tc_perf_03_db_pool();
            break;
        case 'perf_webhook':
            $results[] = tc_perf_04_webhook();
            break;
        case 'all':
        default:
            $results[] = tc_perf_01_concurrent();
            echo "\n";
            $results[] = tc_perf_02_cache();
            echo "\n";
            $results[] = tc_perf_03_db_pool();
            echo "\n";
            $results[] = tc_perf_04_webhook();
            break;
    }
    
    // Summary
    echo "\n" . str_repeat('═', 70) . "\n";
    echo "║  " . str_pad('PERFORMANCE TEST SUMMARY', 65) . " ║\n";
    echo str_repeat('═', 70) . "\n";
    
    $passed = count(array_filter($results, fn($r) => $r === true));
    $failed = count(array_filter($results, fn($r) => $r === false));
    $skipped = count(array_filter($results, fn($r) => $r === null));
    
    printf("  Passed:   %d ✅\n", $passed);
    printf("  Failed:   %d ❌\n", $failed);
    printf("  Skipped:  %d ⚠️\n", $skipped);
    echo str_repeat('═', 70) . "\n";
    
    if ($failed == 0 && $passed > 0) {
test_log("ALL PERFORMANCE TESTS PASSED! 🎉", 'success');
        exit(0);
    } else {
test_log("SOME TESTS FAILED - Review results above", 'error');
        exit(1);
    }
}

// Run
main();
