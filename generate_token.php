<?php
/**
 * Generate a valid token for development testing
 * This script creates a token and stores it in the cache system
 */

// Define application path
define('APP_PATH', __DIR__ . '/source/application/');

// Load ThinkPHP framework
require __DIR__ . '/source/thinkphp/start.php';

// Import necessary classes
use think\Cache;
use app\api\model\User as UserModel;

echo "🔧 Token Generator for Development\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Get user ID from command line or use default
$userId = isset($argv[1]) ? (int)$argv[1] : 31831;
$wxappId = isset($argv[2]) ? (int)$argv[2] : 10001;

echo "📋 Parameters:\n";
echo "  User ID: {$userId}\n";
echo "  Wxapp ID: {$wxappId}\n\n";

try {
    // Get user info
    $user = UserModel::useGlobalScope(false)
        ->where(['user_id' => $userId, 'wxapp_id' => $wxappId, 'is_delete' => 0])
        ->find();
    
    if (!$user) {
        echo "❌ Error: User not found (user_id={$userId}, wxapp_id={$wxappId})\n";
        exit(1);
    }
    
    echo "✅ User found:\n";
    echo "  Nickname: {$user['nickName']}\n";
    echo "  Mobile: {$user['mobile']}\n";
    echo "  Open ID: {$user['open_id']}\n\n";
    
    // Generate token (same logic as Login service)
    $guid = get_guid_v4();
    $timeStamp = microtime(true);
    $salt = 'user_salt';
    $token = md5("{$wxappId}_{$timeStamp}_{$userId}_{$guid}_{$salt}");
    
    echo "🔑 Generated token: {$token}\n\n";
    
    // Store token in cache (30 days)
    $cacheData = [
        'user' => $user,
        'openid' => $user['open_id'],
        'store_id' => $wxappId,
        'is_login' => true,
    ];
    
    Cache::set($token, $cacheData, 86400 * 30);
    
    echo "✅ Token stored in cache (expires in 30 days)\n\n";
    
    // Verify token was stored
    $cached = Cache::get($token);
    if ($cached) {
        echo "✅ Token verification successful\n";
        echo "  Cached user ID: {$cached['user']['user_id']}\n";
        echo "  Cached openid: {$cached['openid']}\n";
        echo "  Cached store_id: {$cached['store_id']}\n\n";
    } else {
        echo "❌ Token verification failed\n\n";
        exit(1);
    }
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📝 Frontend Setup Instructions:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "1. Open your browser console (F12)\n";
    echo "2. Navigate to: http://localhost:3000/\n";
    echo "3. Execute the following code:\n\n";
    echo "localStorage.setItem('token', '{$token}');\n";
    echo "localStorage.setItem('userId', '{$userId}');\n";
    echo "window.location.reload();\n\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    echo "✅ Token generation complete!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

/**
 * Helper function to generate GUID v4
 * @return string
 */
function get_guid_v4() {
    if (function_exists('com_create_guid') === true) {
        return trim(com_create_guid(), '{}');
    }
    
    $data = openssl_random_pseudo_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
