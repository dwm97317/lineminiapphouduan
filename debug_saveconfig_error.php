<?php
/**
 * 调试 saveconfig 错误
 * 捕获完整的错误堆栈
 */

// 启用错误显示
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 加载 ThinkPHP
define('APP_PATH', __DIR__ . '/source/application/');
define('RUNTIME_PATH', __DIR__ . '/source/runtime/');
define('ROOT_PATH', __DIR__ . '/');
define('EXTEND_PATH', __DIR__ . '/source/extend/');
define('VENDOR_PATH', __DIR__ . '/source/vendor/');
define('CONF_PATH', __DIR__ . '/source/config/');

require __DIR__ . '/source/thinkphp/base.php';

// 模拟登录状态
\think\Session::set('yoshop_store', [
    'is_login' => 1,
    'wxapp' => [
        'wxapp_id' => 10001,
        'end_time' => time() + 86400
    ]
]);

// 模拟 POST 请求
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'config_type' => 'task',
    'task_config' => [
        'referrer' => [
            3 => [
                'is_enabled' => '1',
                'is_required' => '1',
            ]
        ],
        'referee' => [
            1 => [
                'is_enabled' => '1',
                'is_required' => '1',
            ]
        ]
    ]
];

echo "=== 调试 saveConfig 错误 ===\n\n";
echo "POST 数据:\n";
print_r($_POST);
echo "\n";

try {
    // 创建控制器实例
    $controller = new \app\store\controller\setting\Referral();
    
    // 调用 saveConfig 方法
    echo "调用 saveConfig 方法...\n\n";
    $result = $controller->saveConfig();
    
    echo "返回结果:\n";
    print_r($result);
    echo "\n";
    
} catch (\Exception $e) {
    echo "❌ 捕获异常:\n";
    echo "错误消息: " . $e->getMessage() . "\n";
    echo "错误文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\n堆栈跟踪:\n";
    echo $e->getTraceAsString() . "\n";
}
