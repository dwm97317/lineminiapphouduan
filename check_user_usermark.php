<?php
/**
 * 查询用户的唛头信息
 */

define('APP_PATH', __DIR__ . '/source/application/');
require __DIR__ . '/source/thinkphp/start.php';

use app\store\model\user\UserMark as UserMarkModel;

echo "==================== 查询用户唛头 ====================\n\n";

$userId = 31966;

echo "【1】查询用户 {$userId} 的唛头列表\n";

$userMarks = (new UserMarkModel())->where('user_id', $userId)->select();

if ($userMarks && count($userMarks) > 0) {
    echo "✅ 找到 " . count($userMarks) . " 个唛头\n\n";
    
    foreach ($userMarks as $index => $mark) {
        echo "唛头 " . ($index + 1) . ":\n";
        echo "- ID: {$mark['id']}\n";
        echo "- 唛头: {$mark['mark']}\n";
        echo "- 描述: " . ($mark['markdes'] ?? '无') . "\n";
        echo "- 创建时间: " . (is_numeric($mark['create_time']) ? date('Y-m-d H:i:s', $mark['create_time']) : $mark['create_time']) . "\n\n";
    }
} else {
    echo "❌ 该用户没有唛头\n";
    echo "提示: 需要先在用户管理中为该用户添加唛头\n";
}

echo "==================== 完成 ====================\n";
