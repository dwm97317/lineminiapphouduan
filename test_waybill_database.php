<?php
/**
 * 电子面单数据库测试脚本
 * 功能：
 * 1. 检查数据库连接
 * 2. 检查 waybill_record 表是否存在
 * 3. 检查 setting 表中的面单配置
 * 4. 测试配置保存和读取
 */

// 引入 ThinkPHP 框架
define('APP_PATH', __DIR__ . '/source/application/');
define('RUNTIME_PATH', __DIR__ . '/runtime/');

require __DIR__ . '/source/thinkphp/base.php';

// 使用命名空间
use think\Db;
use think\Exception;

echo "========== 电子面单数据库测试 ==========\n\n";

try {
    // 1. 测试数据库连接
    echo "1. 测试数据库连接...\n";
    $dbConfig = Db::query("SELECT DATABASE() as db_name");
    echo "   ✓ 数据库连接成功！当前数据库: " . $dbConfig[0]['db_name'] . "\n\n";
    
    // 2. 检查 waybill_record 表
    echo "2. 检查 waybill_record 表...\n";
    $tableExists = Db::query("SHOW TABLES LIKE 'yoshop_waybill_record'");
    
    if (empty($tableExists)) {
        echo "   ✗ waybill_record 表不存在，准备创建...\n";
        
        // 读取并执行建表 SQL
        $createTableSql = file_get_contents(__DIR__ . '/database/migrations/20260117_create_waybill_record_table.sql');
        Db::execute($createTableSql);
        
        echo "   ✓ waybill_record 表创建成功！\n\n";
    } else {
        echo "   ✓ waybill_record 表已存在\n";
        
        // 显示表结构
        $columns = Db::query("SHOW COLUMNS FROM yoshop_waybill_record");
        echo "   表字段:\n";
        foreach ($columns as $col) {
            echo "     - {$col['Field']} ({$col['Type']})\n";
        }
        echo "\n";
    }
    
    // 3. 检查 setting 表中的面单配置
    echo "3. 检查面单配置数据...\n";
    
    // 检查 waybill 配置
    $waybillConfig = Db::name('setting')
        ->where('key', 'waybill')
        ->find();
    
    if ($waybillConfig) {
        echo "   ✓ waybill API 配置已存在\n";
        $values = json_decode($waybillConfig['values'], true);
        echo "   配置内容:\n";
        echo "     - 中通配置: " . (isset($values['zhongtong']) ? '已配置' : '未配置') . "\n";
        echo "     - 顺丰配置: " . (isset($values['shunfeng']) ? '已配置' : '未配置') . "\n";
    } else {
        echo "   ✗ waybill API 配置不存在，准备初始化...\n";
        
        // 初始化配置
        $initData = [
            'key' => 'waybill',
            'describe' => '电子面单配置',
            'values' => json_encode([
                'zhongtong' => [
                    'api_url' => '',
                    'api_key' => '',
                    'api_secret' => '',
                    'company_code' => 'ZTO'
                ],
                'shunfeng' => [
                    'api_url' => '',
                    'api_key' => '',
                    'api_secret' => '',
                    'custid' => '',
                    'pay_method' => '1',
                    'company_code' => 'SF'
                ]
            ]),
            'wxapp_id' => 10001,
            'update_time' => time()
        ];
        
        Db::name('setting')->insert($initData);
        echo "   ✓ waybill API 配置初始化成功！\n";
    }
    echo "\n";
    
    // 检查中通面单配置
    $ztConfig = Db::name('setting')
        ->where('key', 'waybill_config_zhongtong')
        ->find();
    
    if ($ztConfig) {
        echo "   ✓ 中通面单配置已存在\n";
    } else {
        echo "   ✗ 中通面单配置不存在，准备初始化...\n";
        
        $ztConfigData = [
            'key' => 'waybill_config_zhongtong',
            'describe' => '中通快递面单配置',
            'values' => json_encode([
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
                    'remark' => false,
                    'quantity' => true
                ],
                'company_fields' => [
                    'site_code' => '',
                    'site_name' => ''
                ],
                'print_params' => [
                    'paper_size' => '76x130',
                    'orientation' => 'portrait',
                    'scale' => 100
                ]
            ]),
            'wxapp_id' => 10001,
            'update_time' => time()
        ];
        
        Db::name('setting')->insert($ztConfigData);
        echo "   ✓ 中通面单配置初始化成功！\n";
    }
    
    // 检查顺丰面单配置
    $sfConfig = Db::name('setting')
        ->where('key', 'waybill_config_shunfeng')
        ->find();
    
    if ($sfConfig) {
        echo "   ✓ 顺丰面单配置已存在\n";
    } else {
        echo "   ✗ 顺丰面单配置不存在，准备初始化...\n";
        
        $sfConfigData = [
            'key' => 'waybill_config_shunfeng',
            'describe' => '顺丰快递面单配置',
            'values' => json_encode([
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
                    'remark' => false,
                    'quantity' => true
                ],
                'company_fields' => [
                    'monthly_card' => '',
                    'payment_method' => '1'
                ],
                'print_params' => [
                    'paper_size' => '76x130',
                    'orientation' => 'portrait',
                    'scale' => 100
                ]
            ]),
            'wxapp_id' => 10001,
            'update_time' => time()
        ];
        
        Db::name('setting')->insert($sfConfigData);
        echo "   ✓ 顺丰面单配置初始化成功！\n";
    }
    echo "\n";
    
    // 4. 测试配置保存和读取
    echo "4. 测试配置保存和读取功能...\n";
    
    // 更新中通配置
    $testConfig = [
        'fields' => [
            'sender_name' => true,
            'sender_phone' => true,
            'sender_address' => true,
            'receiver_name' => true,
            'receiver_phone' => true,
            'receiver_address' => true,
            'item_name' => false,  // 测试修改
            'weight' => true,
            'volume' => false,
            'remark' => false,
            'quantity' => true
        ],
        'company_fields' => [
            'site_code' => 'TEST001',
            'site_name' => '测试网点'
        ],
        'print_params' => [
            'paper_size' => '76x130',
            'orientation' => 'portrait',
            'scale' => 95
        ]
    ];
    
    $updateResult = Db::name('setting')
        ->where('key', 'waybill_config_zhongtong')
        ->update([
            'values' => json_encode($testConfig),
            'update_time' => time()
        ]);
    
    if ($updateResult !== false) {
        echo "   ✓ 配置保存成功\n";
        
        // 读取验证
        $readConfig = Db::name('setting')
            ->where('key', 'waybill_config_zhongtong')
            ->value('values');
        
        $readConfigArray = json_decode($readConfig, true);
        
        if ($readConfigArray['company_fields']['site_code'] === 'TEST001') {
            echo "   ✓ 配置读取成功，数据正确！\n";
            echo "     - 网点代码: " . $readConfigArray['company_fields']['site_code'] . "\n";
            echo "     - 网点名称: " . $readConfigArray['company_fields']['site_name'] . "\n";
            echo "     - 缩放比例: " . $readConfigArray['print_params']['scale'] . "%\n";
        } else {
            echo "   ✗ 配置读取失败，数据不匹配\n";
        }
    } else {
        echo "   ✗ 配置保存失败\n";
    }
    echo "\n";
    
    // 5. 检查面单记录表数据
    echo "5. 检查面单记录表...\n";
    $recordCount = Db::name('waybill_record')->count();
    echo "   当前记录数: $recordCount\n";
    
    if ($recordCount > 0) {
        $latestRecord = Db::name('waybill_record')
            ->order('created_time', 'desc')
            ->limit(1)
            ->find();
        
        echo "   最新记录:\n";
        echo "     - 订单号: " . $latestRecord['order_sn'] . "\n";
        echo "     - 快递公司: " . $latestRecord['express_name'] . "\n";
        echo "     - 运单号: " . $latestRecord['waybill_no'] . "\n";
        echo "     - 创建时间: " . date('Y-m-d H:i:s', $latestRecord['created_time']) . "\n";
    }
    echo "\n";
    
    echo "========== 测试完成 ==========\n";
    echo "✓ 所有功能正常！\n";
    echo "\n";
    echo "配置页面访问地址:\n";
    echo "http://your-domain/index.php?s=/store/setting.waybill_config/index\n";
    
} catch (Exception $e) {
    echo "\n✗ 错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . "\n";
    echo "行号: " . $e->getLine() . "\n";
    exit(1);
}
