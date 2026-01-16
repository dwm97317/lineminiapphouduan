<?php
$host = '103.119.1.84';
$db = 'xinsuju';
$user = 'xinsuju';
$pass = 'cJGzwZTDCLHzWXN4';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "SELECT `values` FROM yoshop_setting WHERE `key` = 'store' AND wxapp_id = 10001";
    $stmt = $conn->query($sql);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $values = json_decode($result['values'], true);
        echo "User Code Mode Settings:" . PHP_EOL;
        echo "is_show: " . $values['usercode_mode']['is_show'] . PHP_EOL;
        echo "mode: " . $values['usercode_mode']['mode'] . PHP_EOL;
        echo PHP_EOL;
        echo "Full usercode_mode config:" . PHP_EOL;
        print_r($values['usercode_mode']);
    } else {
        echo 'No settings found' . PHP_EOL;
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$conn = null;
