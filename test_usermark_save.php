<?php
/**
 * 测试唛头保存功能
 * 验证后台录入包裹时唛头是否正确保存到数据库
 */

define('APP_PATH', __DIR__ . '/source/application/');
require __DIR__ . '/source/thinkphp/start.php';

use app\store\model\Package;
use app\store\model\User;

echo "==================== 测试唛头保存功能 ====================\n\n";

// 测试数据
$testData = [
    'express_num' => 'TEST' . time(),
    'user_id' => 31966,
    'shop_id' => 10001,
    'country' => 1,
    'width' => 10,
    'length' => 10,
    'height' => 10,
    'weigth' => 1,
    'remark' => '测试唛头保存',
    'pack_attr' => '',
    'num' => 1,
    'price' => 0,
    'mark' => 'TEST-MARK-123',  // 唛头字段
    'class_ids' => '',  // 空字符串而不是数组
    'shelf_unit_id' => '',  // 货架单元ID
    'id' => ''  // 包裹ID（新建时为空）
];

echo "【1】测试数据\n";
echo "- 快递单号: {$testData['express_num']}\n";
echo "- 用户ID: {$testData['user_id']}\n";
echo "- 唛头: {$testData['mark']}\n\n";

// 设置 wxapp_id
\app\common\model\BaseModel::$wxapp_id = 10001;

// 创建包裹
$packageModel = new Package();
echo "【2】创建包裹...\n";

try {
    $result = $packageModel->post($testData);
    
    if ($result) {
        echo "✅ 包裹创建成功\n\n";
        
        // 查询包裹验证唛头是否保存
        echo "【3】验证唛头保存...\n";
        $package = $packageModel->where('express_num', $testData['express_num'])->find();
        
        if ($package) {
            echo "- 包裹ID: {$package['id']}\n";
            echo "- 快递单号: {$package['express_num']}\n";
            echo "- 唛头字段: " . ($package['usermark'] ?: '(空)') . "\n\n";
            
            if ($package['usermark'] === $testData['mark']) {
                echo "✅ 唛头保存成功！\n";
                echo "✅ 数据库中的唛头值与输入一致\n\n";
                
                // 清理测试数据
                echo "【4】清理测试数据...\n";
                $packageModel->where('id', $package['id'])->delete();
                echo "✅ 测试数据已清理\n";
            } else {
                echo "❌ 唛头保存失败\n";
                echo "- 期望值: {$testData['mark']}\n";
                echo "- 实际值: " . ($package['usermark'] ?: '(空)') . "\n";
            }
        } else {
            echo "❌ 无法查询到创建的包裹\n";
        }
    } else {
        echo "❌ 包裹创建失败\n";
        $error = $packageModel->getError();
        if ($error) {
            echo "错误信息: {$error}\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ 发生异常: " . $e->getMessage() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
}

echo "\n==================== 测试完成 ====================\n";
