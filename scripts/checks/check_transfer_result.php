<?php
/**
 * 检查转单结果
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
    
    echo "=== 检查转单结果 ===\n\n";
    
    $inpack_id = 69407;
    
    // 查询集运单信息
    $stmt = $pdo->prepare("
        SELECT id, order_sn, status,
               t_number, t_name, t_order_sn,
               t2_number, t2_name, t2_order_sn,
               transfer, updated_time
        FROM yoshop_inpack 
        WHERE id = ?
    ");
    $stmt->execute([$inpack_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "集运单 ID: {$result['id']}\n";
        echo "订单号: {$result['order_sn']}\n";
        echo "状态: {$result['status']}\n";
        echo "更新时间: " . date('Y-m-d H:i:s', $result['updated_time']) . "\n\n";
        
        echo "首次发货信息:\n";
        echo "  承运商代码: {$result['t_number']}\n";
        echo "  承运商名称: {$result['t_name']}\n";
        echo "  国际单号: {$result['t_order_sn']}\n\n";
        
        echo "转单信息:\n";
        echo "  承运商代码: {$result['t2_number']}\n";
        echo "  承运商名称: {$result['t2_name']}\n";
        echo "  国际单号: {$result['t2_order_sn']}\n";
        echo "  运输方式: " . ($result['transfer'] == 1 ? '外部承运商' : '自有物流') . "\n\n";
        
        // 检查转单是否成功
        if (empty($result['t2_name'])) {
            echo "❌ 问题: t2_name 为空，承运商名称未更新\n";
            echo "\n正在查询承运商代码 100235...\n";
            
            $stmt2 = $pdo->prepare("SELECT express_id, express_name, express_code FROM yoshop_express WHERE express_code = ?");
            $stmt2->execute(['100235']);
            $express = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            if ($express) {
                echo "✓ 找到承运商:\n";
                echo "  ID: {$express['express_id']}\n";
                echo "  名称: {$express['express_name']}\n";
                echo "  代码: {$express['express_code']}\n";
            } else {
                echo "✗ 未找到承运商代码 100235\n";
                echo "\n可用的承运商列表:\n";
                $stmt3 = $pdo->query("SELECT express_code, express_name FROM yoshop_express WHERE type <> 1 LIMIT 10");
                while ($row = $stmt3->fetch(PDO::FETCH_ASSOC)) {
                    echo "  - {$row['express_code']}: {$row['express_name']}\n";
                }
            }
        } else {
            echo "✓ 转单成功！承运商名称已更新为: {$result['t2_name']}\n";
        }
        
        if (empty($result['t2_order_sn'])) {
            echo "❌ 问题: t2_order_sn 为空，国际单号未更新\n";
        } else {
            echo "✓ 国际单号已更新为: {$result['t2_order_sn']}\n";
        }
        
    } else {
        echo "错误: 找不到集运单 ID $inpack_id\n";
    }
    
} catch (PDOException $e) {
    echo "数据库错误: " . $e->getMessage() . "\n";
}
