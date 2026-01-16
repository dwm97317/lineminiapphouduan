<?php
$host = '103.119.1.84';
$db = 'xinsuju';
$user = 'xinsuju';
$pass = 'cJGzwZTDCLHzWXN4';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "SELECT user_id, nickName, line_openid, user_code FROM yoshop_user WHERE line_openid IS NOT NULL AND line_openid != ''";
    $stmt = $conn->query($sql);
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        foreach($users as $row) {
            echo 'User ID: ' . $row['user_id'] . PHP_EOL;
            echo 'Nickname: ' . $row['nickName'] . PHP_EOL;
            echo 'LINE OpenID: ' . $row['line_openid'] . PHP_EOL;
            echo 'User Code: ' . ($row['user_code'] ?: 'NULL/EMPTY') . PHP_EOL;
            echo '---' . PHP_EOL;
        }
    } else {
        echo 'No LINE users found' . PHP_EOL;
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$conn = null;

