<?php
/**
 * 详细测试推荐奖励记录页面
 */

$url = 'http://localhost:8080/index.php?s=/store/referral/rewards';

echo "测试推荐奖励记录页面\n";
echo "===================\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=test123'); // 模拟登录

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP状态码: {$httpCode}\n\n";

if ($httpCode == 200) {
    // 保存完整响应到文件
    file_put_contents('rewards_page_response.html', $response);
    echo "✓ 响应已保存到 rewards_page_response.html\n\n";
    
    // 分析响应内容
    $length = strlen($response);
    echo "响应长度: {$length} 字节\n\n";
    
    // 检查是否是登录页面
    if (strpos($response, 'login') !== false || strpos($response, '登录') !== false) {
        echo "⚠ 可能被重定向到登录页面\n";
        echo "需要先登录后台才能访问\n\n";
    }
    
    // 检查是否有错误信息
    if (strpos($response, 'error') !== false || strpos($response, '错误') !== false) {
        echo "⚠ 响应中包含错误信息\n";
        
        // 提取错误信息
        if (preg_match('/<div[^>]*error[^>]*>(.*?)<\/div>/is', $response, $matches)) {
            echo "错误内容: " . strip_tags($matches[1]) . "\n";
        }
    }
    
    // 显示前500个字符
    echo "\n前500个字符:\n";
    echo "---\n";
    echo substr($response, 0, 500);
    echo "\n---\n";
    
} else {
    echo "✗ 请求失败\n";
}
