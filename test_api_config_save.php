<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "========== 测试 API 配置保存 ==========\n\n";

$host = '103.119.1.84';
$database = 'xinsuju';
$username = 'xinsuju';
$password = 'cJGzwZTDCLHzWXN4';
$port = '3306';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "✓ 数据库连接成功\n\n";
    
    echo "测试场景: 模拟前端 API 配置保存\n\n";
    
    $apiConfig = [
        'zhongtong' => [
            'api_url' => '',
            'api_key' => '',
            'api_secret' => '',
            'company_code' => 'ZTO'
        ],
        'shunfeng' => [
            'api_url' => 'https://',
            'api_key' => 'THGJH89TNITE',
            'api_secret' => 'mrc4KCKnoJfrTsozdFMPyoHocT6QE2mq',
            'custid' => '123123123',
            'pay_method' => '1',
            'company_code' => 'SF'
        ]
    ];
    
    echo "配置内容:\n";
    echo "  中通 API URL: " . ($apiConfig['zhongtong']['api_url'] ?: '(空)') . "\n";
    echo "  顺丰 API URL: " . $apiConfig['shunfeng']['api_url'] . "\n";
    echo "  顺丰 API Key: " . $apiConfig['shunfeng']['api_key'] . "\n";
    echo "  顺丰月结卡号: " . $apiConfig['shunfeng']['custid'] . "\n\n";
    
    echo "步骤 1: 检查配置记录是否存在...\n";
    
    $stmt = $pdo->query("
        SELECT * FROM yoshop_setting 
        WHERE `key` = 'waybill' AND wxapp_id = 10001
    ");
    
    $existing = $stmt->fetch();
    
    if ($existing) {
        echo "   ✓ 配置记录存在，将执行更新操作\n\n";
        
        echo "步骤 2: 更新配置...\n";
        
        $stmt = $pdo->prepare("
            UPDATE yoshop_setting 
            SET `values` = :values, 
                `describe` = '电子面单配置',
                update_time = :update_time 
            WHERE `key` = 'waybill' AND wxapp_id = 10001
        ");
        
        $result = $stmt->execute([
            'values' => json_encode($apiConfig, JSON_UNESCAPED_UNICODE),
            'update_time' => time()
        ]);
        
    } else {
        echo "   ✗ 配置记录不存在，将执行插入操作\n\n";
        
        echo "步骤 2: 插入新配置...\n";
        
        $stmt = $pdo->prepare("
            INSERT INTO yoshop_setting (`key`, `values`, `describe`, wxapp_id, update_time)
            VALUES ('waybill', :values, '电子面单配置', 10001, :update_time)
        ");
        
        $result = $stmt->execute([
            'values' => json_encode($apiConfig, JSON_UNESCAPED_UNICODE),
            'update_time' => time()
        ]);
    }
    
    if ($result) {
        echo "   ✓ 保存成功\n\n";
        
        echo "步骤 3: 验证保存结果...\n";
        
        $stmt = $pdo->query("
            SELECT * FROM yoshop_setting 
            WHERE `key` = 'waybill' AND wxapp_id = 10001
        ");
        
        $saved = $stmt->fetch();
        $savedConfig = json_decode($saved['values'], true);
        
        $checks = [
            ['顺丰 API Key', $savedConfig['shunfeng']['api_key'] === 'THGJH89TNITE'],
            ['顺丰 API Secret', $savedConfig['shunfeng']['api_secret'] === 'mrc4KCKnoJfrTsozdFMPyoHocT6QE2mq'],
            ['顺丰月结卡号', $savedConfig['shunfeng']['custid'] === '123123123'],
            ['顺丰付款方式', $savedConfig['shunfeng']['pay_method'] === '1'],
            ['中通公司代码', $savedConfig['zhongtong']['company_code'] === 'ZTO'],
        ];
        
        $allPassed = true;
        foreach ($checks as $check) {
            list($name, $passed) = $check;
            if ($passed) {
                echo "   ✓ $name 正确\n";
            } else {
                echo "   ✗ $name 错误\n";
                $allPassed = false;
            }
        }
        
        if ($allPassed) {
            echo "\n✓✓✓ 所有验证通过！✓✓✓\n\n";
            echo "API 配置保存功能正常！\n";
        } else {
            echo "\n⚠ 部分验证未通过\n";
        }
        
    } else {
        echo "   ✗ 保存失败\n";
    }
    
    echo "\n步骤 4: 查看完整配置数据...\n";
    
    $stmt = $pdo->query("
        SELECT `key`, `describe`, update_time 
        FROM yoshop_setting 
        WHERE `key` = 'waybill' AND wxapp_id = 10001
    ");
    
    $config = $stmt->fetch();
    
    if ($config) {
        echo "   键名: {$config['key']}\n";
        echo "   描述: {$config['describe']}\n";
        echo "   更新时间: " . date('Y-m-d H:i:s', $config['update_time']) . "\n";
    }
    
} catch (PDOException $e) {
    echo "\n✗ 数据库错误: " . $e->getMessage() . "\n";
    exit(1);
}
