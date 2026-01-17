<?php
/**
 * 查找LINE设置菜单并添加推荐系统子菜单
 */

$conn = new mysqli('103.119.1.84', 'xinsuju', 'cJGzwZTDCLHzWXN4', 'xinsuju', 3306);
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}
$conn->set_charset('utf8');

echo "【1】查找设置相关的菜单\n";
$result = $conn->query("SELECT * FROM yoshop_store_access WHERE parent_id = 10090 ORDER BY sort");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "  ID: {$row['access_id']}, Name: {$row['name']}, Index: {$row['index']}\n";
    }
    echo "\n";
}

echo "【2】查找LINE相关的菜单\n";
$result = $conn->query("SELECT * FROM yoshop_store_access WHERE name LIKE '%LINE%' OR index LIKE '%line%'");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "  ID: {$row['access_id']}, Name: {$row['name']}, Parent: {$row['parent_id']}, Index: {$row['index']}\n";
        
        // 查找子菜单
        $subResult = $conn->query("SELECT * FROM yoshop_store_access WHERE parent_id = {$row['access_id']}");
        if ($subResult && $subResult->num_rows > 0) {
            echo "    子菜单:\n";
            while ($subRow = $subResult->fetch_assoc()) {
                echo "      - ID: {$subRow['access_id']}, Name: {$subRow['name']}, Index: {$subRow['index']}\n";
            }
        }
    }
    echo "\n";
} else {
    echo "  未找到LINE相关菜单\n\n";
}

echo "【3】查看yoshop_store_access表的完整结构\n";
$result = $conn->query("DESCRIBE yoshop_store_access");
while ($field = $result->fetch_assoc()) {
    echo "  {$field['Field']} - {$field['Type']} - {$field['Null']} - {$field['Default']}\n";
}

$conn->close();
echo "\n完成！\n";
