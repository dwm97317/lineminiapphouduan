<?php
/**
 * 验证所有唛头修复是否已应用
 * Verify All Usermark Fixes Applied
 */

echo "=== 唛头保存功能修复验证 ===\n";
echo "Usermark Save Function Fix Verification\n\n";

$allPassed = true;

// 1. 检查 HTML 修复
echo "【1】HTML 修复检查\n";
echo "文件: source/application/store/view/package/index/add.php\n";

$addFile = 'source/application/store/view/package/index/add.php';
if (file_exists($addFile)) {
    $content = file_get_contents($addFile);
    
    // 检查 name="data[mark]" 的数量
    $nameCount = preg_match_all('/name="data\[mark\]"/', $content, $matches);
    
    if ($nameCount == 1) {
        echo "  ✅ PASS: 只有 1 个 name=\"data[mark]\" (hidden field)\n";
    } else {
        echo "  ❌ FAIL: 找到 $nameCount 个 name=\"data[mark]\" (应该只有 1 个)\n";
        $allPassed = false;
    }
    
    // 检查是否有 usermarkplus hidden field
    if (strpos($content, 'id="usermarkplus"') !== false && 
        strpos($content, 'type="hidden"') !== false) {
        echo "  ✅ PASS: 找到 hidden field (id=\"usermarkplus\")\n";
    } else {
        echo "  ❌ FAIL: 未找到 hidden field\n";
        $allPassed = false;
    }
} else {
    echo "  ❌ FAIL: 文件不存在\n";
    $allPassed = false;
}

echo "\n";

// 2. 检查 JavaScript 修复
echo "【2】JavaScript 修复检查\n";
echo "文件: source/application/store/view/package/index/add.php\n";

if (file_exists($addFile)) {
    $content = file_get_contents($addFile);
    
    // 检查是否有 $("#usermarkplus").val(usermark)
    if (strpos($content, '$("#usermarkplus").val(usermark)') !== false ||
        strpos($content, '$(\'#usermarkplus\').val(usermark)') !== false) {
        echo "  ✅ PASS: 找到赋值代码 \$(\"#usermarkplus\").val(usermark)\n";
    } else {
        echo "  ❌ FAIL: 未找到赋值代码\n";
        $allPassed = false;
    }
    
    // 检查 printlabel 函数是否存在
    if (strpos($content, 'function printlabel()') !== false) {
        echo "  ✅ PASS: 找到 printlabel() 函数\n";
    } else {
        echo "  ❌ FAIL: 未找到 printlabel() 函数\n";
        $allPassed = false;
    }
} else {
    echo "  ❌ FAIL: 文件不存在\n";
    $allPassed = false;
}

echo "\n";

// 3. 检查 PHP 后端修复
echo "【3】PHP 后端修复检查\n";
echo "文件: source/application/store/model/Package.php\n";

$packageFile = 'source/application/store/model/Package.php';
if (file_exists($packageFile)) {
    $content = file_get_contents($packageFile);
    
    // 检查是否使用了 null 合并运算符
    if (strpos($content, "(\$result['usermark'] ?? '')") !== false) {
        echo "  ✅ PASS: 找到 null 合并运算符 ??\n";
    } else {
        echo "  ❌ FAIL: 未找到 null 合并运算符\n";
        $allPassed = false;
    }
    
    // 检查 uodatepackStatus 方法是否存在
    if (strpos($content, 'function uodatepackStatus') !== false ||
        strpos($content, 'public function uodatepackStatus') !== false) {
        echo "  ✅ PASS: 找到 uodatepackStatus() 方法\n";
    } else {
        echo "  ❌ FAIL: 未找到 uodatepackStatus() 方法\n";
        $allPassed = false;
    }
} else {
    echo "  ❌ FAIL: 文件不存在\n";
    $allPassed = false;
}

echo "\n";

// 4. 数据库连接测试
echo "【4】数据库连接测试\n";

$host = '103.119.1.84';
$username = 'xinsuju';
$password = 'cJGzwZTDCLHzWXN4';
$database = 'xinsuju';

$conn = new mysqli($host, $username, $password, $database);
$conn->set_charset('utf8');

if ($conn->connect_error) {
    echo "  ❌ FAIL: 数据库连接失败\n";
    $allPassed = false;
} else {
    echo "  ✅ PASS: 数据库连接成功\n";
    
    // 检查最近的包裹
    $sql = "SELECT COUNT(*) as count FROM yoshop_package WHERE is_delete = 0";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        echo "  ✅ PASS: 可以查询包裹表 (总数: {$row['count']})\n";
    } else {
        echo "  ❌ FAIL: 无法查询包裹表\n";
        $allPassed = false;
    }
    
    $conn->close();
}

echo "\n";

// 总结
echo "=== 验证总结 ===\n";
if ($allPassed) {
    echo "🟢 所有检查通过！All checks passed!\n";
    echo "\n";
    echo "修复状态:\n";
    echo "  ✅ HTML 修复: 完成\n";
    echo "  ✅ JavaScript 修复: 完成\n";
    echo "  ✅ PHP 后端修复: 完成\n";
    echo "  ✅ 数据库连接: 正常\n";
    echo "\n";
    echo "下一步: 请按照 FINAL_USERMARK_TEST_CHECKLIST.md 进行功能测试\n";
} else {
    echo "🔴 有检查未通过！Some checks failed!\n";
    echo "\n";
    echo "请检查上述失败的项目并修复。\n";
}

echo "\n";
echo "详细文档:\n";
echo "  - USERMARK_JAVASCRIPT_FIX.md (JavaScript 修复)\n";
echo "  - USERMARK_DUPLICATE_FIELD_FIX.md (HTML 修复)\n";
echo "  - USERMARK_SAVE_FINAL_FIX.md (PHP 后端修复)\n";
echo "  - FINAL_USERMARK_TEST_CHECKLIST.md (测试清单)\n";
