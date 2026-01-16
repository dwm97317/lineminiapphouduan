<?php
/**
 * 模拟修复后的 POST 数据
 * Simulate Fixed POST Data
 */

echo "=== 模拟修复前后的 POST 数据对比 ===\n\n";

// 修复前的 POST 数据
echo "【修复前】POST 数据:\n";
echo "data[mark]=mark2&data[mark]=\n";
echo "\n";

parse_str("data[mark]=mark2&data[mark]=", $before);
echo "PHP 解析结果:\n";
echo "  \$_POST['data']['mark'] = '" . $before['data']['mark'] . "'\n";
echo "  长度: " . strlen($before['data']['mark']) . "\n";
echo "  是否为空: " . (empty($before['data']['mark']) ? 'YES ❌' : 'NO') . "\n";
echo "\n";

// 修复后的 POST 数据
echo "【修复后】POST 数据:\n";
echo "data[mark]=mark2\n";
echo "\n";

parse_str("data[mark]=mark2", $after);
echo "PHP 解析结果:\n";
echo "  \$_POST['data']['mark'] = '" . $after['data']['mark'] . "'\n";
echo "  长度: " . strlen($after['data']['mark']) . "\n";
echo "  是否为空: " . (empty($after['data']['mark']) ? 'YES' : 'NO ✅') . "\n";
echo "\n";

// 模拟后端处理
echo "=== 模拟后端处理 ===\n\n";

// 修复前
echo "【修复前】后端处理:\n";
$data_before = ['mark' => $before['data']['mark']];
$result = null; // 新包裹，没有现有记录

// 旧代码 (有问题)
$usermark_old = isset($data_before['mark']) ? $data_before['mark'] : ($result ? $result['usermark'] : null);
echo "  旧代码: isset(\$data['mark']) ? \$data['mark'] : \$result['usermark']\n";
echo "  结果: usermark = '" . ($usermark_old ?? 'NULL') . "'\n";
echo "  保存到数据库: '" . ($usermark_old ?? 'NULL') . "' ❌\n";
echo "\n";

// 修复后
echo "【修复后】后端处理:\n";
$data_after = ['mark' => $after['data']['mark']];

// 新代码 (已修复)
$usermark_new = isset($data_after['mark']) && !empty($data_after['mark']) 
    ? $data_after['mark'] 
    : ($result['usermark'] ?? '');
echo "  新代码: isset(\$data['mark']) && !empty(\$data['mark']) ? \$data['mark'] : (\$result['usermark'] ?? '')\n";
echo "  结果: usermark = '" . $usermark_new . "'\n";
echo "  保存到数据库: '" . $usermark_new . "' ✅\n";
echo "\n";

// 总结
echo "=== 总结 ===\n";
echo "修复前: 选择 mark2 → 保存为空字符串 ❌\n";
echo "修复后: 选择 mark2 → 保存为 'mark2' ✅\n";
echo "\n";

echo "修复包括:\n";
echo "1. 前端: 移除重复的 name=\"data[mark]\" 属性\n";
echo "2. 后端: 使用 null 合并运算符 ?? 处理空值\n";
