<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "========== 电子面单数据库初始化 ==========\n\n";

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
    
    echo "步骤 1: 创建 waybill_record 表...\n";
    
    $createTableSql = "
DROP TABLE IF EXISTS `yoshop_waybill_record`;
CREATE TABLE `yoshop_waybill_record` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `inpack_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '集运订单ID',
  `order_sn` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `express_type` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '快递公司类型 (zhongtong/shunfeng)',
  `express_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '快递公司名称',
  `waybill_no` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '运单号',
  `operation_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '操作类型 (1:打印 2:只下单)',
  `operator_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '操作人ID',
  `operator_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '操作人姓名',
  `print_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '打印时间',
  `api_response` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'API响应数据',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序ID',
  `created_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_inpack_id` (`inpack_id`) USING BTREE,
  INDEX `idx_order_sn` (`order_sn`) USING BTREE,
  INDEX `idx_waybill_no` (`waybill_no`) USING BTREE,
  INDEX `idx_created_time` (`created_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='面单打印记录表';
";
    
    $pdo->exec($createTableSql);
    echo "   ✓ waybill_record 表创建成功\n\n";
    
    echo "步骤 2: 初始化 waybill API 配置...\n";
    
    $apiConfigData = [
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
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO yoshop_setting (wxapp_id, `key`, `values`, `describe`, update_time) 
        VALUES (10001, 'waybill', :values, '电子面单配置', :update_time)
        ON DUPLICATE KEY UPDATE 
            `values` = VALUES(`values`),
            update_time = VALUES(update_time)
    ");
    
    $stmt->execute([
        'values' => json_encode($apiConfigData, JSON_UNESCAPED_UNICODE),
        'update_time' => time()
    ]);
    
    echo "   ✓ waybill API 配置初始化成功\n\n";
    
    echo "步骤 3: 初始化中通快递面单配置...\n";
    
    $ztConfigData = [
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
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO yoshop_setting (wxapp_id, `key`, `values`, `describe`, update_time) 
        VALUES (10001, 'waybill_config_zhongtong', :values, '中通快递面单配置', :update_time)
        ON DUPLICATE KEY UPDATE 
            `values` = VALUES(`values`),
            update_time = VALUES(update_time)
    ");
    
    $stmt->execute([
        'values' => json_encode($ztConfigData, JSON_UNESCAPED_UNICODE),
        'update_time' => time()
    ]);
    
    echo "   ✓ 中通快递面单配置初始化成功\n\n";
    
    echo "步骤 4: 初始化顺丰快递面单配置...\n";
    
    $sfConfigData = [
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
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO yoshop_setting (wxapp_id, `key`, `values`, `describe`, update_time) 
        VALUES (10001, 'waybill_config_shunfeng', :values, '顺丰快递面单配置', :update_time)
        ON DUPLICATE KEY UPDATE 
            `values` = VALUES(`values`),
            update_time = VALUES(update_time)
    ");
    
    $stmt->execute([
        'values' => json_encode($sfConfigData, JSON_UNESCAPED_UNICODE),
        'update_time' => time()
    ]);
    
    echo "   ✓ 顺丰快递面单配置初始化成功\n\n";
    
    echo "========== 初始化完成 ==========\n";
    echo "✓ 所有数据库表和配置已成功创建！\n\n";
    echo "现在可以访问配置页面:\n";
    echo "http://your-domain/index.php?s=/store/setting.waybill_config/index\n";
    
} catch (PDOException $e) {
    echo "\n✗ 错误: " . $e->getMessage() . "\n";
    exit(1);
}
