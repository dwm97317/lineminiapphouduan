<?php
/**
 * 为现有的LINE用户生成 user_code
 */

$host = '103.119.1.84';
$db = 'xinsuju';
$user = 'xinsuju';
$pass = 'cJGzwZTDCLHzWXN4';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 获取系统设置
    $sql = "SELECT `values` FROM yoshop_setting WHERE `key` = 'store' AND wxapp_id = 10001";
    $stmt = $conn->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $settings = json_decode($result['values'], true);
    
    echo "User Code Settings:" . PHP_EOL;
    echo "Mode: " . $settings['usercode_mode']['mode'] . PHP_EOL;
    echo "Char: " . $settings['usercode_mode'][30]['char'] . PHP_EOL;
    echo "Number: " . $settings['usercode_mode'][30]['number'] . PHP_EOL;
    echo PHP_EOL;
    
    // 获取所有没有 user_code 的 LINE 用户
    $sql = "SELECT user_id, nickName, line_openid FROM yoshop_user 
            WHERE line_openid IS NOT NULL AND line_openid != '' 
            AND (user_code IS NULL OR user_code = '')";
    $stmt = $conn->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) == 0) {
        echo "No LINE users need user_code generation." . PHP_EOL;
        exit;
    }
    
    echo "Found " . count($users) . " LINE users without user_code" . PHP_EOL;
    echo PHP_EOL;
    
    // 生成 user_code 的函数
    function generateUserCode($conn, $mode, $char, $number) {
        $maxAttempts = 100;
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            // 根据模式生成 user_code
            switch ($mode) {
                case '10': // 纯数字
                    $min = pow(10, $number - 1);
                    $max = pow(10, $number) - 1;
                    $code = rand($min, $max);
                    break;
                case '20': // 纯字母
                    $code = '';
                    $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    for ($i = 0; $i < $number; $i++) {
                        $code .= $letters[rand(0, 25)];
                    }
                    break;
                case '30': // 字母+数字
                    $min = pow(10, $number - 1);
                    $max = pow(10, $number) - 1;
                    $code = $char . rand($min, $max);
                    break;
                default:
                    $min = pow(10, $number - 1);
                    $max = pow(10, $number) - 1;
                    $code = rand($min, $max);
            }
            
            // 检查是否已存在
            $checkSql = "SELECT COUNT(*) FROM yoshop_user WHERE user_code = :code";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute(['code' => $code]);
            $count = $checkStmt->fetchColumn();
            
            if ($count == 0) {
                return $code;
            }
            
            $attempt++;
        }
        
        throw new Exception("Failed to generate unique user_code after $maxAttempts attempts");
    }
    
    // 为每个用户生成并更新 user_code
    $updateStmt = $conn->prepare("UPDATE yoshop_user SET user_code = :code WHERE user_id = :user_id");
    
    foreach ($users as $user) {
        $userCode = generateUserCode(
            $conn, 
            $settings['usercode_mode']['mode'],
            $settings['usercode_mode'][30]['char'],
            $settings['usercode_mode'][30]['number']
        );
        
        $updateStmt->execute([
            'code' => $userCode,
            'user_id' => $user['user_id']
        ]);
        
        echo "✅ User ID: " . $user['user_id'] . " - Generated code: " . $userCode . PHP_EOL;
    }
    
    echo PHP_EOL;
    echo "✅ All LINE users have been assigned user_code!" . PHP_EOL;
    
} catch(PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . PHP_EOL;
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
}

$conn = null;
