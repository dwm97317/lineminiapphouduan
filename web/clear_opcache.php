<?php
// 清除 OPcache 缓存

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache 已清除\n";
} else {
    echo "OPcache 未启用\n";
}

// 检查 TrOrder 类
require_once __DIR__ . '/../source/application/store/controller/TrOrder.php';

$reflection = new ReflectionClass('app\store\controller\TrOrder');
$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

echo "\n=== TrOrder 类的所有公共方法 ===\n\n";

$cashforpriceCount = 0;
foreach ($methods as $method) {
    if (stripos($method->getName(), 'cashfor') !== false) {
        echo "找到方法: {$method->getName()} (行 {$method->getStartLine()})\n";
        $cashforpriceCount++;
    }
}

echo "\n总共找到 $cashforpriceCount 个 cashforprice 相关方法\n";

if ($cashforpriceCount > 2) {
    echo "\n警告: 发现重复的方法定义!\n";
}
