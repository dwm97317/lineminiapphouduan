<?php
/**
 * 添加推荐系统菜单到LINE设置下
 */

$conn = new mysqli('103.119.1.84', 'xinsuju', 'cJGzwZTDCLHzWXN4', 'xinsuju', 3306);
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}
$conn->set_charset('utf8');

echo "【1】查找菜单相关表\n";
$result = $conn->query("SHOW TABLES LIKE '%menu%'");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_array()) {
        echo "  找到表: {$row[0]}\n";
        
        // 查看表结构
        $desc = $conn->query("DESCRIBE {$row[0]}");
        echo "  表结构:\n";
        while ($field = $desc->fetch_assoc()) {
            echo "    - {$field['Field']} ({$field['Type']})\n";
        }
        echo "\n";
    }
} else {
    echo "  未找到菜单表\n\n";
}

echo "【2】查找access相关表\n";
$result = $conn->query("SHOW TABLES LIKE '%access%'");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_array()) {
        echo "  找到表: {$row[0]}\n";
    }
    echo "\n";
}

echo "【3】查找store_user相关表\n";
$result = $conn->query("SHOW TABLES LIKE '%store_user%'");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_array()) {
        echo "  找到表: {$row[0]}\n";
    }
    echo "\n";
}

echo "【4】搜索包含'LINE'或'设置'的菜单记录\n";
$tables = ['yoshop_store_access', 'yoshop_access', 'yoshop_menu', 'yoshop_store_menu'];
foreach ($tables as $table) {
    $result = $conn->query("SELECT * FROM {$table} WHERE name LIKE '%LINE%' OR name LIKE '%设置%' LIMIT 5");
    if ($result && $result->num_rows > 0) {
        echo "  在表 {$table} 中找到:\n";
        while ($row = $result->fetch_assoc()) {
            echo "    ID: {$row['access_id']}, Name: {$row['name']}, Parent: {$row['parent_id']}\n";
        }
        echo "\n";
    }
}

$conn->close();
echo "\n完成！\n";
