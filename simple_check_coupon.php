<?php
/**
 * 简单检查优惠券数据
 */

// 读取数据库配置
$config = include __DIR__ . '/source/application/database.php';

// 连接数据库
$mysqli = new mysqli(
    $config['hostname'],
    $config['username'],
    $config['password'],
    $config['database'],
    $config['hostport']
);

if ($mysqli->connect_error) {
    die("数据库连接失败: " . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

echo "=== 检查优惠券数据 ===\n\n";

// 1. 检查优惠券表
echo "1. 检查优惠券表...\n";
$result = $mysqli->query("SELECT * FROM yoshop_coupon WHERE is_delete = 0 ORDER BY sort ASC");

if (!$result) {
    die("查询失败: " . $mysqli->error);
}

$coupons = [];
while ($row = $result->fetch_assoc()) {
    $coupons[] = $row;
}

echo "找到 " . count($coupons) . " 个优惠券\n\n";

if (empty($coupons)) {
    echo "⚠️ 数据库中没有优惠券数据\n\n";
    echo "建议: 运行以下 SQL 创建测试数据:\n";
    echo "---\n";
    $time = time();
    echo "INSERT INTO `yoshop_coupon` (`name`, `color`, `coupon_type`, `reduce_price`, `discount`, `min_price`, `expire_type`, `expire_day`, `start_time`, `end_time`, `total_num`, `receive_num`, `is_open`, `sort`, `wxapp_id`, `create_time`) VALUES\n";
    echo "('满100减10运费券', 10, 10, 10.00, 10, 100.00, 10, 7, 0, 0, 100, 0, 0, 100, 10001, $time),\n";
    echo "('满200减30运费券', 20, 10, 30.00, 10, 200.00, 10, 7, 0, 0, 50, 0, 0, 90, 10001, $time),\n";
    echo "('运费9折优惠券', 30, 20, 0.00, 90, 50.00, 10, 7, 0, 0, 200, 0, 0, 80, 10001, $time);\n";
    echo "---\n\n";
    
    echo "是否立即创建测试数据? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    
    if (strtolower($line) === 'y') {
        $sql1 = "INSERT INTO `yoshop_coupon` (`name`, `color`, `coupon_type`, `reduce_price`, `discount`, `min_price`, `expire_type`, `expire_day`, `start_time`, `end_time`, `total_num`, `receive_num`, `is_open`, `sort`, `wxapp_id`, `create_time`) VALUES ('满100减10运费券', 10, 10, 10.00, 10, 100.00, 10, 7, 0, 0, 100, 0, 0, 100, 10001, $time)";
        $sql2 = "INSERT INTO `yoshop_coupon` (`name`, `color`, `coupon_type`, `reduce_price`, `discount`, `min_price`, `expire_type`, `expire_day`, `start_time`, `end_time`, `total_num`, `receive_num`, `is_open`, `sort`, `wxapp_id`, `create_time`) VALUES ('满200减30运费券', 20, 10, 30.00, 10, 200.00, 10, 7, 0, 0, 50, 0, 0, 90, 10001, $time)";
        $sql3 = "INSERT INTO `yoshop_coupon` (`name`, `color`, `coupon_type`, `reduce_price`, `discount`, `min_price`, `expire_type`, `expire_day`, `start_time`, `end_time`, `total_num`, `receive_num`, `is_open`, `sort`, `wxapp_id`, `create_time`) VALUES ('运费9折优惠券', 30, 20, 0.00, 90, 50.00, 10, 7, 0, 0, 200, 0, 0, 80, 10001, $time)";
        
        if ($mysqli->query($sql1) && $mysqli->query($sql2) && $mysqli->query($sql3)) {
            echo "✅ 测试数据创建成功!\n\n";
            
            // 重新查询
            $result = $mysqli->query("SELECT * FROM yoshop_coupon WHERE is_delete = 0 ORDER BY sort ASC");
            $coupons = [];
            while ($row = $result->fetch_assoc()) {
                $coupons[] = $row;
            }
        } else {
            echo "❌ 创建失败: " . $mysqli->error . "\n";
            exit;
        }
    } else {
        exit;
    }
}

// 显示优惠券列表
foreach ($coupons as $index => $coupon) {
    echo "优惠券 #" . ($index + 1) . ":\n";
    echo "  ID: {$coupon['coupon_id']}\n";
    echo "  名称: {$coupon['name']}\n";
    echo "  类型: " . ($coupon['coupon_type'] == 10 ? '满减券' : '折扣券') . "\n";
    
    if ($coupon['coupon_type'] == 10) {
        echo "  优惠: 满{$coupon['min_price']}减{$coupon['reduce_price']}\n";
    } else {
        echo "  优惠: 满{$coupon['min_price']}打" . ($coupon['discount'] / 10) . "折\n";
    }
    
    echo "  有效期: " . ($coupon['expire_type'] == 10 ? "领取后{$coupon['expire_day']}天" : "固定时间段") . "\n";
    echo "  总数量: " . ($coupon['total_num'] == -1 ? '不限制' : $coupon['total_num']) . "\n";
    echo "  已领取: {$coupon['receive_num']}\n";
    echo "  是否公开: " . ($coupon['is_open'] == 0 ? '是' : '否') . "\n";
    echo "\n";
}

// 2. 检查用户优惠券表
echo "2. 检查用户优惠券表...\n";
$result = $mysqli->query("SELECT COUNT(*) as count FROM yoshop_user_coupon WHERE is_delete = 0");
$row = $result->fetch_assoc();
echo "已领取的优惠券总数: {$row['count']}\n\n";

// 3. 检查测试用户
echo "3. 检查测试用户...\n";
$result = $mysqli->query("SELECT * FROM yoshop_user WHERE is_delete = 0 ORDER BY user_id ASC LIMIT 1");

if ($result && $testUser = $result->fetch_assoc()) {
    echo "找到测试用户:\n";
    echo "  用户ID: {$testUser['user_id']}\n";
    echo "  昵称: {$testUser['nickName']}\n";
    
    // 检查该用户已领取的优惠券
    $stmt = $mysqli->prepare("SELECT * FROM yoshop_user_coupon WHERE user_id = ? AND is_delete = 0");
    $stmt->bind_param("i", $testUser['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $userCouponList = [];
    while ($row = $result->fetch_assoc()) {
        $userCouponList[] = $row;
    }
    
    echo "  已领取优惠券: " . count($userCouponList) . " 个\n";
    
    if (!empty($userCouponList)) {
        foreach ($userCouponList as $uc) {
            echo "    - {$uc['name']} (优惠券ID: {$uc['coupon_id']})\n";
        }
    }
    
    echo "\n✅ 数据检查完成，可以进行接口测试\n";
    echo "\n下一步: 使用浏览器访问测试页面\n";
    echo "URL: http://localhost/test_coupon_api.html\n";
} else {
    echo "⚠️ 没有找到测试用户\n";
}

$mysqli->close();

echo "\n=== 检查完成 ===\n";
