<?php
/**
 * 测试getLineUserIdByUserId方法
 */

// 引入ThinkPHP框架
define('APP_PATH', __DIR__ . '/source/application/');
require __DIR__ . '/source/thinkphp/start.php';

use app\common\model\User;

echo "==================== 测试getLineUserIdByUserId ====================\n\n";

$userId = 31966;

echo "【方法1】直接查询数据库\n";
$user = User::where(['user_id' => $userId])->find();
if ($user) {
    echo "✅ 用户存在\n";
    echo "user_id: {$user['user_id']}\n";
    echo "line_openid: " . ($user['line_openid'] ?: '空') . "\n";
    echo "line_openid类型: " . gettype($user['line_openid']) . "\n";
    echo "line_openid长度: " . strlen($user['line_openid']) . "\n";
    echo "empty检查: " . (empty($user['line_openid']) ? '是空' : '不是空') . "\n";
} else {
    echo "❌ 用户不存在\n";
}

echo "\n【方法2】模拟Basics类的getLineUserIdByUserId方法\n";

// 模拟方法逻辑
$user2 = User::where(['user_id' => $userId])->find();

if (!$user2) {
    echo "❌ 用户不存在\n";
} else {
    echo "✅ 用户存在\n";
    
    // 优先使用 line_openid 字段
    if (!empty($user2['line_openid'])) {
        $lineUserId = $user2['line_openid'];
        echo "✅ 从line_openid获取: {$lineUserId}\n";
    } elseif (!empty($user2['line_user_id'])) {
        $lineUserId = $user2['line_user_id'];
        echo "✅ 从line_user_id获取: {$lineUserId}\n";
    } else {
        $lineUserId = null;
        echo "❌ 两个字段都为空\n";
    }
    
    if ($lineUserId) {
        echo "✅ 最终返回: {$lineUserId}\n";
    } else {
        echo "❌ 返回null\n";
    }
}

echo "\n【方法3】检查User模型字段\n";
$userArray = $user->toArray();
echo "用户数组键: " . implode(', ', array_keys($userArray)) . "\n";

if (array_key_exists('line_openid', $userArray)) {
    echo "✅ line_openid字段存在\n";
    echo "值: " . var_export($userArray['line_openid'], true) . "\n";
} else {
    echo "❌ line_openid字段不存在\n";
}

echo "\n==================== 测试完成 ====================\n";
