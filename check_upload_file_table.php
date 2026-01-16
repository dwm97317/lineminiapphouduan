<?php
/**
 * 检查upload_file表结构
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

echo "<h1>检查 yoshop_upload_file 表结构</h1>";

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        [PDO::ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<p style='color: green;'>✅ 数据库连接成功</p>";
    
    // 检查表是否存在
    $stmt = $pdo->query("SHOW TABLES LIKE 'yoshop_upload_file'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ 表 yoshop_upload_file 不存在</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ 表存在</p>";
    
    // 显示表结构
    echo "<h2>表结构</h2>";
    $stmt = $pdo->query("DESCRIBE yoshop_upload_file");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #4CAF50; color: white;'><th>字段名</th><th>类型</th><th>空</th><th>键</th><th>默认值</th><th>Extra</th></tr>";
    
    $fieldNames = [];
    foreach ($columns as $col) {
        $fieldNames[] = $col['Field'];
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
    
    // 显示字段列表
    echo "<h2>字段列表（用于代码）</h2>";
    echo "<pre>";
    echo "可用字段:\n";
    foreach ($fieldNames as $field) {
        echo "  - $field\n";
    }
    echo "</pre>";
    
    // 显示示例数据
    echo "<h2>示例数据（最新5条）</h2>";
    $stmt = $pdo->query("SELECT * FROM yoshop_upload_file ORDER BY file_id DESC LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($rows) {
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%; font-size: 12px;'>";
        echo "<tr style='background: #f0f0f0;'>";
        foreach (array_keys($rows[0]) as $key) {
            echo "<th>$key</th>";
        }
        echo "</tr>";
        
        foreach ($rows as $row) {
            echo "<tr>";
            foreach ($row as $value) {
                $displayValue = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
                echo "<td>" . htmlspecialchars($displayValue) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>暂无数据</p>";
    }
    
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
}
th {
    padding: 10px;
    text-align: left;
}
td {
    padding: 8px;
}
tr:nth-child(even) {
    background: #f9f9f9;
}
pre {
    background: #263238;
    color: #aed581;
    padding: 15px;
    border-radius: 6px;
    overflow-x: auto;
}
</style>
