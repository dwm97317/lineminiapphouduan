<?php
/**
 * LINE消息通知集成测试脚本
 * 
 * 用途：测试所有7种通知类型的发送功能
 * 
 * 使用方法：
 * 1. 修改下面的测试配置
 * 2. 在浏览器中访问此文件：http://your-domain/test_notification_integration.php
 * 3. 或在命令行运行：php test_notification_integration.php
 */

// 引入ThinkPHP框架
require_once __DIR__ . '/../../thinkphp/start.php';

use app\common\model\Setting as SettingModel;
use app\common\service\message\line\Inwarehouse;
use app\common\service\message\line\Sendpack;
use app\common\service\message\line\Payment;
use app\common\service\message\line\Dabaosuccess;
use app\common\service\message\line\Payorder;
use app\common\service\message\line\Toshop;
use app\common\service\message\line\Outapply;

// ==================== 测试配置 ====================
$TEST_CONFIG = [
    'wxapp_id' => 10001, // 小程序ID
    'member_id' => 1, // 测试用户ID（必须有line_openid）
    'line_user_id' => 'Ud4e37d68c438cc70350957039add98d8', // LINE用户ID（用于验证）
];

// ==================== 测试数据 ====================

// 1. 包裹入库通知测试数据
$inwarehouseData = [
    'wxapp_id' => $TEST_CONFIG['wxapp_id'],
    'member_id' => $TEST_CONFIG['member_id'],
    'id' => 123,
    'shop_name' => '武汉仓库',
    'express_num' => 'TEST123456',
    'entering_warehouse_time' => date('Y-m-d H:i:s'),
    'weight' => 1.5,
    'remark' => '包裹已入库，可提交打包',
    'images' => [
        'https://example.com/image1.jpg',
        'https://example.com/image2.jpg',
    ],
];

// 2. 发货通知测试数据
$sendpackData = [
    'wxapp_id' => $TEST_CONFIG['wxapp_id'],
    'member_id' => $TEST_CONFIG['member_id'],
    'order_sn' => 'ORD' . date('YmdHis'),
    't_order_sn' => 'TRACK' . date('YmdHis'),
    'weight' => 2.5,
    't_name' => 'DHL快递',
    'send_time' => date('Y-m-d H:i:s'),
    'tracking_url' => 'https://example.com/tracking',
];

// 3. 打包完成通知测试数据
$dabaosuccessData = [
    'wxapp_id' => $TEST_CONFIG['wxapp_id'],
    'member_id' => $TEST_CONFIG['member_id'],
    'order_sn' => 'ORD' . date('YmdHis'),
    'pack_count' => 3,
    'weight' => 5.0,
    'volume' => 0.05,
    'order_id' => 456,
];

// 4. 支付成功通知测试数据
$paymentData = [
    'wxapp_id' => $TEST_CONFIG['wxapp_id'],
    'member_id' => $TEST_CONFIG['member_id'],
    'order_sn' => 'ORD' . date('YmdHis'),
    'total_free' => 150.00,
    'pay_time' => date('Y-m-d H:i:s'),
    'remark' => '支付成功',
];

// 5. 付款单生成通知测试数据
$payorderData = [
    'wxapp_id' => $TEST_CONFIG['wxapp_id'],
    'member_id' => $TEST_CONFIG['member_id'],
    'order_sn' => 'ORD' . date('YmdHis'),
    'total_amount' => 200.00,
    'due_date' => date('Y-m-d', strtotime('+7 days')),
    'remark' => '请及时支付',
    'order_id' => 789,
];

// 6. 到仓通知测试数据
$toshopData = [
    'wxapp_id' => $TEST_CONFIG['wxapp_id'],
    'member_id' => $TEST_CONFIG['member_id'],
    'id' => 321,
    'express_company' => '顺丰快递',
    'express_num' => 'SF123456789',
    'arrive_time' => date('Y-m-d H:i:s'),
    'remark' => '包裹已到仓',
];

// 7. 出库申请通知测试数据
$outapplyData = [
    'wxapp_id' => $TEST_CONFIG['wxapp_id'],
    'member_id' => $TEST_CONFIG['member_id'],
    'apply_sn' => 'APPLY' . date('YmdHis'),
    'package_count' => 2,
    'status' => '待审核',
    'remark' => '出库申请已提交',
    'apply_id' => 654,
];

// ==================== 测试函数 ====================

function testNotification($name, $serviceClass, $data) {
    echo "\n========== 测试 {$name} ==========\n";
    echo "测试数据：\n";
    print_r($data);
    
    try {
        $service = new $serviceClass();
        $result = $service->send($data);
        
        if ($result) {
            echo "✅ {$name} 发送成功\n";
        } else {
            echo "❌ {$name} 发送失败（可能是用户未添加好友或模板未启用）\n";
        }
        
        return $result;
    } catch (\Exception $e) {
        echo "❌ {$name} 发送异常：" . $e->getMessage() . "\n";
        return false;
    }
}

// ==================== 执行测试 ====================

echo "==================== LINE消息通知集成测试 ====================\n";
echo "测试时间：" . date('Y-m-d H:i:s') . "\n";
echo "测试配置：\n";
print_r($TEST_CONFIG);

$results = [];

// 测试1：包裹入库通知
$results['inwarehouse'] = testNotification(
    '包裹入库通知',
    Inwarehouse::class,
    $inwarehouseData
);

// 测试2：发货通知
$results['sendpack'] = testNotification(
    '发货通知',
    Sendpack::class,
    $sendpackData
);

// 测试3：打包完成通知
$results['dabaosuccess'] = testNotification(
    '打包完成通知',
    Dabaosuccess::class,
    $dabaosuccessData
);

// 测试4：支付成功通知
$results['payment'] = testNotification(
    '支付成功通知',
    Payment::class,
    $paymentData
);

// 测试5：付款单生成通知
$results['payorder'] = testNotification(
    '付款单生成通知',
    Payorder::class,
    $payorderData
);

// 测试6：到仓通知
$results['toshop'] = testNotification(
    '到仓通知',
    Toshop::class,
    $toshopData
);

// 测试7：出库申请通知
$results['outapply'] = testNotification(
    '出库申请通知',
    Outapply::class,
    $outapplyData
);

// ==================== 测试总结 ====================

echo "\n==================== 测试总结 ====================\n";
$successCount = count(array_filter($results));
$totalCount = count($results);
echo "总测试数：{$totalCount}\n";
echo "成功数：{$successCount}\n";
echo "失败数：" . ($totalCount - $successCount) . "\n";

echo "\n详细结果：\n";
foreach ($results as $type => $result) {
    $status = $result ? '✅ 成功' : '❌ 失败';
    echo "- {$type}: {$status}\n";
}

if ($successCount === $totalCount) {
    echo "\n🎉 所有测试通过！LINE消息通知集成成功！\n";
} else {
    echo "\n⚠️  部分测试失败，请检查：\n";
    echo "1. LINE消息通知是否已启用\n";
    echo "2. 各消息模板是否已配置并启用\n";
    echo "3. 用户是否已添加LINE OA为好友\n";
    echo "4. 用户表是否有line_openid字段数据\n";
    echo "5. LINE Access Token是否有效\n";
}

echo "\n==================== 测试完成 ====================\n";
