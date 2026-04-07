#!/usr/bin/env php
<?php
/**
 * Facebook Webhook Testing Suite
 * 
 * Tests for Facebook Messenger webhook integration
 * Verifies signature validation, message parsing, and response handling
 * 
 * Usage: php test_facebook_webhook.php [test_case]
 * Example: php test_facebook_webhook.php all
 */

// Configuration
define('BASE_URL', 'http://localhost:8000');
define('API_TIMEOUT', 30);
define('FACEBOOK_VERIFY_TOKEN', 'your_random_verify_token');
define('FACEBOOK_APP_SECRET', 'your_app_secret_for_testing');

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

function fb_log($msg, $style = 'info') {
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
function fb_http_request($url, $method = 'GET', $data = null, $headers = [], $timeout = 30) {
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
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
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
 * Helper: Generate Facebook webhook signature
 */
function generate_facebook_signature($body, $app_secret) {
    $signature = hash_hmac('sha1', $body, $app_secret);
    return 'sha1=' . $signature;
}

/**
 * TC_FB_01: Webhook Verification
 */
function tc_fb_01_webhook_verification() {
    fb_log("TC_FB_01: Webhook Verification", 'test');
    
    $challenge = 'test_challenge_123456789';
    $url = BASE_URL . '/api/facebook/webhook?' . http_build_query([
        'hub_mode' => 'subscribe',
        'hub_verify_token' => FACEBOOK_VERIFY_TOKEN,
        'hub_challenge' => $challenge
    ]);
    
    fb_log("Testing webhook verification endpoint", 'step');
    $result = fb_http_request($url, 'GET');
    
    if ($result['code'] == 200 && $result['body'] === $challenge) {
        fb_log("✅ Webhook verification successful", 'success');
        return true;
    } else {
        fb_log("❌ Webhook verification failed", 'error');
        fb_log("Response code: " . $result['code'], 'error');
        fb_log("Response body: " . $result['body'], 'error');
        return false;
    }
}

/**
 * TC_FB_02: Webhook with Invalid Token
 */
function tc_fb_02_invalid_token() {
    fb_log("TC_FB_02: Webhook Verification with Invalid Token", 'test');
    
    $url = BASE_URL . '/api/facebook/webhook?' . http_build_query([
        'hub_mode' => 'subscribe',
        'hub_verify_token' => 'invalid_token',
        'hub_challenge' => 'test_challenge'
    ]);
    
    fb_log("Testing with invalid token", 'step');
    $result = fb_http_request($url, 'GET');
    
    if ($result['code'] == 403) {
        fb_log("✅ Correctly rejected invalid token", 'success');
        return true;
    } else {
        fb_log("❌ Should reject invalid token with 403", 'error');
        fb_log("Got response code: " . $result['code'], 'error');
        return false;
    }
}

/**
 * TC_FB_03: Message Reception
 */
function tc_fb_03_message_reception() {
    fb_log("TC_FB_03: Message Reception", 'test');
    
    $body = json_encode([
        'object' => 'page',
        'entry' => [
            [
                'id' => 'page_123',
                'time' => time(),
                'messaging' => [
                    [
                        'sender' => ['id' => 'user_123'],
                        'recipient' => ['id' => 'page_123'],
                        'timestamp' => time(),
                        'message' => [
                            'mid' => 'msg_123',
                            'text' => 'Test message'
                        ]
                    ]
                ]
            ]
        ]
    ]);
    
    $headers = [
        'Content-Type: application/json',
        'X-Hub-Signature: ' . generate_facebook_signature($body, FACEBOOK_APP_SECRET)
    ];
    
    fb_log("Sending test message to webhook", 'step');
    $result = fb_http_request(BASE_URL . '/api/facebook/webhook', 'POST', $body, $headers);
    
    if ($result['code'] == 200) {
        fb_log("✅ Message received successfully", 'success');
        return true;
    } else {
        fb_log("❌ Failed to receive message", 'error');
        fb_log("Response code: " . $result['code'], 'error');
        fb_log("Response body: " . $result['body'], 'error');
        return false;
    }
}

/**
 * TC_FB_04: Invalid Signature
 */
function tc_fb_04_invalid_signature() {
    fb_log("TC_FB_04: Webhook with Invalid Signature", 'test');
    
    $body = json_encode([
        'object' => 'page',
        'entry' => [[
            'messaging' => [
                ['sender' => ['id' => 'user_123'], 'message' => ['text' => 'Test']]
            ]
        ]]
    ]);
    
    $headers = [
        'Content-Type: application/json',
        'X-Hub-Signature: sha1=invalid_signature_xyz'
    ];
    
    fb_log("Sending message with invalid signature", 'step');
    $result = fb_http_request(BASE_URL . '/api/facebook/webhook', 'POST', $body, $headers);
    
    if ($result['code'] == 403) {
        fb_log("✅ Correctly rejected invalid signature", 'success');
        return true;
    } else {
        fb_log("❌ Should reject invalid signature with 403", 'error');
        fb_log("Got response code: " . $result['code'], 'error');
        return false;
    }
}

/**
 * TC_FB_05: Multiple Messages
 */
function tc_fb_05_multiple_messages() {
    fb_log("TC_FB_05: Multiple Messages in Single Webhook", 'test');
    
    $body = json_encode([
        'object' => 'page',
        'entry' => [
            [
                'messaging' => [
                    [
                        'sender' => ['id' => 'user_123'],
                        'message' => ['text' => 'Message 1']
                    ],
                    [
                        'sender' => ['id' => 'user_456'],
                        'message' => ['text' => 'Message 2']
                    ],
                    [
                        'sender' => ['id' => 'user_123'],
                        'message' => ['text' => 'Message 3']
                    ]
                ]
            ]
        ]
    ]);
    
    $headers = [
        'Content-Type: application/json',
        'X-Hub-Signature: ' . generate_facebook_signature($body, FACEBOOK_APP_SECRET)
    ];
    
    fb_log("Sending 3 messages from 2 different users", 'step');
    $result = fb_http_request(BASE_URL . '/api/facebook/webhook', 'POST', $body, $headers);
    
    if ($result['code'] == 200) {
        fb_log("✅ Multiple messages processed successfully", 'success');
        return true;
    } else {
        fb_log("❌ Failed to process multiple messages", 'error');
        return false;
    }
}

/**
 * TC_FB_06: Postback Event
 */
function tc_fb_06_postback_event() {
    fb_log("TC_FB_06: Postback Event (Button Click)", 'test');
    
    $body = json_encode([
        'object' => 'page',
        'entry' => [
            [
                'messaging' => [
                    [
                        'sender' => ['id' => 'user_123'],
                        'postback' => [
                            'title' => 'Create Order',
                            'payload' => 'CREATE_ORDER_ACTION'
                        ]
                    ]
                ]
            ]
        ]
    ]);
    
    $headers = [
        'Content-Type: application/json',
        'X-Hub-Signature: ' . generate_facebook_signature($body, FACEBOOK_APP_SECRET)
    ];
    
    fb_log("Sending postback event", 'step');
    $result = fb_http_request(BASE_URL . '/api/facebook/webhook', 'POST', $body, $headers);
    
    if ($result['code'] == 200) {
        fb_log("✅ Postback event processed successfully", 'success');
        return true;
    } else {
        fb_log("❌ Failed to process postback event", 'error');
        return false;
    }
}

/**
 * TC_FB_07: Quick Reply Event
 */
function tc_fb_07_quick_reply_event() {
    fb_log("TC_FB_07: Quick Reply Event", 'test');
    
    $body = json_encode([
        'object' => 'page',
        'entry' => [
            [
                'messaging' => [
                    [
                        'sender' => ['id' => 'user_123'],
                        'message' => [
                            'text' => 'Yes',
                            'quick_reply' => [
                                'payload' => 'YES_OPTION'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]);
    
    $headers = [
        'Content-Type: application/json',
        'X-Hub-Signature: ' . generate_facebook_signature($body, FACEBOOK_APP_SECRET)
    ];
    
    fb_log("Sending quick reply event", 'step');
    $result = fb_http_request(BASE_URL . '/api/facebook/webhook', 'POST', $body, $headers);
    
    if ($result['code'] == 200) {
        fb_log("✅ Quick reply processed successfully", 'success');
        return true;
    } else {
        fb_log("❌ Failed to process quick reply", 'error');
        return false;
    }
}

/**
 * TC_FB_08: Response Time Check
 */
function tc_fb_08_response_time() {
    fb_log("TC_FB_08: Response Time Performance", 'test');
    
    $body = json_encode([
        'object' => 'page',
        'entry' => [
            [
                'messaging' => [
                    ['sender' => ['id' => 'user_123'], 'message' => ['text' => 'Test']]
                ]
            ]
        ]
    ]);
    
    $headers = [
        'Content-Type: application/json',
        'X-Hub-Signature: ' . generate_facebook_signature($body, FACEBOOK_APP_SECRET)
    ];
    
    fb_log("Measuring response time...", 'step');
    $result = fb_http_request(BASE_URL . '/api/facebook/webhook', 'POST', $body, $headers);
    
    $response_time = $result['time'];
    fb_log("Response time: " . round($response_time, 2) . "ms", 'info');
    
    // Facebook expects < 2000ms (2 seconds)
    if ($response_time < 2000) {
        fb_log("✅ Response time within acceptable range", 'success');
        return true;
    } else {
        fb_log("⚠️  Response time exceeds 2 seconds", 'warning');
        return false;
    }
}

/**
 * Run all tests
 */
function run_all_tests() {
    fb_log("=" . str_repeat("=", 60), 'cyan');
    fb_log("Facebook Webhook Integration Test Suite", 'cyan');
    fb_log("=" . str_repeat("=", 60), 'cyan');
    fb_log("Target: " . BASE_URL, 'info');
    fb_log("", 'info');
    
    $tests = [
        'tc_fb_01_webhook_verification' => 'Webhook Verification',
        'tc_fb_02_invalid_token' => 'Invalid Token Rejection',
        'tc_fb_03_message_reception' => 'Message Reception',
        'tc_fb_04_invalid_signature' => 'Invalid Signature Rejection',
        'tc_fb_05_multiple_messages' => 'Multiple Messages',
        'tc_fb_06_postback_event' => 'Postback Event',
        'tc_fb_07_quick_reply_event' => 'Quick Reply Event',
        'tc_fb_08_response_time' => 'Response Time',
    ];
    
    $results = [];
    
    foreach ($tests as $func => $name) {
        if (function_exists($func)) {
            $results[$name] = $func();
            fb_log("", 'info');
        }
    }
    
    // Summary
    fb_log("=" . str_repeat("=", 60), 'cyan');
    fb_log("Test Summary", 'cyan');
    fb_log("=" . str_repeat("=", 60), 'cyan');
    
    $passed = array_sum($results);
    $total = count($results);
    
    foreach ($results as $name => $passed_test) {
        $status = $passed_test ? '✅ PASS' : '❌ FAIL';
        echo $status . " - " . $name . "\n";
    }
    
    fb_log("", 'info');
    fb_log("Total: $passed/$total tests passed", $passed === $total ? 'success' : 'warning');
    fb_log("", 'info');
}

// Main execution
if (php_sapi_name() !== 'cli') {
    die('This script must be run from command line');
}

$arg = $argv[1] ?? 'all';

switch ($arg) {
    case 'all':
        run_all_tests();
        break;
    case 'verify':
        tc_fb_01_webhook_verification();
        break;
    case 'message':
        tc_fb_03_message_reception();
        break;
    case 'performance':
        tc_fb_08_response_time();
        break;
    default:
        echo "Usage: php test_facebook_webhook.php [all|verify|message|performance]\n";
        break;
}
