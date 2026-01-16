<?php
// 检查 LINE Messaging 配置是否保存成功
$mysqli = new mysqli('103.119.1.84', 'xinsuju', 'cJGzwZTDCLHzWXN4', 'xinsuju', 3306);

if ($mysqli->connect_error) {
    die('连接失败: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8');

echo "<h2>LINE Messaging 配置检查</h2>";

// 查询 line_messaging 配置
$result = $mysqli->query("SELECT * FROM yoshop_setting WHERE `key` = 'line_messaging'");

if ($result && $result->num_rows > 0) {
    echo "<p style='color:green'>✅ 找到 line_messaging 配置记录</p>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<h3>wxapp_id: {$row['wxapp_id']}</h3>";
        echo "<p><strong>describe:</strong> {$row['describe']}</p>";
        echo "<p><strong>update_time:</strong> {$row['update_time']}</p>";
        
        $values = json_decode($row['values'], true);
        echo "<h4>配置内容：</h4>";
        echo "<pre>" . print_r($values, true) . "</pre>";
        
        // 检查关键字段
        echo "<h4>关键字段检查：</h4>";
        echo "<ul>";
        echo "<li>is_enable: " . ($values['is_enable'] ?? '未设置') . "</li>";
        echo "<li>channel_id: " . ($values['channel_id'] ?? '未设置') . "</li>";
        echo "<li>channel_secret: " . ($values['channel_secret'] ?? '未设置') . "</li>";
        echo "<li>access_token: " . (isset($values['access_token']) ? substr($values['access_token'], 0, 20) . '...' : '未设置') . "</li>";
        echo "<li>liff_url: " . ($values['liff_url'] ?? '未设置') . "</li>";
        echo "</ul>";
    }
} else {
    echo "<p style='color:red'>❌ 未找到 line_messaging 配置记录</p>";
    echo "<p>可能原因：</p>";
    echo "<ul>";
    echo "<li>配置从未保存过</li>";
    echo "<li>保存失败</li>";
    echo "<li>数据库表不存在</li>";
    echo "</ul>";
}

// 检查表结构
echo "<h3>yoshop_setting 表结构</h3>";
$result = $mysqli->query("DESCRIBE yoshop_setting");
if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>字段</th><th>类型</th><th>空</th><th>默认值</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$mysqli->close();
?>
