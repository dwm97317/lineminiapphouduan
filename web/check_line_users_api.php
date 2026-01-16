<?php
// 临时 API 来检查 LINE 用户
define('APP_PATH', __DIR__ . '/../source/application/');
define('ROOT_PATH', __DIR__ . '/../');

// 加载框架引导文件
require __DIR__ . '/../source/thinkphp/start.php';

use think\Db;

header('Content-Type: text/plain; charset=utf-8');

try {
    $lineOpenId = 'Ud4e37d68c438cc70350957039add98d8';
    
    // 查询 yoshop_user 表
    $users = Db::name('user')
        ->where('line_openid', $lineOpenId)
        ->field('user_id, nickName, line_openid, open_id, create_time')
        ->order('create_time DESC')
        ->select();
    
    echo "LINE 用户记录 (line_openid = $lineOpenId):\n";
    echo "===========================================\n";
    foreach ($users as $index => $user) {
        $num = $index + 1;
        echo "[$num] User ID: {$user['user_id']}, Nick: {$user['nickName']}, Open ID: {$user['open_id']}, Created: {$user['create_time']}\n";
    }
    echo "\n总共找到 " . count($users) . " 条记录\n";
    
    // 查询 user_binding 表
    $bindings = Db::name('user_binding')
        ->where('openid', $lineOpenId)
        ->where('platform', 'LINE')
        ->field('id, user_id, openid, platform, create_time')
        ->order('create_time DESC')
        ->select();
    
    echo "\nuser_binding 表中的 LINE 记录:\n";
    echo "===========================================\n";
    foreach ($bindings as $index => $binding) {
        $num = $index + 1;
        echo "[$num] ID: {$binding['id']}, User ID: {$binding['user_id']}, OpenID: {$binding['openid']}, Platform: {$binding['platform']}, Created: {$binding['create_time']}\n";
    }
    echo "\n总共找到 " . count($bindings) . " 条记录\n";
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
