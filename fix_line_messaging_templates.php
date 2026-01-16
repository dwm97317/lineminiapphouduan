<?php
/**
 * 修复 LINE 消息通知模板配置
 */

// 数据库配置
$host = '103.119.1.84';
$username = 'xinsuju';
$password = 'cJGzwZTDCLHzWXN4';
$database = 'xinsuju';

// 连接数据库
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

echo "<h2>修复 LINE 消息通知模板</h2>";

// 获取当前配置
$sql = "SELECT * FROM yoshop_setting WHERE `key` = 'line_messaging' AND wxapp_id = 10001";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo "<p style='color:red'>❌ 未找到配置</p>";
    exit;
}

$row = $result->fetch_assoc();
$values = unserialize($row['values']);

echo "<h3>当前模板状态</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>模板</th><th>is_enable</th><th>操作</th></tr>";

$templates = [
    'inwarehouse' => '包裹入库通知',
    'sendpack' => '发货通知',
    'payment' => '支付成功通知',
    'dabaosuccess' => '打包完成通知',
    'payorder' => '付款单生成通知',
    'toshop' => '到仓通知',
    'outapply' => '出库申请通知',
];

$needUpdate = false;

foreach ($templates as $key => $name) {
    $template = $values['templates'][$key] ?? null;
    $isEnable = $template['is_enable'] ?? 'N/A';
    
    echo "<tr>";
    echo "<td>$name ($key)</td>";
    echo "<td>$isEnable</td>";
    
    // 如果没有 is_enable 或者不是 '1'，设置为 '1'
    if (!isset($template['is_enable']) || $template['is_enable'] !== '1') {
        $values['templates'][$key]['is_enable'] = '1';
        echo "<td style='color:orange'>⚠️ 需要修复</td>";
        $needUpdate = true;
    } else {
        echo "<td style='color:green'>✅ 正常</td>";
    }
    
    echo "</tr>";
}

echo "</table>";

if ($needUpdate) {
    echo "<h3>执行修复</h3>";
    
    // 序列化更新后的值
    $serializedValues = serialize($values);
    
    // 更新数据库
    $updateSql = "UPDATE yoshop_setting SET `values` = ?, update_time = ? WHERE `key` = 'line_messaging' AND wxapp_id = 10001";
    $stmt = $conn->prepare($updateSql);
    $updateTime = time();
    $stmt->bind_param('si', $serializedValues, $updateTime);
    
    if ($stmt->execute()) {
        echo "<p style='color:green'>✅ 修复成功！所有模板已启用</p>";
    } else {
        echo "<p style='color:red'>❌ 修复失败: " . $stmt->error . "</p>";
    }
    
    $stmt->close();
} else {
    echo "<p style='color:green'>✅ 所有模板配置正常，无需修复</p>";
}

$conn->close();

echo "<h3>完成</h3>";
echo "<p><a href='http://localhost:8080/index.php?s=/store/setting.line_config/index'>返回配置页面</a></p>";
