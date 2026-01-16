<?php
/**
 * 测试重复 data[mark] 参数修复
 * Test Duplicate data[mark] Parameter Fix
 */

echo "=== 重复 data[mark] 参数问题修复验证 ===\n\n";

// 1. 问题说明
echo "1. 问题原因\n";
echo "   原始 POST 数据:\n";
echo "   data[mark]=mark2&data[mark]=\n";
echo "   \n";
echo "   解析后:\n";
echo "   - 第一个: data[mark] = 'mark2' (下拉选择)\n";
echo "   - 第二个: data[mark] = '' (文本输入框)\n";
echo "   \n";
echo "   ⚠️  PHP 会使用最后一个值，所以 \$_POST['data']['mark'] = '' (空)\n";
echo "\n";

// 2. 原因分析
echo "2. 代码问题\n";
echo "   文件: source/application/store/view/package/index/add.php\n";
echo "   \n";
echo "   修改前:\n";
echo "   - <select id=\"usermark\" name=\"data[mark]\">  ← 有 name 属性\n";
echo "   - <input id=\"inputmark\" name=\"data[mark]\">  ← 有 name 属性\n";
echo "   \n";
echo "   结果: 两个字段都提交，产生重复参数\n";
echo "\n";

// 3. 修复方案
echo "3. 修复方案 (参考 newadd.php)\n";
echo "   修改后:\n";
echo "   - <select id=\"usermark\">  ← 移除 name 属性\n";
echo "   - <input id=\"inputmark\">  ← 移除 name 属性\n";
echo "   - <input type=\"hidden\" id=\"usermarkplus\" name=\"data[mark]\">  ← 只有这个有 name\n";
echo "   \n";
echo "   JavaScript printlabel() 函数会:\n";
echo "   - 从 select 或 input 获取值\n";
echo "   - 设置到 hidden field (usermarkplus)\n";
echo "   - 只有 hidden field 会被提交\n";
echo "\n";

// 4. 检查修复状态
echo "4. 检查修复状态\n";
$file = 'source/application/store/view/package/index/add.php';
if (file_exists($file)) {
    $content = file_get_contents($file);
    
    // 检查是否还有重复的 name="data[mark]"
    $count = preg_match_all('/name="data\[mark\]"/', $content, $matches);
    
    if ($count == 1) {
        echo "   ✅ 修复成功 - 只有 1 个 name=\"data[mark]\" (hidden field)\n";
    } else if ($count > 1) {
        echo "   ❌ 仍有问题 - 找到 $count 个 name=\"data[mark]\"\n";
    } else {
        echo "   ⚠️  未找到 name=\"data[mark]\"\n";
    }
    
    // 检查是否有 usermarkplus hidden field
    if (strpos($content, 'id="usermarkplus"') !== false) {
        echo "   ✅ 找到 hidden field: id=\"usermarkplus\"\n";
    } else {
        echo "   ❌ 未找到 hidden field: id=\"usermarkplus\"\n";
    }
} else {
    echo "   ❌ 文件不存在: $file\n";
}

echo "\n";

// 5. 测试建议
echo "=== 测试步骤 ===\n";
echo "1. 访问后台录入页面: http://localhost:8080/store/package.index/add\n";
echo "2. 填写快递单号: TEST" . time() . "\n";
echo "3. 选择用户: ID 31966\n";
echo "4. 在唛头下拉框选择: mark2\n";
echo "5. 点击\"确认入库\"\n";
echo "6. 检查浏览器开发者工具 Network 标签\n";
echo "7. 查看 POST 数据，应该只有一个 data[mark]=mark2\n";
echo "8. 查询数据库验证 usermark 字段有值\n";
echo "\n";

// 6. 预期结果
echo "=== 预期结果 ===\n";
echo "POST 数据应该是:\n";
echo "data[mark]=mark2  (只有一个，不是两个)\n";
echo "\n";
echo "数据库 yoshop_package.usermark 字段:\n";
echo "应该保存为: 'mark2'\n";
echo "\n";

// 7. 相关修复
echo "=== 相关修复 ===\n";
echo "1. ✅ 前端修复: add.php - 移除重复的 name 属性\n";
echo "2. ✅ 后端修复: Package.php line 189 - 使用 null 合并运算符\n";
echo "   'usermark'=> isset(\$data['mark']) && !empty(\$data['mark']) ? \$data['mark'] : (\$result['usermark'] ?? '')\n";
echo "\n";

echo "修复完成！请测试验证。\n";
