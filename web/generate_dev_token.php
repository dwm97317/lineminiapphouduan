<?php
/**
 * Generate a valid token for development testing
 * Access via: http://localhost:8080/generate_dev_token.php?user_id=31831&wxapp_id=10001
 */

// Load ThinkPHP framework
define('APP_PATH', __DIR__ . '/../source/application/');
define('RUNTIME_PATH', __DIR__ . '/../source/runtime/');
define('CONF_PATH', __DIR__ . '/../source/config/');

require __DIR__ . '/../source/thinkphp/start.php';

use think\Cache;
use app\api\model\User as UserModel;

// Get parameters
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 31831;
$wxappId = isset($_GET['wxapp_id']) ? (int)$_GET['wxapp_id'] : 10001;

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Token Generator</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e1e1e;color:#d4d4d4;}";
echo ".success{color:#4ec9b0;}.error{color:#f48771;}.info{color:#9cdcfe;}";
echo ".code{background:#2d2d2d;padding:15px;border-radius:5px;margin:10px 0;}";
echo "button{background:#0e639c;color:white;border:none;padding:10px 20px;cursor:pointer;border-radius:3px;font-size:14px;}";
echo "button:hover{background:#1177bb;}</style></head><body>";

echo "<h2>🔧 Token Generator for Development</h2>";
echo "<hr>";

try {
    // Get user info
    $user = UserModel::useGlobalScope(false)
        ->where(['user_id' => $userId, 'wxapp_id' => $wxappId, 'is_delete' => 0])
        ->find();
    
    if (!$user) {
        echo "<p class='error'>❌ Error: User not found (user_id={$userId}, wxapp_id={$wxappId})</p>";
        echo "<p>Available users with wxapp_id=10001:</p>";
        
        $users = UserModel::useGlobalScope(false)
            ->where(['wxapp_id' => 10001, 'is_delete' => 0])
            ->limit(10)
            ->select();
        
        echo "<ul>";
        foreach ($users as $u) {
            echo "<li><a href='?user_id={$u['user_id']}&wxapp_id=10001'>";
            echo "User ID: {$u['user_id']} - {$u['nickName']}</a></li>";
        }
        echo "</ul>";
        exit;
    }
    
    echo "<p class='success'>✅ User found:</p>";
    echo "<ul>";
    echo "<li>User ID: {$user['user_id']}</li>";
    echo "<li>Nickname: {$user['nickName']}</li>";
    echo "<li>Mobile: {$user['mobile']}</li>";
    echo "<li>Wxapp ID: {$user['wxapp_id']}</li>";
    echo "</ul>";
    
    // Generate token (same logic as Login service)
    $guid = get_guid_v4(); // This function is defined in common.php
    $timeStamp = microtime(true);
    $salt = 'user_salt';
    $token = md5("{$wxappId}_{$timeStamp}_{$userId}_{$guid}_{$salt}");
    
    echo "<p class='info'>🔑 Generated token:</p>";
    echo "<div class='code'>{$token}</div>";
    
    // Store token in cache (30 days)
    $cacheData = [
        'user' => $user,
        'openid' => $user['open_id'],
        'store_id' => $wxappId,
        'is_login' => true,
    ];
    
    Cache::set($token, $cacheData, 86400 * 30);
    
    echo "<p class='success'>✅ Token stored in cache (expires in 30 days)</p>";
    
    // Verify token was stored
    $cached = Cache::get($token);
    if ($cached) {
        echo "<p class='success'>✅ Token verification successful</p>";
        echo "<ul>";
        echo "<li>Cached user ID: {$cached['user']['user_id']}</li>";
        echo "<li>Cached openid: {$cached['openid']}</li>";
        echo "<li>Cached store_id: {$cached['store_id']}</li>";
        echo "</ul>";
    } else {
        echo "<p class='error'>❌ Token verification failed</p>";
        exit;
    }
    
    echo "<hr>";
    echo "<h3>📝 Frontend Setup Instructions:</h3>";
    echo "<p>Click the button below to automatically set the token in your frontend:</p>";
    
    echo "<div class='code'>";
    echo "<button onclick=\"setToken()\">🚀 Set Token in Frontend</button>";
    echo "</div>";
    
    echo "<p>Or manually execute this code in your browser console (F12) at <strong>http://localhost:3000/</strong>:</p>";
    echo "<div class='code'>";
    echo "localStorage.setItem('token', '{$token}');<br>";
    echo "localStorage.setItem('userId', '{$userId}');<br>";
    echo "window.location.reload();";
    echo "</div>";
    
    echo "<script>";
    echo "function setToken() {";
    echo "  localStorage.setItem('token', '{$token}');";
    echo "  localStorage.setItem('userId', '{$userId}');";
    echo "  alert('Token set successfully! Redirecting to frontend...');";
    echo "  window.location.href = 'http://localhost:3000/';";
    echo "}";
    echo "</script>";
    
    echo "<hr>";
    echo "<p class='success'>✅ Token generation complete!</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";
