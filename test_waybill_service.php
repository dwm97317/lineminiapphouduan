<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('APP_PATH', __DIR__ . '/source/application/');
define('RUNTIME_PATH', __DIR__ . '/runtime/');

require __DIR__ . '/source/thinkphp/base.php';

use app\common\service\WaybillConfigService;
use app\common\model\Setting as SettingModel;

echo "========== WaybillConfigService 测试 ==========\n\n";

try {
    SettingModel::$wxapp_id = 10001;
    
    $service = new WaybillConfigService(10001);
    
    echo "1. 测试获取中通配置...\n";
    $ztConfig = $service->getConfig('zhongtong');
    echo "   ✓ 获取成功\n";
    echo "   当前网点代码: " . ($ztConfig['company_fields']['site_code'] ?: '(未设置)') . "\n";
    echo "   当前网点名称: " . ($ztConfig['company_fields']['site_name'] ?: '(未设置)') . "\n\n";
    
    echo "2. 测试保存中通配置...\n";
    $newConfig = [
        'fields' => [
            'sender_name' => true,
            'sender_phone' => true,
            'sender_address' => true,
            'receiver_name' => true,
            'receiver_phone' => true,
            'receiver_address' => true,
            'item_name' => true,
            'weight' => true,
            'volume' => false,
            'remark' => true,
            'quantity' => true
        ],
        'company_fields' => [
            'site_code' => 'ZT001',
            'site_name' => '中通测试网点（通过Service保存）'
        ],
        'print_params' => [
            'paper_size' => '76x130',
            'orientation' => 'portrait',
            'scale' => 90
        ]
    ];
    
    $result = $service->saveConfig('zhongtong', $newConfig);
    
    if ($result) {
        echo "   ✓ 保存成功\n\n";
        
        echo "3. 验证保存结果...\n";
        $savedConfig = $service->getConfig('zhongtong');
        
        if ($savedConfig['company_fields']['site_code'] === 'ZT001') {
            echo "   ✓ 验证成功\n";
            echo "   网点代码: " . $savedConfig['company_fields']['site_code'] . "\n";
            echo "   网点名称: " . $savedConfig['company_fields']['site_name'] . "\n";
            echo "   缩放比例: " . $savedConfig['print_params']['scale'] . "%\n";
            echo "   备注字段: " . ($savedConfig['fields']['remark'] ? '显示' : '隐藏') . "\n";
        } else {
            echo "   ✗ 验证失败\n";
        }
    } else {
        echo "   ✗ 保存失败\n";
    }
    
    echo "\n4. 测试获取字段定义...\n";
    $fields = $service->getFieldDefinitions('zhongtong');
    echo "   ✓ 获取成功\n";
    echo "   基础字段数: " . count($fields['fields']) . "\n";
    echo "   打印参数数: " . count($fields['print_params']) . "\n";
    echo "   公司字段数: " . count($fields['company_fields']) . "\n\n";
    
    echo "5. 测试顺丰配置...\n";
    $sfConfig = [
        'fields' => [
            'sender_name' => true,
            'sender_phone' => true,
            'sender_address' => true,
            'receiver_name' => true,
            'receiver_phone' => true,
            'receiver_address' => true,
            'item_name' => true,
            'weight' => true,
            'volume' => true,
            'remark' => false,
            'quantity' => true
        ],
        'company_fields' => [
            'monthly_card' => 'SF12345678',
            'payment_method' => '1'
        ],
        'print_params' => [
            'paper_size' => '76x130',
            'orientation' => 'portrait',
            'scale' => 100
        ]
    ];
    
    $result = $service->saveConfig('shunfeng', $sfConfig);
    
    if ($result) {
        echo "   ✓ 顺丰配置保存成功\n";
        
        $savedSfConfig = $service->getConfig('shunfeng');
        echo "   月结卡号: " . $savedSfConfig['company_fields']['monthly_card'] . "\n";
        echo "   体积字段: " . ($savedSfConfig['fields']['volume'] ? '显示' : '隐藏') . "\n";
    } else {
        echo "   ✗ 顺丰配置保存失败\n";
    }
    
    echo "\n========== 测试完成 ==========\n";
    echo "✓ WaybillConfigService 所有功能正常！\n\n";
    
} catch (Exception $e) {
    echo "\n✗ 错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . " (第 " . $e->getLine() . " 行)\n";
    echo "\n堆栈跟踪:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
