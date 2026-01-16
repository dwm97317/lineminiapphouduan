<?php
/**
 * 测试优惠券领取接口
 * 
 * 测试步骤:
 * 1. 获取优惠券列表
 * 2. 领取一个可用的优惠券
 * 3. 验证领取结果
 * 4. 尝试重复领取（应该失败）
 */

// 配置
$base_url = 'http://localhost:8080/api';
$token = '9846ee87b5afc4b654a81e79c9da768d'; // 替换为实际的用户 token
$wxapp_id = 10001; // 根据实际情况修改

echo "=== 优惠券领取接口测试 ===\n\n";

// 1. 获取优惠券列表
echo "1. 获取优惠券列表...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$base_url/coupon/lists?wxapp_id=$wxapp_id&token=$token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP 状态码: $http_code\n";
echo "响应数据:\n";
$data = json_decode($response, true);
print_r($data);

if ($data['code'] != 1 || empty($data['data']['list'])) {
    die("\n错误: 无法获取优惠券列表\n");
}

// 找到第一个可领取的优惠券
$coupon_to_receive = null;
foreach ($data['data']['list'] as $coupon) {
    if (!$coupon['is_receive'] && $coupon['state']['value'] == 1) {
        $coupon_to_receive = $coupon;
        break;
    }
}

if (!$coupon_to_receive) {
    die("\n提示: 没有可领取的优惠券\n");
}

echo "\n找到可领取的优惠券:\n";
echo "ID: {$coupon_to_receive['coupon_id']}\n";
echo "名称: {$coupon_to_receive['name']}\n";
echo "类型: {$coupon_to_receive['coupon_type']['text']}\n";

// 2. 领取优惠券
echo "\n2. 领取优惠券...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$base_url/coupon/receive?wxapp_id=$wxapp_id&token=$token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'coupon_id' => $coupon_to_receive['coupon_id']
]));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP 状态码: $http_code\n";
echo "响应数据:\n";
$receive_result = json_decode($response, true);
print_r($receive_result);

if ($receive_result['code'] == 1) {
    echo "\n✅ 领取成功!\n";
} else {
    echo "\n❌ 领取失败: {$receive_result['msg']}\n";
}

// 3. 尝试重复领取
echo "\n3. 尝试重复领取（应该失败）...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$base_url/coupon/receive?wxapp_id=$wxapp_id&token=$token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'coupon_id' => $coupon_to_receive['coupon_id']
]));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP 状态码: $http_code\n";
echo "响应数据:\n";
$duplicate_result = json_decode($response, true);
print_r($duplicate_result);

if ($duplicate_result['code'] == 0 && strpos($duplicate_result['msg'], '已领取') !== false) {
    echo "\n✅ 防重复领取验证通过!\n";
} else {
    echo "\n⚠️ 防重复领取验证异常\n";
}

// 4. 再次获取列表，验证 is_receive 状态
echo "\n4. 验证优惠券状态更新...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$base_url/coupon/lists?wxapp_id=$wxapp_id&token=$token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
foreach ($data['data']['list'] as $coupon) {
    if ($coupon['coupon_id'] == $coupon_to_receive['coupon_id']) {
        if ($coupon['is_receive']) {
            echo "✅ 优惠券状态已更新为已领取\n";
        } else {
            echo "⚠️ 优惠券状态未更新\n";
        }
        break;
    }
}

echo "\n=== 测试完成 ===\n";
