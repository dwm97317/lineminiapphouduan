<?php
/**
 * Set Development Token in ThinkPHP Cache
 * This script properly stores a token in the cache system that the backend can recognize
 * Access via: http://localhost:8080/set_dev_token.php?user_id=31831&wxapp_id=10001
 */

// Define paths
define('APP_PATH', __DIR__ . '/../source/application/');
define('RUNTIME_PATH', __DIR__ . '/../source/runtime/');
define('CONF_PATH', __DIR__ . '/../source/config/');

// Load ThinkPHP
require __DIR__ . '/../source/thinkphp/start.php';

// Import classes
use think\Cache;
use app\api\model\User as UserModel;

try {
    
    // Get parameters
    $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 31831;
    $wxappId = isset($_GET['wxapp_id']) ? (int)$_GET['wxapp_id'] : 10001;
    
    header('Content-Type: text/html; charset=utf-8');
    
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Set Dev Token</title>";
    echo "<style>body{font-family:monospace;padding:20px;background:#1e1e1e;color:#d4d4d4;}";
    echo ".success{color:#4ec9b0;}.error{color:#f48771;}.info{color:#9cdcfe;}";
    echo ".code{background:#2d2d2d;padding:15px;border-radius:5px;margin:10px 0;word-break:break-all;}";
    echo "button{background:#0e639c;color:white;border:none;padding:10px 20px;cursor:pointer;border-radius:3px;font-size:14px;margin:5px;}";
    echo "button:hover{background:#1177bb;}</style></head><body>";
    
    echo "<h2>🔧 Development Token Setup</h2><hr>";
    
    // Get user
    $user = UserModel::useGlobalScope(false)
        ->where(['user_id' => $userId, 'wxapp_id' => $wxappId, 'is_delete' => 0])
        ->find();
    
    if (!$user) {
        echo "<p class='error'>❌ User not found (user_id={$userId}, wxapp_id={$wxappId})</p>";
        
        // Show available users
        $users = UserModel::useGlobalScope(false)
            ->where(['wxapp_id' => 10001, 'is_delete' => 0])
            ->limit(10)
            ->select();
        
        echo "<p>Available users:</p><ul>";
        foreach ($users as $u) {
            echo "<li><a href='?user_id={$u['user_id']}&wxapp_id=10001'>";
            echo "ID: {$u['user_id']} - {$u['nickName']}</a></li>";
        }
        echo "</ul></body></html>";
        exit;
    }
    
    echo "<p class='success'>✅ User found:</p><ul>";
    echo "<li>User ID: {$user['user_id']}</li>";
    echo "<li>Nickname: {$user['nickName']}</li>";
    echo "<li>Mobile: {$user['mobile']}</li>";
    echo "<li>Wxapp ID: {$user['wxapp_id']}</li></ul>";
    
    // Generate token (same as Login service)
    $guid = get_guid_v4();
    $timeStamp = microtime(true);
    $salt = 'user_salt';
    $token = md5("{$wxappId}_{$timeStamp}_{$userId}_{$guid}_{$salt}");
    
    echo "<p class='info'>🔑 Generated token:</p>";
    echo "<div class='code'>{$token}</div>";
    
    // Store in cache (same as Login service - 30 days)
    $cacheData = [
        'user' => $user,
        'openid' => $user['open_id'],
        'store_id' => $wxappId,
        'is_login' => true,
    ];
    
    Cache::set($token, $cacheData, 86400 * 30);
    
    echo "<p class='success'>✅ Token stored in ThinkPHP cache (30 days)</p>";
    
    // Verify
    $cached = Cache::get($token);
    if ($cached && isset($cached['user'])) {
        echo "<p class='success'>✅ Token verified in cache:</p><ul>";
        echo "<li>User ID: {$cached['user']['user_id']}</li>";
        echo "<li>OpenID: {$cached['openid']}</li>";
        echo "<li>Store ID: {$cached['store_id']}</li></ul>";
    } else {
        echo "<p class='error'>❌ Token verification failed</p></body></html>";
        exit;
    }
    
    echo "<hr><h3>📝 Frontend Setup:</h3>";
    echo "<p>Click to set token in frontend:</p>";
    echo "<button onclick=\"setTokenAndOpen()\">🚀 Set Token & Open Frontend</button>";
    echo "<button onclick=\"copyCode()\">📋 Copy Manual Code</button>";
    
    echo "<p>Manual code:</p><div class='code' id='code'>";
    echo "localStorage.setItem('token', '{$token}');<br>";
    echo "localStorage.setItem('userId', '{$userId}');<br>";
    echo "window.location.reload();";
    echo "</div>";
    
    echo "<hr><h3>🧪 Test API:</h3>";
    echo "<p>Test with token:</p><ul>";
    echo "<li><a href='http://localhost:8080/index.php?s=api/page/storage_list&wxapp_id=10001&token={$token}' target='_blank'>Storage List</a></li>";
    echo "<li><a href='http://localhost:8080/index.php?s=api/package/claim_list&wxapp_id=10001&token={$token}' target='_blank'>Claim List</a></li>";
    echo "</ul>";
    
    echo "<script>";
    echo "function setTokenAndOpen() {";
    echo "  const w = window.open('http://localhost:3000/', '_blank');";
    echo "  setTimeout(() => {";
    echo "    if (w) {";
    echo "      w.localStorage.setItem('token', '{$token}');";
    echo "      w.localStorage.setItem('userId', '{$userId}');";
    echo "      w.location.reload();";
    echo "    }";
    echo "  }, 500);";
    echo "}";
    echo "function copyCode() {";
    echo "  const code = document.getElementById('code').innerText;";
    echo "  navigator.clipboard.writeText(code).then(() => alert('Copied!'));";
    echo "}";
    echo "</script>";
    
    echo "<hr><p class='success'>✅ Setup complete!</p>";
    echo "</body></html>";
    
} catch (Exception $e) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><body style='font-family:monospace;padding:20px;background:#1e1e1e;color:#f48771;'>";
    echo "<h2>❌ Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</body></html>";
}
