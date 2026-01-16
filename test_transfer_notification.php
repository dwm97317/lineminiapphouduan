<?php
/**
 * 测试转单LINE通知
 */

// 数据库配置
$host = '103.119.1.84';
$port = 3306;
$dbname = 'xinsuju';
$username = 'xinsuju';
$password = 'cJGzwZTDCLHzWXN4';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== 转单LINE通知测试 ===\n\n";
    
    $inpack_id = 69407;
    
    // 查询集运单信息
    $stmt = $pdo->prepare("
        SELECT id, order_sn, member_id, wxapp_id,
               t_number, t_name, t_order_sn,
               t2_number, t2_name, t2_order_sn,
               free, pack_free, other_free, weight
        FROM yoshop_inpack 
        WHERE id = ?
    ");
    $stmt->execute([$inpack_id]);
    $pack = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pack) {
        die("错误: 找不到集运单 ID $inpack_id\n");
    }
    
    echo "集运单信息:\n";
    echo "  订单号: {$pack['order_sn']}\n";
    echo "  用户ID: {$pack['member_id']}\n\n";
    
    echo "首次发货信息:\n";
    echo "  承运商: {$pack['t_name']} ({$pack['t_number']})\n";
    echo "  单号: {$pack['t_order_sn']}\n\n";
    
    echo "转单信息:\n";
    echo "  承运商: {$pack['t2_name']} ({$pack['t2_number']})\n";
    echo "  单号: {$pack['t2_order_sn']}\n\n";
    
    // 模拟 Sendpack::send() 的逻辑
    echo "=== 模拟LINE通知数据 ===\n\n";
    
    // 判断是否有转单信息
    $trackingNumber = !empty($pack['t2_order_sn']) 
        ? $pack['t2_order_sn'] 
        : $pack['t_order_sn'];
    
    $carrierName = !empty($pack['t2_name']) 
        ? $pack['t2_name'] 
        : $pack['t_name'];
    
    echo "LINE通知将使用的数据:\n";
    echo "  订单号: {$pack['order_sn']}\n";
    echo "  承运商: $carrierName\n";
    echo "  单号: $trackingNumber\n";
    echo "  重量: {$pack['weight']}\n\n";
    
    if (!empty($pack['t2_order_sn'])) {
        echo "✓ 检测到转单信息，使用转单数据\n";
        echo "  原始承运商: {$pack['t_name']}\n";
        echo "  原始单号: {$pack['t_order_sn']}\n";
        echo "  → 转单承运商: {$pack['t2_name']}\n";
        echo "  → 转单单号: {$pack['t2_order_sn']}\n";
    } else {
        echo "⚠ 未检测到转单信息，使用首次发货数据\n";
    }
    
    echo "\n=== 测试完成 ===\n";
    
} catch (PDOException $e) {
    echo "数据库错误: " . $e->getMessage() . "\n";
}
