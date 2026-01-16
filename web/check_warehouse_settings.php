<?php
// Check warehouse settings and user data
$conn = new mysqli('103.119.1.84', 'xinsuju', 'cJGzwZTDCLHzWXN4', 'xinsuju');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Get store settings
$result = $conn->query("SELECT * FROM yoshop_setting WHERE `key`='store' AND wxapp_id=10001");
if ($row = $result->fetch_assoc()) {
    $values = json_decode($row['values'], true);
    echo "Store Settings:\n";
    echo "is_show: " . $values['usercode_mode']['is_show'] . "\n";
    echo "link_mode: " . $values['link_mode'] . "\n";
    echo "address_mode: " . $values['address_mode'] . "\n";
    echo "is_change_uid: " . $values['is_change_uid'] . "\n";
}

// Get LINE user info
$result = $conn->query("SELECT user_id, nickName, user_code FROM yoshop_user WHERE line_openid IS NOT NULL AND line_openid != '' LIMIT 1");
if ($row = $result->fetch_assoc()) {
    echo "\nLINE User:\n";
    echo "user_id: " . $row['user_id'] . "\n";
    echo "nickName: " . $row['nickName'] . "\n";
    echo "user_code: " . ($row['user_code'] ?: 'NULL') . "\n";
}

// Get warehouse info
$result = $conn->query("SELECT shop_id, linkman, shop_name FROM yoshop_store_shop WHERE wxapp_id=10001 LIMIT 1");
if ($row = $result->fetch_assoc()) {
    echo "\nWarehouse:\n";
    echo "shop_id: " . $row['shop_id'] . "\n";
    echo "linkman: " . $row['linkman'] . "\n";
    echo "shop_name: " . $row['shop_name'] . "\n";
}

$conn->close();
