<?php
/**
 * 测试产品类别 API
 */

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>测试产品类别 API</h2>";
echo "<hr>";

// 测试 URL
$baseUrl = 'http://localhost:8080/index.php?s=api/category/lists&wxapp_id=10001';

echo "<h3>1. 测试 API 请求</h3>";
echo "<p><strong>URL:</strong> $baseUrl</p>";

// 使用 cURL 测试
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP 状态码:</strong> $httpCode</p>";

if ($error) {
    echo "<p style='color: red;'><strong>错误:</strong> $error</p>";
} else {
    echo "<h3>2. API 响应</h3>";
    echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
    
    $data = json_decode($response, true);
    if ($data) {
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        echo "</pre>";
        
        if (isset($data['code']) && $data['code'] == 1) {
            echo "<p style='color: green;'><strong>✅ API 调用成功！</strong></p>";
            
            if (isset($data['data']['data'])) {
                $categories = $data['data']['data'];
                echo "<h3>3. 产品类别列表 (" . count($categories) . " 个)</h3>";
                echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr style='background: #4CAF50; color: white;'>";
                echo "<th>ID</th><th>名称</th><th>排序</th><th>图片</th>";
                echo "</tr>";
                
                foreach ($categories as $cat) {
                    echo "<tr>";
                    echo "<td>" . ($cat['category_id'] ?? 'N/A') . "</td>";
                    echo "<td>" . ($cat['name'] ?? 'N/A') . "</td>";
                    echo "<td>" . ($cat['sort'] ?? 'N/A') . "</td>";
                    echo "<td>" . (isset($cat['image']['file_path']) ? '有图片' : '无图片') . "</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p style='color: orange;'><strong>⚠️ 没有找到类别数据</strong></p>";
            }
        } else {
            echo "<p style='color: red;'><strong>❌ API 返回错误</strong></p>";
            echo "<p>错误信息: " . ($data['msg'] ?? '未知错误') . "</p>";
        }
    } else {
        echo "无法解析 JSON 响应";
        echo "\n\n原始响应:\n";
        echo htmlspecialchars($response);
        echo "</pre>";
    }
}

echo "<hr>";
echo "<h3>4. 前端调用示例</h3>";
echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
echo "// JavaScript 调用示例
const response = await fetch('$baseUrl');
const data = await response.json();

if (data.code === 1 && data.data?.data) {
  const categories = data.data.data;
  console.log('找到', categories.length, '个类别');
  categories.forEach(cat => {
    console.log(cat.category_id, cat.name);
  });
}";
echo "</pre>";

echo "<hr>";
echo "<p><em>测试完成时间: " . date('Y-m-d H:i:s') . "</em></p>";
?>
