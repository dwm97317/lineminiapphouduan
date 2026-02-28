<?php
/**
 * 创建充值申请表
 * 直接在浏览器访问此文件即可创建表
 */

// 数据库配置
$config = [
    'host' => '103.119.1.84',
    'database' => 'xinsuju',
    'username' => 'xinsuju',
    'password' => 'cJGzwZTDCLHzWXN4',
    'port' => '3306',
    'charset' => 'utf8mb4',
];

echo "<h1>创建充值申请表</h1>";
echo "<p><strong>数据库:</strong> {$config['database']}</p>";
echo "<p><strong>表名:</strong> yoshop_recharge_apply</p>";

try {
    // 连接数据库
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<p style='color: green;'>✅ 数据库连接成功</p>";
    
    // 检查表是否已存在
    $stmt = $pdo->query("SHOW TABLES LIKE 'yoshop_recharge_apply'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p style='color: orange;'>⚠️ 表已存在，跳过创建</p>";
    } else {
        // 创建表的SQL
        $sql = "
        CREATE TABLE `yoshop_recharge_apply` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '申请ID',
          `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
          `transfer_date` varchar(20) NOT NULL DEFAULT '' COMMENT '转账日期',
          `transfer_time` varchar(20) NOT NULL DEFAULT '' COMMENT '转账时间',
          `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '充值金额',
          `screenshots` text COMMENT '转账截图(JSON数组)',
          `remarks` varchar(500) DEFAULT '' COMMENT '备注',
          `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态: 0=待审核, 1=已通过, 2=已拒绝',
          `admin_remark` varchar(500) DEFAULT '' COMMENT '管理员备注',
          `reviewed_by` int(11) unsigned DEFAULT NULL COMMENT '审核人ID',
          `reviewed_time` int(11) unsigned DEFAULT NULL COMMENT '审核时间',
          `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
          `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`),
          KEY `status` (`status`),
          KEY `create_time` (`create_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='转账充值申请表';
        ";
        
        // 执行SQL
        $pdo->exec($sql);
        
        echo "<p style='color: green;'>✅ 表创建成功！</p>";
    }
    
    // 显示表结构
    echo "<h2>表结构</h2>";
    $stmt = $pdo->query("DESCRIBE yoshop_recharge_apply");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>字段</th><th>类型</th><th>空</th><th>键</th><th>默认值</th><th>说明</th></tr>";
    
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // 显示索引
    echo "<h2>索引</h2>";
    $stmt = $pdo->query("SHOW INDEX FROM yoshop_recharge_apply");
    $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>索引名</th><th>字段</th><th>唯一</th></tr>";
    
    foreach ($indexes as $idx) {
        echo "<tr>";
        echo "<td>{$idx['Key_name']}</td>";
        echo "<td>{$idx['Column_name']}</td>";
        echo "<td>" . ($idx['Non_unique'] == 0 ? 'YES' : 'NO') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h2>✅ 完成</h2>";
    echo "<p>现在可以测试充值申请API了：</p>";
    echo "<p><a href='test_recharge_apply.php' style='display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>测试充值API</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ 错误: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background: #f5f5f5;
}
h1, h2 {
    color: #333;
    border-bottom: 2px solid #4CAF50;
    padding-bottom: 10px;
}
table {
    background: white;
    width: 100%;
    margin: 20px 0;
}
th {
    background: #4CAF50;
    color: white;
    padding: 10px;
    text-align: left;
}
td {
    padding: 8px;
}
tr:nth-child(even) {
    background: #f9f9f9;
}
</style>
