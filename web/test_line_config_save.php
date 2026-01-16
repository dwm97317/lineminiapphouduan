<?php
// 测试 LINE 配置保存
require_once __DIR__ . '/../source/application/database.php';

// 数据库配置
$config = [
    'host' => '103.119.1.84',
    'database' => 'xinsuju',
    'username' => 'xinsuju',
    'password' => 'cJGzwZTDCLHzWXN4',
    'port' => '3306',
    'charset' => 'utf8',
];

// 连接数据库
$mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database'], $config['port']);

if ($mysqli->connect_error) {
    die('数据库连接失败: ' . $mysqli->connect_error);
}

$mysqli->set_charset($config['charset']);

echo "<h2>LINE 配置保存测试</h2>";

// 1. 检查 yoshop_setting 表结构
echo "<h3>1. 检查 yoshop_setting 表结构</h3>";
$result = $mysqli->query("DESCRIBE yoshop_setting");
if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>字段</th><th>类型</th><th>空</th><th>键</th><th>默认值</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>查询失败: " . $mysqli->error . "</p>";
}

// 2. 查询现有的 LINE 相关配置
echo "<h3>2. 查询现有的 LINE 相关配置</h3>";
$result = $mysqli->query("SELECT * FROM yoshop_setting WHERE `key` IN ('line_config', 'line_messaging', 'line_pay')");
if ($result) {
    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>key</th><th>describe</th><th>values (前100字符)</th><th>wxapp_id</th><th>update_time</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['key']}</td>";
            echo "<td>{$row['describe']}</td>";
            echo "<td>" . htmlspecialchars(substr($row['values'], 0, 100)) . "...</td>";
            echo "<td>{$row['wxapp_id']}</td>";
            echo "<td>{$row['update_time']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:orange'>没有找到 LINE 相关配置记录</p>";
    }
} else {
    echo "<p style='color:red'>查询失败: " . $mysqli->error . "</p>";
}

// 3. 测试插入/更新 line_messaging 配置
echo "<h3>3. 测试插入/更新 line_messaging 配置</h3>";

$testData = [
    'is_enable' => '1',
    'channel_id' => 'TEST_CHANNEL_ID',
    'channel_secret' => 'TEST_CHANNEL_SECRET',
    'access_token' => 'TEST_ACCESS_TOKEN',
    'liff_url' => 'https://liff.line.me/test',
    'templates' => [
        'inwarehouse' => [
            'is_enable' => '1',
            'name' => '包裹入库通知',
            'alt_text' => '📦 包裹入库通知',
            'priority' => 'high',
            'send_delay' => 0,
            'flex_template' => [],
            'variables' => []
        ]
    ]
];

$valuesJson = json_encode($testData, JSON_UNESCAPED_UNICODE);
$wxapp_id = 10001; // 测试用的 wxapp_id

// 检查是否已存在
$checkSql = "SELECT * FROM yoshop_setting WHERE `key` = 'line_messaging' AND wxapp_id = ?";
$stmt = $mysqli->prepare($checkSql);
$stmt->bind_param('i', $wxapp_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // 更新
    echo "<p>记录已存在，执行更新操作...</p>";
    $updateSql = "UPDATE yoshop_setting SET `values` = ?, `describe` = 'LINE Messaging API 配置', update_time = NOW() WHERE `key` = 'line_messaging' AND wxapp_id = ?";
    $stmt = $mysqli->prepare($updateSql);
    $stmt->bind_param('si', $valuesJson, $wxapp_id);
    
    if ($stmt->execute()) {
        echo "<p style='color:green'>✅ 更新成功！受影响行数: " . $stmt->affected_rows . "</p>";
    } else {
        echo "<p style='color:red'>❌ 更新失败: " . $stmt->error . "</p>";
    }
} else {
    // 插入
    echo "<p>记录不存在，执行插入操作...</p>";
    $insertSql = "INSERT INTO yoshop_setting (`key`, `describe`, `values`, wxapp_id, update_time) VALUES ('line_messaging', 'LINE Messaging API 配置', ?, ?, NOW())";
    $stmt = $mysqli->prepare($insertSql);
    $stmt->bind_param('si', $valuesJson, $wxapp_id);
    
    if ($stmt->execute()) {
        echo "<p style='color:green'>✅ 插入成功！插入ID: " . $mysqli->insert_id . "</p>";
    } else {
        echo "<p style='color:red'>❌ 插入失败: " . $stmt->error . "</p>";
    }
}

// 4. 验证保存结果
echo "<h3>4. 验证保存结果</h3>";
$result = $mysqli->query("SELECT * FROM yoshop_setting WHERE `key` = 'line_messaging' AND wxapp_id = $wxapp_id");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "<p><strong>key:</strong> {$row['key']}</p>";
    echo "<p><strong>describe:</strong> {$row['describe']}</p>";
    echo "<p><strong>wxapp_id:</strong> {$row['wxapp_id']}</p>";
    echo "<p><strong>update_time:</strong> {$row['update_time']}</p>";
    echo "<p><strong>values:</strong></p>";
    echo "<pre>" . htmlspecialchars(json_encode(json_decode($row['values']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
} else {
    echo "<p style='color:red'>验证失败：无法读取刚保存的数据</p>";
}

// 5. 检查缓存
echo "<h3>5. 缓存说明</h3>";
echo "<p>ThinkPHP 使用缓存存储配置，缓存键名: <code>setting_{wxapp_id}</code></p>";
echo "<p>如果配置无法保存，可能需要清除缓存：</p>";
echo "<pre>Cache::rm('setting_' . \$wxapp_id);</pre>";
echo "<p>或者清除所有缓存：</p>";
echo "<pre>Cache::clear();</pre>";

$mysqli->close();
?>
