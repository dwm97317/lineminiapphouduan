<?php
/**
 * 检查certificate表结构
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

echo "<h1>Certificate表结构检查</h1>";

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<p style='color: green;'>✅ 数据库连接成功</p>";
    
    // 检查 yoshop_certificate 表结构
    echo "<h2>yoshop_certificate 表结构</h2>";
    $stmt = $pdo->query("DESCRIBE yoshop_certificate");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #4CAF50; color: white;'><th>字段名</th><th>类型</th><th>空</th><th>键</th><th>默认值</th><th>Extra</th></tr>";
    
    foreach ($columns as $col) {
        $highlight = ($col['Field'] == 'create_time' || $col['Field'] == 'update_time') ? 'background: yellow;' : '';
        echo "<tr style='$highlight'>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td><strong>{$col['Type']}</strong></td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 检查 yoshop_certificate_image 表结构
    echo "<h2>yoshop_certificate_image 表结构</h2>";
    $stmt = $pdo->query("DESCRIBE yoshop_certificate_image");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #4CAF50; color: white;'><th>字段名</th><th>类型</th><th>空</th><th>键</th><th>默认值</th><th>Extra</th></tr>";
    
    foreach ($columns as $col) {
        $highlight = ($col['Field'] == 'create_time' || $col['Field'] == 'update_time') ? 'background: yellow;' : '';
        echo "<tr style='$highlight'>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td><strong>{$col['Type']}</strong></td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ 错误: " . $e->getMessage() . "</p>";
}
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 1400px;
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
    margin: 20px 0;
    font-size: 13px;
}
th, td {
    padding: 10px;
    text-align: left;
}
tr:nth-child(even) {
    background: #f9f9f9;
}
</style>
