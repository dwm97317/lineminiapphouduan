#!/usr/bin/env php
<?php
/**
 * Test Runner - Execute All Tests
 * 
 * Wrapper script to run all test suites with nice formatting
 * 
 * Usage: php run_all_tests.php [--verbose]
 */

$testDir = __DIR__;

echo "\n";
echo str_repeat('═', 80) . "\n";
echo "║  " . str_pad('BOT SYSTEM - COMPLETE TEST SUITE', 76) . " ║\n";
echo "║  " . str_pad('Running Performance, Security & Exception Tests', 76) . " ║\n";
echo str_repeat('═', 80) . "\n\n";

// Check prerequisites
echo "📋 PREREQUISITES CHECK:\n";
echo str_repeat('-', 80) . "\n";

// Check PHP version
$phpVersion = phpversion();
echo "✓ PHP Version: $phpVersion\n";

// Check web server
$ch = curl_init('http://localhost:8000/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode > 0) {
    echo "✓ Web Server: Running (HTTP $httpCode)\n";
} else {
    echo "✗ Web Server: NOT RUNNING - Start with: php -S localhost:8000 -t ../web/\n";
    exit(1);
}

// Check Redis
if (extension_loaded('redis')) {
    try {
        $redis = new Redis();
        if ($redis->connect('127.0.0.1', 6379, 1.0)) {
            echo "✓ Redis: Connected\n";
        } else {
            echo "⚠ Redis: Server not available\n";
        }
    } catch (Exception $e) {
        echo "⚠ Redis: Extension loaded but connection failed\n";
    }
} else {
    echo "✗ Redis: Extension not installed (sudo apt-get install php-redis)\n";
}

echo "\n\n";

// Run Performance Tests
echo str_repeat('═', 80) . "\n";
echo "║  " . str_pad('PERFORMANCE TESTS', 76) . " ║\n";
echo str_repeat('═', 80) . "\n\n";

require_once $testDir . '/test_performance.php';

$perfResults = [
    'perf_concurrent' => tc_perf_01_concurrent(),
    'perf_cache' => tc_perf_02_cache(),
    'perf_db_pool' => tc_perf_03_db_pool(),
];

echo "\n\n";

// Run Security Tests
echo str_repeat('═', 80) . "\n";
echo "║  " . str_pad('SECURITY TESTS', 76) . " ║\n";
echo str_repeat('═', 80) . "\n\n";

require_once $testDir . '/test_security.php';

$secResults = [
    'sec_api_key' => tc_sec_01_api_key(),
    'sec_sql_injection' => tc_sec_02_sql_injection(),
    'sec_wxapp_id' => tc_sec_03_wxapp_forgery(),
    'sec_rate_limit' => tc_sec_04_rate_limiting(),
];

echo "\n\n";

// Run Exception Tests
echo str_repeat('═', 80) . "\n";
echo "║  " . str_pad('EXCEPTION HANDLING TESTS', 76) . " ║\n";
echo str_repeat('═', 80) . "\n\n";

require_once $testDir . '/test_exception.php';

$excResults = [
    'exc_meta_timeout' => tc_exc_01_meta_timeout(),
    'exc_carrier_fallback' => tc_exc_02_carrier_fallback(),
    'exc_db_reconnect' => tc_exc_03_db_reconnection(),
    'exc_redis_reconnect' => tc_exc_04_redis_reconnection(),
];

echo "\n\n";

// Final Summary
echo str_repeat('═', 80) . "\n";
echo "║  " . str_pad('FINAL TEST SUMMARY', 76) . " ║\n";
echo str_repeat('═', 80) . "\n\n";

$allResults = array_merge($perfResults, $secResults, $excResults);

$passed = count(array_filter($allResults, fn($r) => $r === true));
$failed = count(array_filter($allResults, fn($r) => $r === false));
$skipped = count(array_filter($allResults, fn($r) => $r === null));

echo "Total Tests: " . count($allResults) . "\n";
echo "  ✅ Passed:  $passed\n";
echo "  ❌ Failed:  $failed\n";
echo "  ⚠️  Skipped: $skipped\n";
echo "\n";

if ($failed == 0 && $passed > 0) {
    echo "🎉 ALL TESTS PASSED! System is ready for production.\n";
    exit(0);
} else {
    echo "⚠️  SOME TESTS FAILED. Please review and fix issues before deployment.\n";
    echo "\nSee TEST_README.md for detailed instructions.\n";
    exit(1);
}
