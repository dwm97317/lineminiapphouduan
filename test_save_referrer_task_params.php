<?php
/**
 * 测试保存推荐人任务参数
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
    
    echo "=== 测试保存推荐人任务参数 ===\n\n";
    
    // 查询推荐人任务配置
    $referrerTask = $db->name('referral_task_config')
        ->where('user_type', 1)
        ->where('task_type', 'invite_success')
        ->find();
    
    if (!$referrerTask) {
        echo "✗ 未找到推荐人任务配置\n";
        exit;
    }
    
    echo "找到任务: {$referrerTask['config_name']} (ID: {$referrerTask['id']})\n";
    echo "当前 task_params: " . var_export($referrerTask['task_params'], true) . "\n\n";
    
    // 准备新的参数
    $newParams = [
        'min_invites' => 5  // 设置最低邀请人数为5
    ];
    
    $paramsJson = json_encode($newParams, JSON_UNESCAPED_UNICODE);
    
    echo "准备保存新参数: {$paramsJson}\n";
    
    // 使用原始SQL更新
    $result = $db->name('referral_task_config')
        ->where('id', $referrerTask['id'])
        ->update([
            'task_params' => $paramsJson
        ]);
    
    if ($result !== false) {
        echo "✓ 参数保存成功\n\n";
        
        // 验证保存结果
        $updated = $db->name('referral_task_config')
            ->where('id', $referrerTask['id'])
            ->find();
        
        echo "验证保存结果:\n";
        echo "task_params (原始): " . var_export($updated['task_params'], true) . "\n";
        
        if (!empty($updated['task_params'])) {
            $decoded = json_decode($updated['task_params'], true);
            echo "task_params (解析): " . json_encode($decoded, JSON_UNESCAPED_UNICODE) . "\n";
            echo "min_invites 值: " . ($decoded['min_invites'] ?? '未设置') . "\n";
        }
    } else {
        echo "✗ 参数保存失败\n";
    }
    
    echo "\n=== 测试完成 ===\n";
    
} catch (\Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "堆栈: " . $e->getTraceAsString() . "\n";
}
