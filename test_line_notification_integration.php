<?php
/**
 * 测试 LINE 通知集成
 * 模拟订单状态变更，测试通知发送
 */

// 引入 ThinkPHP 框架
require_once __DIR__ . '/source/application/../thinkphp/start.php';

use app\common\service\message\line\Inwarehouse;
use app\common\service\message\line\Sendpack;
use app\common\model\Setting as SettingModel;

echo "=== LINE 通知集成测试 ===\n\n";

// 数据库配置
$config = [
    'type' => 'mysql',
    'hostname' => '103.119.1.84',
    'database' => 'xinsuju',
    'username' => 'xinsuju',
    'password' => 'cJGzwZTDCLHzWXN4',
    'hostport' => '3306',
    'charset' => 'utf8',
    'prefix' => 'yoshop_',
];

try {
    $db = \think\Db::connect($config);
    
    // 1. 查找一个测试用户（已绑定 LINE）
    echo "1. 查找测试用户...\n";
    $testUser = $db->name('user')
        ->where('line_openid', '<>', '')
        ->whereNotNull('line_openid')
        ->find();
    
    if (!$testUser) {
        echo "❌ 未找到已绑定 LINE 的用户\n";
        exit;
    }
    
    echo "✅ 找到测试用户: {$testUser['nickName']} (ID: {$testUser['user_id']})\n";
    echo "   LINE ID: {$testUser['line_openid']}\n\n";
    
    // 2. 查找该用户的一个集运订单
    echo "2. 查找测试订单...\n";
    $testOrder = $db->name('inpack')
        ->where('member_id', $testUser['user_id'])
        ->where('is_delete', 0)
        ->order('id', 'desc')
        ->find();
    
    if (!$testOrder) {
        echo "❌ 该用户没有集运订单\n";
        exit;
    }
    
    echo "✅ 找到测试订单: {$testOrder['order_sn']} (ID: {$testOrder['id']})\n";
    echo "   当前状态: {$testOrder['status']}\n\n";
    
    // 3. 测试入库通知
    echo "3. 测试入库通知 (Inwarehouse)...\n";
    
    $inwarehouseData = [
        'id' => $testOrder['id'],
        'order_sn' => $testOrder['order_sn'],
        'member_id' => $testUser['user_id'],
        'wxapp_id' => $testOrder['wxapp_id'],
        'shop_name' => '测试仓库',
        'express_num' => 'TEST123456',
        'entering_warehouse_time' => date('Y-m-d H:i:s'),
        'weight' => 1.5,
        'length' => 30,
        'width' => 20,
        'height' => 10,
        'usermark' => '测试唛头',
        'remark' => '这是一条测试入库通知'
    ];
    
    try {
        $inwarehouseService = new Inwarehouse();
        $result = $inwarehouseService->send($inwarehouseData);
        
        if ($result) {
            echo "✅ 入库通知发送成功\n";
        } else {
            echo "⚠️ 入库通知发送失败（可能是用户未添加好友或配置问题）\n";
        }
    } catch (\Exception $e) {
        echo "❌ 入库通知发送异常: {$e->getMessage()}\n";
    }
    
    echo "\n";
    
    // 4. 测试发货通知
    echo "4. 测试发货通知 (Sendpack)...\n";
    
    $sendpackData = [
        'id' => $testOrder['id'],
        'order_sn' => $testOrder['order_sn'],
        'member_id' => $testUser['user_id'],
        'wxapp_id' => $testOrder['wxapp_id'],
        't_order_sn' => 'TRACK' . time(),
        'weight' => 2.5,
        't_name' => 'EMS',
        'send_time' => date('Y-m-d H:i:s'),
        'tracking_url' => 'https://example.com/track'
    ];
    
    try {
        $sendpackService = new Sendpack();
        $result = $sendpackService->send($sendpackData);
        
        if ($result) {
            echo "✅ 发货通知发送成功\n";
        } else {
            echo "⚠️ 发货通知发送失败（可能是用户未添加好友或配置问题）\n";
        }
    } catch (\Exception $e) {
        echo "❌ 发货通知发送异常: {$e->getMessage()}\n";
    }
    
    echo "\n";
    
    // 5. 检查日志
    echo "5. 检查最近的通知日志...\n";
    
    $logFile = __DIR__ . '/runtime/log/' . date('Ym') . '/' . date('d') . '.log';
    
    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);
        $lines = explode("\n", $logContent);
        $recentLogs = array_slice($lines, -20); // 最后20行
        
        $lineNotificationLogs = array_filter($recentLogs, function($line) {
            return strpos($line, 'LINE') !== false;
        });
        
        if (!empty($lineNotificationLogs)) {
            echo "✅ 找到 LINE 通知相关日志:\n";
            foreach ($lineNotificationLogs as $log) {
                echo "   " . trim($log) . "\n";
            }
        } else {
            echo "⚠️ 未找到 LINE 通知相关日志\n";
        }
    } else {
        echo "⚠️ 日志文件不存在: {$logFile}\n";
    }
    
    echo "\n=== 测试完成 ===\n\n";
    
    echo "📱 请检查用户的 LINE 应用，确认是否收到通知消息\n";
    echo "📋 如果未收到，请检查:\n";
    echo "   1. 用户是否已添加 LINE OA 为好友\n";
    echo "   2. LINE 配置是否正确（Channel ID, Access Token）\n";
    echo "   3. 模板是否已启用\n";
    echo "   4. 查看日志文件了解详细错误信息\n";
    
} catch (\Exception $e) {
    echo "❌ 测试失败: {$e->getMessage()}\n";
    echo "堆栈: " . $e->getTraceAsString() . "\n";
}
