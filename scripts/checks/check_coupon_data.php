<?php
/**
 * 检查优惠券数据
 */

// 引入 ThinkPHP
define('APP_PATH', __DIR__ . '/source/application/');
define('RUNTIME_PATH', __DIR__ . '/runtime/');

// 加载框架引导文件
require __DIR__ . '/source/thinkphp/start.php';

echo "=== 检查优惠券数据 ===\n\n";

// 1. 检查优惠券表
echo "1. 检查优惠券表 (yoshop_coupon)...\n";
$coupons = \think\Db::name('coupon')
    ->where('is_delete', 0)
    ->order('sort', 'asc')
    ->select();

echo "找到 " . count($coupons) . " 个优惠券\n\n";

if (empty($coupons)) {
    echo "⚠️ 数据库中没有优惠券数据\n";
    echo "\n建议: 请先在后台管理系统中创建优惠券\n";
    echo "路径: 后台 → 营销 → 优惠券管理 → 添加优惠券\n\n";
    
    echo "或者运行以下 SQL 创建测试数据:\n";
    echo "---\n";
    echo "INSERT INTO `yoshop_coupon` (`name`, `color`, `coupon_type`, `reduce_price`, `discount`, `min_price`, `expire_type`, `expire_day`, `start_time`, `end_time`, `total_num`, `receive_num`, `is_open`, `sort`, `wxapp_id`, `create_time`) VALUES\n";
    echo "('满100减10运费券', 10, 10, 10.00, 10, 100.00, 10, 7, 0, 0, 100, 0, 0, 100, 10001, " . time() . "),\n";
    echo "('满200减30运费券', 20, 10, 30.00, 10, 200.00, 10, 7, 0, 0, 50, 0, 0, 90, 10001, " . time() . "),\n";
    echo "('运费9折优惠券', 30, 20, 0.00, 90, 50.00, 10, 7, 0, 0, 200, 0, 0, 80, 10001, " . time() . ");\n";
    echo "---\n\n";
    exit;
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
    echo "  排序: {$coupon['sort']}\n";
    echo "\n";
}

// 2. 检查用户优惠券表
echo "2. 检查用户优惠券表 (yoshop_user_coupon)...\n";
$userCoupons = \think\Db::name('user_coupon')
    ->where('is_delete', 0)
    ->count();

echo "已领取的优惠券总数: {$userCoupons}\n\n";

// 3. 检查测试用户
echo "3. 检查测试用户...\n";
$testUser = \think\Db::name('user')
    ->where('is_delete', 0)
    ->order('user_id', 'asc')
    ->find();

if ($testUser) {
    echo "找到测试用户:\n";
    echo "  用户ID: {$testUser['user_id']}\n";
    echo "  昵称: {$testUser['nickName']}\n";
    
    // 检查该用户已领取的优惠券
    $userCouponList = \think\Db::name('user_coupon')
        ->where('user_id', $testUser['user_id'])
        ->where('is_delete', 0)
        ->select();
    
    echo "  已领取优惠券: " . count($userCouponList) . " 个\n";
    
    if (!empty($userCouponList)) {
        foreach ($userCouponList as $uc) {
            echo "    - {$uc['name']} (ID: {$uc['coupon_id']})\n";
        }
    }
} else {
    echo "⚠️ 没有找到测试用户\n";
}

echo "\n=== 检查完成 ===\n";
