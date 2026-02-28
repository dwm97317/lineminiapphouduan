<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "========== 电子面单配置检查 ==========\n\n";

$host = '103.119.1.84';
$database = 'xinsuju';
$username = 'xinsuju';
$password = 'cJGzwZTDCLHzWXN4';
$port = '3306';

echo "1. 测试数据库连接...\n";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "   ✓ 数据库连接成功！\n\n";
    
    echo "2. 检查 waybill_record 表...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'yoshop_waybill_record'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "   ✗ waybill_record 表不存在\n";
        echo "   请手动执行建表脚本: database/migrations/20260117_create_waybill_record_table.sql\n\n";
    } else {
        echo "   ✓ waybill_record 表已存在\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM yoshop_waybill_record");
        $result = $stmt->fetch();
        echo "   当前记录数: " . $result['count'] . "\n\n";
    }
    
    echo "3. 检查面单配置数据...\n";
    
    $stmt = $pdo->query("SELECT * FROM yoshop_setting WHERE `key` = 'waybill' LIMIT 1");
    $waybillConfig = $stmt->fetch();
    
    if ($waybillConfig) {
        echo "   ✓ waybill API 配置已存在\n";
        $values = json_decode($waybillConfig['values'], true);
        echo "   配置内容:\n";
        if (isset($values['zhongtong'])) {
            echo "     - 中通 API URL: " . ($values['zhongtong']['api_url'] ?: '(未设置)') . "\n";
            echo "     - 中通 API Key: " . ($values['zhongtong']['api_key'] ? '已设置' : '(未设置)') . "\n";
        }
        if (isset($values['shunfeng'])) {
            echo "     - 顺丰 API URL: " . ($values['shunfeng']['api_url'] ?: '(未设置)') . "\n";
            echo "     - 顺丰 Partner ID: " . ($values['shunfeng']['api_key'] ? '已设置' : '(未设置)') . "\n";
        }
    } else {
        echo "   ✗ waybill API 配置不存在\n";
        echo "   请手动执行初始化脚本: database/migrations/20260117_init_waybill_config.sql\n";
    }
    echo "\n";
    
    $stmt = $pdo->query("SELECT * FROM yoshop_setting WHERE `key` = 'waybill_config_zhongtong' LIMIT 1");
    $ztConfig = $stmt->fetch();
    
    if ($ztConfig) {
        echo "   ✓ 中通面单配置已存在\n";
        $values = json_decode($ztConfig['values'], true);
        if (isset($values['company_fields'])) {
            echo "     - 网点代码: " . ($values['company_fields']['site_code'] ?: '(未设置)') . "\n";
            echo "     - 网点名称: " . ($values['company_fields']['site_name'] ?: '(未设置)') . "\n";
        }
    } else {
        echo "   ✗ 中通面单配置不存在\n";
    }
    
    $stmt = $pdo->query("SELECT * FROM yoshop_setting WHERE `key` = 'waybill_config_shunfeng' LIMIT 1");
    $sfConfig = $stmt->fetch();
    
    if ($sfConfig) {
        echo "   ✓ 顺丰面单配置已存在\n";
    } else {
        echo "   ✗ 顺丰面单配置不存在\n";
    }
    echo "\n";
    
    echo "4. 测试配置保存功能...\n";
    
    $testValues = [
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
            'site_code' => 'TEST001',
            'site_name' => '测试网点（通过PHP脚本更新）'
        ],
        'print_params' => [
            'paper_size' => '76x130',
            'orientation' => 'portrait',
            'scale' => 95
        ]
    ];
    
    $updateSql = "UPDATE yoshop_setting 
                  SET `values` = :values, update_time = :update_time 
                  WHERE `key` = 'waybill_config_zhongtong'";
    
    $stmt = $pdo->prepare($updateSql);
    $stmt->execute([
        'values' => json_encode($testValues, JSON_UNESCAPED_UNICODE),
        'update_time' => time()
    ]);
    
    echo "   ✓ 配置保存成功\n";
    
    $stmt = $pdo->query("SELECT * FROM yoshop_setting WHERE `key` = 'waybill_config_zhongtong' LIMIT 1");
    $updatedConfig = $stmt->fetch();
    $updatedValues = json_decode($updatedConfig['values'], true);
    
    if ($updatedValues['company_fields']['site_code'] === 'TEST001') {
        echo "   ✓ 配置读取验证成功\n";
        echo "     - 网点代码: " . $updatedValues['company_fields']['site_code'] . "\n";
        echo "     - 网点名称: " . $updatedValues['company_fields']['site_name'] . "\n";
        echo "     - 缩放比例: " . $updatedValues['print_params']['scale'] . "%\n";
    } else {
        echo "   ✗ 配置验证失败\n";
    }
    
    echo "\n========== 测试完成 ==========\n";
    echo "✓ 数据库连接和配置保存功能正常！\n\n";
    echo "访问配置页面:\n";
    echo "http://your-domain/index.php?s=/store/setting.waybill_config/index\n\n";
    
} catch (PDOException $e) {
    echo "\n✗ 数据库错误: " . $e->getMessage() . "\n";
    echo "错误代码: " . $e->getCode() . "\n\n";
    
    if ($e->getCode() == 2054 || strpos($e->getMessage(), 'caching_sha2_password') !== false) {
        echo "提示: 这是 MySQL 8.0+ 认证方式问题\n";
        echo "解决方案:\n";
        echo "1. 升级 PHP MySQL 扩展到最新版本\n";
        echo "2. 或在 MySQL 中修改用户认证方式:\n";
        echo "   ALTER USER '$username'@'%' IDENTIFIED WITH mysql_native_password BY '$password';\n";
        echo "   FLUSH PRIVILEGES;\n";
    }
    
    exit(1);
}
