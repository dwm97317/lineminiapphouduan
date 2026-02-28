<?php
/**
 * 检查 category 表结构
 */

require __DIR__ . '/source/application/common.php';

use think\Db;

echo "<h2>检查 Category 表结构</h2>";
echo "<hr>";

try {
    // 获取表结构
    $columns = Db::query("SHOW COLUMNS FROM yoshop_category");
    
    echo "<h3>表字段列表：</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #4CAF50; color: white;'>";
    echo "<th>字段名</th><th>类型</th><th>允许NULL</th><th>键</th><th>默认值</th><th>额外</th>";
    echo "</tr>";
    
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td><strong>" . $col['Field'] . "</strong></td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . ($col['Extra'] ?? '') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // 获取一些示例数据
    echo "<h3>示例数据（前5条）：</h3>";
    $categories = Db::name('category')->limit(5)->select();
    
    echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
    print_r($categories);
    echo "</pre>";
    
    // 统计总数
    $count = Db::name('category')->count();
    echo "<p><strong>总记录数：</strong> $count</p>";
    
} catch (\Exception $e) {
    echo "<p style='color: red;'><strong>错误：</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>检查完成时间: " . date('Y-m-d H:i:s') . "</em></p>";
?>
