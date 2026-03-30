<?php
// Mock ThinkPHP app context
define('APP_PATH', __DIR__ . '/source/application/');
define('THINK_PATH', __DIR__ . '/source/thinkphp/');
require __DIR__ . '/source/thinkphp/base.php';

use think\Cache;

$host="103.119.1.84"; 
$db="xinsuju"; 
$user="xinsuju"; 
$pass="cJGzwZTDCLHzWXN4"; 
$pdo=new PDO("mysql:host=$host;dbname=$db",$user,$pass); 

$wxappid = 10001;
// lay user_id cua wxappid nay
$stmt = $pdo->prepare("SELECT user_id, open_id FROM yoshop_user WHERE wxapp_id = ? LIMIT 1");
$stmt->execute([$wxappid]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
    die("Khong tim thay user");
}

$user_id = $userData['user_id'];
$openid = $userData['open_id'];

// Ignore warnings
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

// Init App configurations so config works
\think\App::initCommon();

// Generate token string
$guid = getGuidV4();
$timeStamp = microtime(true);
$salt = 'token_salt';
$token = md5("{$wxappid}_{$timeStamp}_{$openid}_{$guid}_{$salt}");


// Lưu vào cache
Cache::set($token, [
    'user' => [
        'user_id' => $user_id,
        'wxapp_id' => $wxappid
    ],
    'wxapp_id' => $wxappid,
    'is_login' => true,
], 2592000); // 30 ngày


echo "============================================\n";
echo "😎 TẠO FAKE TOKEN (MÔI TRƯỜNG TEST) THÀNH CÔNG!\n";
echo "============================================\n";
echo "Token để bạn mang đi test (nhập vào Headers):\n";
echo "\n🎯 " . $token . "\n\n";
echo "Thông tin đang giả lập:\n";
echo "- User ID:  " . $user_id . "\n";
echo "- Tenant:   wxapp_id = " . $wxappid . "\n";
echo "============================================\n";
echo "Trong Postman, cấu hình HEADER:\n";
echo "  token:    " . $token . "\n";
echo "  wxapp_id: " . $wxappid . "\n";
echo "============================================\n";

