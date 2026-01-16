<?php
/**
 * 验证唛头保存修复
 * Verify Usermark Save Fix
 */

// 数据库连接
$host = '103.119.1.84';
$username = 'xinsuju';
$password = 'cJGzwZTDCLHzWXN4';
$database = 'xinsuju';

$conn = new mysqli($host, $username, $password, $database);
$conn->set_charset('utf8');

if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

echo "=== 唛头保存修复验证 ===\n\n";

// 1. 检查代码修复
echo "1. 检查 Package.php 代码修复状态\n";
$file = 'source/application/store/model/Package.php';
if (file_exists($file)) {
    $content = file_get_contents($file);
    
    // 查找修复后的代码
    if (strpos($content, "(\$result['usermark'] ?? '')") !== false) {
        echo "   ✅ 代码已修复 - 使用了 null 合并运算符\n";
        echo "   修复内容：'usermark'=> isset(\$data['mark']) && !empty(\$data['mark']) ? \$data['mark'] : (\$result['usermark'] ?? '')\n";
    } else if (strpos($content, "\$result['usermark']") !== false) {
        echo "   ⚠️  代码可能未完全修复 - 仍使用旧的 \$result['usermark']\n";
    } else {
        echo "   ❌ 未找到 usermark 相关代码\n";
    }
} else {
    echo "   ❌ 文件不存在: $file\n";
}

echo "\n";

// 2. 检查最近的包裹记录
echo "2. 检查最近录入的包裹唛头数据\n";
$sql = "SELECT id, express_num, usermark, member_id, created_time 
        FROM yoshop_package 
        WHERE is_delete = 0 
        ORDER BY id DESC 
        LIMIT 10";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "   最近 10 条包裹记录：\n";
    echo "   " . str_repeat("-", 80) . "\n";
    printf("   %-8s %-20s %-15s %-10s %-20s\n", "ID", "快递单号", "唛头", "用户ID", "创建时间");
    echo "   " . str_repeat("-", 80) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        $usermark = empty($row['usermark']) ? '(空)' : $row['usermark'];
        printf("   %-8s %-20s %-15s %-10s %-20s\n", 
            $row['id'], 
            $row['express_num'], 
            $usermark,
            $row['member_id'] ?: '(无)',
            $row['created_time']
        );
    }
} else {
    echo "   ❌ 未找到包裹记录\n";
}

echo "\n";

// 3. 统计唛头使用情况
echo "3. 唛头使用统计\n";
$sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN usermark IS NOT NULL AND usermark != '' THEN 1 ELSE 0 END) as with_usermark,
            SUM(CASE WHEN usermark IS NULL OR usermark = '' THEN 1 ELSE 0 END) as without_usermark
        FROM yoshop_package 
        WHERE is_delete = 0";

$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    echo "   总包裹数: {$row['total']}\n";
    echo "   有唛头: {$row['with_usermark']} (" . round($row['with_usermark']/$row['total']*100, 2) . "%)\n";
    echo "   无唛头: {$row['without_usermark']} (" . round($row['without_usermark']/$row['total']*100, 2) . "%)\n";
}

echo "\n";

// 4. 测试建议
echo "=== 测试建议 ===\n";
echo "1. 通过后台录入新包裹，选择唛头（如 mark2）\n";
echo "2. 提交后检查数据库 yoshop_package 表的 usermark 字段\n";
echo "3. 预期结果：usermark 字段应该保存选择的唛头值\n";
echo "\n";
echo "测试 URL: http://localhost:8080/store/package.index/newadd\n";
echo "测试用户: ID 31966\n";
echo "测试唛头: mark2 或其他已存在的唛头\n";

$conn->close();
