<?php
// 测试 CashforPrice 方法

// 清除 opcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache 已清除\n\n";
}

// 读取文件内容
$file = __DIR__ . '/../source/application/store/controller/TrOrder.php';
$content = file_get_contents($file);

// 搜索所有 cashforprice 方法 (不区分大小写)
preg_match_all('/function\s+(cashforprice|CashforPrice)\s*\(/i', $content, $matches, PREG_OFFSET_CAPTURE);

echo "=== 搜索结果 ===\n\n";
echo "找到 " . count($matches[0]) . " 个方法定义:\n\n";

foreach ($matches[0] as $index => $match) {
    $position = $match[1];
    $methodName = $matches[1][$index][0];
    
    // 计算行号
    $lineNum = substr_count(substr($content, 0, $position), "\n") + 1;
    
    // 获取上下文
    $start = max(0, $position - 50);
    $end = min(strlen($content), $position + 100);
    $context = substr($content, $start, $end - $start);
    
    echo "方法 #" . ($index + 1) . ": $methodName\n";
    echo "  行号: $lineNum\n";
    echo "  位置: $position\n";
    echo "  上下文:\n";
    echo "  " . str_replace("\n", "\n  ", trim($context)) . "\n\n";
}

// 检查是否有重复的大写方法名
$upperCount = 0;
$lowerCount = 0;
foreach ($matches[1] as $match) {
    if ($match[0] === 'CashforPrice') {
        $upperCount++;
    } else {
        $lowerCount++;
    }
}

echo "=== 统计 ===\n\n";
echo "cashforprice (小写): $lowerCount 个\n";
echo "CashforPrice (大写): $upperCount 个\n";

if ($upperCount > 1) {
    echo "\n警告: 发现 $upperCount 个 CashforPrice 方法,存在重复定义!\n";
} else {
    echo "\n正常: 没有重复定义\n";
}
