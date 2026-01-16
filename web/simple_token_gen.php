<?php
/**
 * Simple Token Generator (Standalone - No Framework Dependencies)
 * Access via: http://localhost:8080/simple_token_gen.php
 */

// Database configuration
$host = '103.119.1.84';
$database = 'xinsuju';
$username = 'xinsuju';
$password = 'cJGzwZTDCLHzWXN4';
$prefix = 'yoshop_';

// Get parameters
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 31831;
$wxappId = isset($_GET['wxapp_id']) ? (int)$_GET['wxapp_id'] : 10001;

header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Token Generator</title>
    <style>
        body{font-family:monospace;padding:20px;background:#1e1e1e;color:#d4d4d4;}
        .success{color:#4ec9b0;}.error{color:#f48771;}.info{color:#9cdcfe;}
        .code{background:#2d2d2d;padding:15px;border-radius:5px;margin:10px 0;word-break:break-all;}
        button{background:#0e639c;color:white;border:none;padding:10px 20px;cursor:pointer;border-radius:3px;font-size:14px;margin:5px;}
        button:hover{background:#1177bb;}
        ul{list-style:none;padding:0;}
        li{padding:5px 0;}
        a{color:#9cdcfe;text-decoration:none;}
        a:hover{text-decoration:underline;}
    </style>
</head>
<body>

<h2>🔧 Token Generator for Development</h2>
<hr>

<?php

try {
    // Connect to database
    $pdo = new PDO(
        "mysql:host={$host};dbname={$database};charset=utf8",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Get user info
    $stmt = $pdo->prepare("SELECT * FROM {$prefix}user WHERE user_id = ? AND wxapp_id = ? AND is_delete = 0");
    $stmt->execute([$userId, $wxappId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "<p class='error'>❌ Error: User not found (user_id={$userId}, wxapp_id={$wxappId})</p>";
        echo "<p>Available users with wxapp_id=10001:</p>";
        
        $stmt = $pdo->prepare("SELECT user_id, nickName, mobile FROM {$prefix}user WHERE wxapp_id = 10001 AND is_delete = 0 LIMIT 10");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
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
    $guid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    
    $timeStamp = microtime(true);
    $salt = 'user_salt';
    $token = md5("{$wxappId}_{$timeStamp}_{$userId}_{$guid}_{$salt}");
    
    echo "<p class='info'>🔑 Generated token:</p>";
    echo "<div class='code' id='tokenValue'>{$token}</div>";
    
    // Note: We can't store in ThinkPHP cache from here, but we can provide instructions
    echo "<p class='success'>✅ Token generated successfully!</p>";
    echo "<p class='info'>⚠️ Note: This token needs to be set in your frontend localStorage.</p>";
    
    echo "<hr>";
    echo "<h3>📝 Setup Instructions:</h3>";
    echo "<p>Choose one of the following methods:</p>";
    
    echo "<h4>Method 1: Automatic (Recommended)</h4>";
    echo "<button onclick=\"openFrontendAndSetToken()\">🚀 Open Frontend & Set Token</button>";
    
    echo "<h4>Method 2: Manual</h4>";
    echo "<p>1. Open <a href='http://localhost:3000/' target='_blank'>http://localhost:3000/</a></p>";
    echo "<p>2. Open browser console (F12)</p>";
    echo "<p>3. Execute this code:</p>";
    echo "<div class='code'>";
    echo "localStorage.setItem('token', '{$token}');<br>";
    echo "localStorage.setItem('userId', '{$userId}');<br>";
    echo "window.location.reload();";
    echo "</div>";
    
    echo "<button onclick=\"copyToClipboard()\">📋 Copy Code</button>";
    
    echo "<script>";
    echo "function openFrontendAndSetToken() {";
    echo "  const token = '{$token}';";
    echo "  const userId = '{$userId}';";
    echo "  const url = 'http://localhost:3000/';";
    echo "  const newWindow = window.open(url, '_blank');";
    echo "  setTimeout(() => {";
    echo "    if (newWindow) {";
    echo "      newWindow.localStorage.setItem('token', token);";
    echo "      newWindow.localStorage.setItem('userId', userId);";
    echo "      newWindow.location.reload();";
    echo "    }";
    echo "  }, 1000);";
    echo "  alert('Opening frontend... Token will be set automatically.');";
    echo "}";
    echo "function copyToClipboard() {";
    echo "  const code = \"localStorage.setItem('token', '{$token}');\\nlocalStorage.setItem('userId', '{$userId}');\\nwindow.location.reload();\";";
    echo "  navigator.clipboard.writeText(code).then(() => {";
    echo "    alert('Code copied to clipboard!');";
    echo "  });";
    echo "}";
    echo "</script>";
    
    echo "<hr>";
    echo "<h3>🔍 Testing the Token:</h3>";
    echo "<p>After setting the token, test these API endpoints:</p>";
    echo "<ul>";
    echo "<li><a href='http://localhost:8080/index.php?s=api/page/storage_list&wxapp_id=10001&token={$token}' target='_blank'>Storage List API</a></li>";
    echo "<li><a href='http://localhost:8080/index.php?s=api/package/claim_list&wxapp_id=10001&token={$token}' target='_blank'>Claim List API</a></li>";
    echo "</ul>";
    
    echo "<hr>";
    echo "<p class='success'>✅ Token generation complete!</p>";
    echo "<p class='info'>💡 Tip: Bookmark this page for easy token generation during development.</p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Database Error: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

?>

</body>
</html>
