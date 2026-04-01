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

// Run Functional E2E Tests
echo str_repeat('═', 80) . "\n";
echo "║  " . str_pad('FUNCTIONAL E2E TESTS', 76) . " ║\n";
echo str_repeat('═', 80) . "\n\n";

require_once $testDir . '/test_e2e_functional.php';

$funcResults = [
    'e2e_account_linking' => tc_e2e_01_account_linking(),
    'e2e_order_session' => tc_e2e_02_order_session_creation(),
    'e2e_info_supplement' => tc_e2e_03_information_supplement(),
    'e2e_tracking_link' => tc_e2e_04_tracking_code_linking(),
    'e2e_activate_order' => tc_e2e_05_activate_pending_orders(),
    'e2e_multiple_orders' => tc_e2e_06_multiple_orders(),
];

echo "\n\n";

// Run Anti-Confusion & Isolation Tests
echo str_repeat('═', 80) . "\n";
echo "║  " . str_pad('ANTI-CONFUSION & ISOLATION TESTS', 76) . " ║\n";
echo str_repeat('═', 80) . "\n\n";

require_once $testDir . '/test_anti_confusion_isolation.php';

$aciResults = [
    'ac_timeout' => tc_ac_01_timeout_auto_session(),
    'ac_same_seller' => tc_ac_02_same_seller_prevention(),
    'ac_diff_seller' => tc_ac_03_different_seller_confirmation(),
    'ac_new_order' => tc_ac_04_customer_new_order_selection(),
    'iso_tenant' => tc_iso_01_multi_tenant_isolation(),
    'iso_cross_access' => tc_iso_02_cross_tenant_rejection(),
];

echo "\n\n";

// Run Exception Scenarios Tests
echo str_repeat('═', 80) . "\n";
echo "║  " . str_pad('EXCEPTION SCENARIOS TESTS', 76) . " ║\n";
echo str_repeat('═', 80) . "\n\n";

require_once $testDir . '/test_exception_scenarios.php';

$excResults = [
    'exc_network' => tc_exc_net_01_timeout_retry(),
    'exc_duplicate' => tc_exc_dup_01_duplicate_tracking(),
    'exc_access' => tc_exc_acc_01_unlinked_access_limit(),
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
