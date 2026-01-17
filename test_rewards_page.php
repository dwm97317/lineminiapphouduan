<?php
/**
 * 测试推荐奖励记录页面
 * 访问: http://localhost:8080/test_rewards_page.php
 */

// 模拟访问后台奖励页面
$url = 'http://localhost:8080/index.php?s=/store/referral/rewards';

echo "正在测试推荐奖励记录页面...\n\n";
echo "访问URL: {$url}\n\n";

// 使用curl测试
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP状态码: {$httpCode}\n";

if ($error) {
    echo "错误: {$error}\n";
} else {
    if ($httpCode == 200) {
        echo "✓ 页面加载成功!\n\n";
        
        // 检查是否有PHP错误
        if (strpos($response, 'ThrowableError') !== false || 
            strpos($response, 'ErrorException') !== false ||
            strpos($response, 'syntax error') !== false) {
            echo "✗ 页面包含PHP错误\n";
            
            // 提取错误信息
            if (preg_match('/line (\d+)/', $response, $matches)) {
                echo "错误行号: {$matches[1]}\n";
            }
            if (preg_match('/(syntax error[^<]+)/', $response, $matches)) {
                echo "错误信息: {$matches[1]}\n";
            }
        } else {
            echo "✓ 页面无PHP错误\n";
            
            // 检查关键元素
            $checks = [
                '推荐奖励记录' => strpos($response, '推荐奖励记录') !== false,
                '搜索表单' => strpos($response, 'name="user_id"') !== false,
                '统计信息' => strpos($response, 'total_cash') !== false || strpos($response, '现金奖励总额') !== false,
                '数据表格' => strpos($response, '<table') !== false,
                '分页' => strpos($response, 'pagination') !== false || strpos($response, '总记录') !== false,
            ];
            
            echo "\n关键元素检查:\n";
            foreach ($checks as $name => $result) {
                echo ($result ? '✓' : '✗') . " {$name}\n";
            }
        }
    } else {
        echo "✗ 页面加载失败\n";
        echo "响应内容:\n";
        echo substr($response, 0, 500) . "...\n";
    }
}

echo "\n完成!\n";
