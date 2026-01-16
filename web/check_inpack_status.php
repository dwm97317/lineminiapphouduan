<?php
// 查询集运订单的所有状态

$config = [
    'hostname' => '103.119.1.84',
    'database' => 'xinsuju',
    'username' => 'xinsuju',
    'password' => 'cJGzwZTDCLHzWXN4',
    'hostport' => '3306',
    'charset' => 'utf8mb4',
];

try {
    $pdo = new PDO(
        "mysql:host={$config['hostname']};port={$config['hostport']};dbname={$config['database']};charset={$config['charset']}",
        $config['username'],
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== 集运订单表结构 ===\n\n";
    
    // 查看 inpack 表结构
    $columns = $pdo->query("DESCRIBE yoshop_inpack")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        if ($col['Field'] === 'status') {
            echo "status 字段类型: {$col['Type']}\n";
            echo "默认值: {$col['Default']}\n\n";
        }
    }
    
    // 查询所有不同的状态值
    echo "=== 数据库中实际使用的状态值 ===\n\n";
    $statuses = $pdo->query("
        SELECT DISTINCT status, COUNT(*) as count 
        FROM yoshop_inpack 
        GROUP BY status 
        ORDER BY status
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($statuses as $status) {
        echo "状态 {$status['status']}: {$status['count']} 条记录\n";
    }
    
    echo "\n=== 状态值说明 (根据代码推断) ===\n\n";
    $statusMap = [
        -1 => '已取消/问题件',
        1 => '待查验',
        2 => '待支付',
        3 => '已支付',
        4 => '已拣货',
        5 => '已打包',
        6 => '已发货',
        7 => '已收货',
        8 => '已完成'
    ];
    
    foreach ($statusMap as $code => $name) {
        $found = false;
        foreach ($statuses as $status) {
            if ($status['status'] == $code) {
                echo "[$code] $name - 有 {$status['count']} 条记录\n";
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo "[$code] $name - 无记录\n";
        }
    }
    
    // 查询支付状态
    echo "\n=== 支付状态 (is_pay) ===\n\n";
    $payStatuses = $pdo->query("
        SELECT DISTINCT is_pay, COUNT(*) as count 
        FROM yoshop_inpack 
        GROUP BY is_pay 
        ORDER BY is_pay
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($payStatuses as $pay) {
        $payText = $pay['is_pay'] == 1 ? '已支付' : '未支付';
        echo "is_pay = {$pay['is_pay']} ($payText): {$pay['count']} 条记录\n";
    }
    
    // 查询支付类型
    echo "\n=== 支付类型 (is_pay_type) ===\n\n";
    $payTypes = $pdo->query("
        SELECT DISTINCT is_pay_type, COUNT(*) as count 
        FROM yoshop_inpack 
        WHERE is_pay_type IS NOT NULL AND is_pay_type != 0
        GROUP BY is_pay_type 
        ORDER BY is_pay_type
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $payTypeMap = [
        1 => '余额支付',
        2 => '微信支付',
        3 => '支付宝',
        4 => '线下支付',
        5 => '现金支付'
    ];
    
    foreach ($payTypes as $type) {
        $typeName = $payTypeMap[$type['is_pay_type']] ?? '未知';
        echo "is_pay_type = {$type['is_pay_type']} ($typeName): {$type['count']} 条记录\n";
    }
    
} catch (PDOException $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
