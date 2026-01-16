<?php
/**
 * 调试唛头保存问题
 * 检查后台录入时唛头是否正确传递和保存
 */

// 模拟后台录入的数据
$testData = [
    'express_num' => 'TEST' . time(),
    'user_id' => 31966,
    'mark' => 'DEBUG-MARK-TEST',  // 这是唛头字段
    'shop_id' => 1,
    'country' => 1,
    'width' => 10,
    'length' => 10,
    'height' => 10,
    'weigth' => 1,
    'remark' => '调试测试',
    'express_id' => 1,
    'price' => 100,
    'num' => 1,
];

echo "=== 唛头保存调试 ===\n\n";

echo "1. 模拟前端提交的数据\n";
echo "   data[mark] = '{$testData['mark']}'\n";
echo "\n";

echo "2. 检查 Package.php 中的处理逻辑\n";
echo "   uodatepackStatus() 方法：\n";
echo "   \$result = \$this->where('express_num',\$data['express_num'])->find();\n";
echo "   \n";
echo "   如果 \$result 存在（更新包裹）：\n";
echo "   'usermark'=> isset(\$data['mark'])?\$data['mark']:\$result['usermark']\n";
echo "   \n";
echo "   如果 \$result 不存在（新包裹）：\n";
echo "   'usermark'=> isset(\$data['mark'])?\$data['mark']:\$result['usermark']  ← 问题！\n";
echo "   \$result['usermark'] 会导致错误或返回 null\n";
echo "\n";

echo "3. 问题分析\n";
echo "   当创建新包裹时：\n";
echo "   - \$result = null\n";
echo "   - \$result['usermark'] 会触发 PHP 警告或返回 null\n";
echo "   - 即使 isset(\$data['mark']) 为 true，也可能被覆盖\n";
echo "\n";

echo "4. 解决方案\n";
echo "   修改 uodatepackStatus() 方法中的代码：\n";
echo "   \n";
echo "   修改前：\n";
echo "   'usermark'=> isset(\$data['mark'])?\$data['mark']:\$result['usermark'],\n";
echo "   \n";
echo "   修改后：\n";
echo "   'usermark'=> isset(\$data['mark'])?\$data['mark']:(\$result?(\$result['usermark']??''):''),\n";
echo "   \n";
echo "   或者更简洁：\n";
echo "   'usermark'=> isset(\$data['mark']) && !empty(\$data['mark']) ? \$data['mark'] : (\$result['usermark'] ?? ''),\n";
echo "\n";

// 连接数据库检查最近的包裹
$host = '103.119.1.84';
$username = 'xinsuju';
$password = 'cJGzwZTDCLHzWXN4';
$database = 'xinsuju';

$conn = new mysqli($host, $username, $password, $database);
$conn->set_charset('utf8');

if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

echo "5. 检查最近的包裹记录\n";
$sql = "SELECT id, express_num, member_id, usermark, created_time 
        FROM yoshop_package 
        WHERE member_id = 31966 
        ORDER BY id DESC 
        LIMIT 10";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "   最近10条包裹记录：\n";
    echo "   " . str_repeat("-", 80) . "\n";
    printf("   %-8s %-20s %-15s %-20s\n", "ID", "快递单号", "唛头", "创建时间");
    echo "   " . str_repeat("-", 80) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        $mark = $row['usermark'] ? $row['usermark'] : '(空)';
        printf("   %-8s %-20s %-15s %-20s\n", 
            $row['id'], 
            $row['express_num'], 
            $mark, 
            $row['created_time']
        );
    }
    echo "   " . str_repeat("-", 80) . "\n";
} else {
    echo "   ⚠️  没有找到包裹记录\n";
}

echo "\n";

echo "6. 检查代码中的实际情况\n";
$packageFile = __DIR__ . '/source/application/store/model/Package.php';
if (file_exists($packageFile)) {
    $content = file_get_contents($packageFile);
    
    // 查找 uodatepackStatus 方法中的 usermark 行
    if (preg_match("/'usermark'[^,]+,/", $content, $matches)) {
        echo "   当前代码：\n";
        echo "   " . trim($matches[0]) . "\n";
    }
}

echo "\n";

echo "=== 修复建议 ===\n";
echo "需要修改 Package.php 的 uodatepackStatus() 方法\n";
echo "将第 189 行的代码修改为：\n";
echo "\n";
echo "修改前：\n";
echo "'usermark'=> isset(\$data['mark'])?\$data['mark']:\$result['usermark'],\n";
echo "\n";
echo "修改后：\n";
echo "'usermark'=> isset(\$data['mark']) && !empty(\$data['mark']) ? \$data['mark'] : (\$result['usermark'] ?? ''),\n";
echo "\n";
echo "这样可以确保：\n";
echo "1. 如果 data['mark'] 有值，使用它\n";
echo "2. 如果 data['mark'] 为空，且 \$result 存在，使用 \$result['usermark']\n";
echo "3. 如果 data['mark'] 为空，且 \$result 不存在，使用空字符串\n";

$conn->close();
