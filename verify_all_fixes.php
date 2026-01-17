<?php
/**
 * 验证所有修复是否生效
 */

echo "=== 推荐奖励系统配置 - 修复验证 ===\n\n";

$checks = [];

// 1. 检查控制器文件
echo "1. 检查控制器分组逻辑修复...\n";
$controllerFile = __DIR__ . '/source/application/store/controller/setting/Referral.php';
$controllerContent = file_get_contents($controllerFile);

if (strpos($controllerContent, "is_array(\$task['user_type'])") !== false) {
    echo "   ✓ 控制器已修复（包含数组处理逻辑）\n";
    $checks['controller'] = true;
} else {
    echo "   ✗ 控制器未修复（缺少数组处理逻辑）\n";
    $checks['controller'] = false;
}

// 2. 检查视图文件
echo "\n2. 检查视图复选框样式...\n";
$viewFile = __DIR__ . '/source/application/store/view/setting/referral/config.php';
$viewContent = file_get_contents($viewFile);

if (strpos($viewContent, 'data-am-ucheck') !== false) {
    echo "   ✓ 视图已添加Amazeui样式\n";
    $checks['view'] = true;
} else {
    echo "   ✗ 视图未添加Amazeui样式\n";
    $checks['view'] = false;
}

// 3. 检查数据库连接
echo "\n3. 检查数据库连接...\n";
$config = [
    'host' => '103.119.1.84',
    'database' => 'xinsuju',
    'username' => 'xinsuju',
    'password' => 'cJGzwZTDCLHzWXN4',
    'port' => '3306',
    'charset' => 'utf8',
];

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}",
        $config['username'],
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✓ 数据库连接成功\n";
    $checks['database'] = true;
    
    // 4. 检查任务配置数据
    echo "\n4. 检查任务配置数据...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM yoshop_referral_task_config WHERE wxapp_id = 10001");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] >= 3) {
        echo "   ✓ 任务配置数据存在（{$result['count']}条）\n";
        $checks['data'] = true;
        
        // 显示任务详情
        $stmt = $pdo->query("SELECT id, config_name, user_type, is_enabled, is_required FROM yoshop_referral_task_config WHERE wxapp_id = 10001 ORDER BY user_type, id");
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n   任务列表:\n";
        foreach ($tasks as $task) {
            $userTypeText = $task['user_type'] == 1 ? '推荐人' : '被推荐人';
            $enabledText = $task['is_enabled'] ? '启用' : '禁用';
            $requiredText = $task['is_required'] ? '必须' : '可选';
            echo "   - ID {$task['id']}: {$task['config_name']} ({$userTypeText}, {$enabledText}, {$requiredText})\n";
        }
    } else {
        echo "   ✗ 任务配置数据不足\n";
        $checks['data'] = false;
    }
    
} catch (PDOException $e) {
    echo "   ✗ 数据库连接失败: " . $e->getMessage() . "\n";
    $checks['database'] = false;
    $checks['data'] = false;
}

// 5. 检查模板缓存
echo "\n5. 检查模板缓存...\n";
$cacheDir = __DIR__ . '/runtime/temp';
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '/*');
    if (empty($files)) {
        echo "   ✓ 模板缓存已清空\n";
        $checks['cache'] = true;
    } else {
        echo "   ⚠ 模板缓存存在 (" . count($files) . " 个文件)\n";
        echo "   建议运行: php clear_all_cache.php\n";
        $checks['cache'] = false;
    }
} else {
    echo "   ✓ 模板缓存目录不存在（正常）\n";
    $checks['cache'] = true;
}

// 总结
echo "\n" . str_repeat("=", 50) . "\n";
echo "验证总结\n";
echo str_repeat("=", 50) . "\n\n";

$allPassed = true;
foreach ($checks as $name => $passed) {
    $status = $passed ? '✓ 通过' : '✗ 失败';
    $nameMap = [
        'controller' => '控制器修复',
        'view' => '视图修复',
        'database' => '数据库连接',
        'data' => '任务配置数据',
        'cache' => '模板缓存',
    ];
    echo sprintf("%-20s %s\n", $nameMap[$name] . ':', $status);
    if (!$passed) {
        $allPassed = false;
    }
}

echo "\n";

if ($allPassed) {
    echo "✓✓✓ 所有检查通过！系统已准备就绪 ✓✓✓\n\n";
    echo "下一步:\n";
    echo "1. 访问配置页面: http://localhost:8080/store/setting.referral/config\n";
    echo "2. 验证任务分组是否正确\n";
    echo "3. 测试表单提交功能\n";
} else {
    echo "✗✗✗ 部分检查未通过，请修复后重试 ✗✗✗\n\n";
    echo "修复建议:\n";
    if (!$checks['controller']) {
        echo "- 重新应用控制器修复\n";
    }
    if (!$checks['view']) {
        echo "- 重新应用视图修复\n";
    }
    if (!$checks['database']) {
        echo "- 检查数据库连接配置\n";
    }
    if (!$checks['data']) {
        echo "- 检查任务配置数据是否存在\n";
    }
    if (!$checks['cache']) {
        echo "- 运行 php clear_all_cache.php 清除缓存\n";
    }
}

echo "\n";
