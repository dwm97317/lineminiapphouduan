<?php
/**
 * 测试推荐人任务参数显示
 */

// 引入ThinkPHP
require __DIR__ . '/source/thinkphp/base.php';

// 数据库配置
$config = [
    'type'     => 'mysql',
    'hostname' => '103.119.1.84',
    'database' => 'xinsuju',
    'username' => 'xinsuju',
    'password' => 'cJGzwZTDCLHzWXN4',
    'hostport' => '3306',
    'prefix'   => 'yoshop_',
    'charset'  => 'utf8',
];

try {
    // 连接数据库
    $db = \think\Db::connect($config);
    
    echo "=== 测试推荐人任务参数 ===\n\n";
    
    // 查询推荐人任务配置
    $referrerTasks = $db->name('referral_task_config')
        ->where('user_type', 1)
        ->order('sort_order', 'asc')
        ->select();
    
    echo "推荐人任务数量: " . count($referrerTasks) . "\n\n";
    
    foreach ($referrerTasks as $task) {
        echo "任务ID: {$task['id']}\n";
        echo "任务名称: {$task['config_name']}\n";
        echo "任务类型: {$task['task_type']}\n";
        echo "是否启用: " . ($task['is_enabled'] ? '是' : '否') . "\n";
        echo "是否必须: " . ($task['is_required'] ? '是' : '否') . "\n";
        echo "task_params (原始): " . var_export($task['task_params'], true) . "\n";
        
        // 解析 task_params
        $taskParams = [];
        if (!empty($task['task_params'])) {
            if (is_string($task['task_params'])) {
                $taskParams = json_decode($task['task_params'], true) ?: [];
                echo "task_params (解析后): " . json_encode($taskParams, JSON_UNESCAPED_UNICODE) . "\n";
            } elseif (is_array($task['task_params'])) {
                $taskParams = $task['task_params'];
                echo "task_params (已是数组): " . json_encode($taskParams, JSON_UNESCAPED_UNICODE) . "\n";
            }
        } else {
            echo "task_params: 空\n";
        }
        
        // 检查特定参数
        if ($task['task_type'] == 'invite_success') {
            $minInvites = isset($taskParams['min_invites']) ? $taskParams['min_invites'] : 1;
            echo "最低邀请人数: {$minInvites}\n";
        }
        
        echo "\n" . str_repeat('-', 50) . "\n\n";
    }
    
    echo "\n=== 测试完成 ===\n";
    
} catch (\Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "堆栈: " . $e->getTraceAsString() . "\n";
}
