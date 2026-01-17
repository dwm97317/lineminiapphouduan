<?php
/**
 * 添加推荐系统菜单到后台
 */

$conn = new mysqli('103.119.1.84', 'xinsuju', 'cJGzwZTDCLHzWXN4', 'xinsuju', 3306);
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}
$conn->set_charset('utf8');

echo "【1】查找设置菜单ID\n";
$result = $conn->query("SELECT access_id, name FROM yoshop_store_access WHERE name = '设置' AND parent_id = 0");
if ($result && $result->num_rows > 0) {
    $settingMenu = $result->fetch_assoc();
    $settingId = $settingMenu['access_id'];
    echo "  找到设置菜单: ID = {$settingId}\n\n";
} else {
    die("  未找到设置菜单\n");
}

echo "【2】检查是否已存在推荐系统菜单\n";
$result = $conn->query("SELECT access_id FROM yoshop_store_access WHERE name = '推荐系统'");
if ($result && $result->num_rows > 0) {
    $existing = $result->fetch_assoc();
    echo "  推荐系统菜单已存在 (ID: {$existing['access_id']})\n";
    $referralMenuId = $existing['access_id'];
} else {
    echo "  推荐系统菜单不存在，准备创建...\n";
    
    // 插入推荐系统主菜单
    $sql = "INSERT INTO yoshop_store_access (name, url, parent_id, sort, create_time, update_time) 
            VALUES ('推荐系统', '', {$settingId}, 100, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())";
    
    if ($conn->query($sql)) {
        $referralMenuId = $conn->insert_id;
        echo "  ✓ 创建推荐系统主菜单成功 (ID: {$referralMenuId})\n";
    } else {
        die("  ✗ 创建失败: " . $conn->error . "\n");
    }
}

echo "\n【3】添加推荐系统子菜单\n";

$subMenus = [
    [
        'name' => '推荐配置',
        'url' => 'store/referral/config',
        'sort' => 10
    ],
    [
        'name' => '推荐关系',
        'url' => 'store/referral/relations',
        'sort' => 20
    ],
    [
        'name' => '奖励记录',
        'url' => 'store/referral/rewards',
        'sort' => 30
    ]
];

foreach ($subMenus as $menu) {
    // 检查是否已存在
    $checkSql = "SELECT access_id FROM yoshop_store_access WHERE name = '{$menu['name']}' AND parent_id = {$referralMenuId}";
    $result = $conn->query($checkSql);
    
    if ($result && $result->num_rows > 0) {
        echo "  - {$menu['name']} 已存在\n";
    } else {
        $sql = "INSERT INTO yoshop_store_access (name, url, parent_id, sort, create_time, update_time) 
                VALUES ('{$menu['name']}', '{$menu['url']}', {$referralMenuId}, {$menu['sort']}, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())";
        
        if ($conn->query($sql)) {
            echo "  ✓ 创建 {$menu['name']} 成功 (ID: {$conn->insert_id})\n";
        } else {
            echo "  ✗ 创建 {$menu['name']} 失败: " . $conn->error . "\n";
        }
    }
}

echo "\n【4】验证菜单结构\n";
$result = $conn->query("SELECT * FROM yoshop_store_access WHERE access_id = {$referralMenuId} OR parent_id = {$referralMenuId} ORDER BY parent_id, sort");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $indent = $row['parent_id'] == 0 ? '' : '  ';
        echo "{$indent}ID: {$row['access_id']}, Name: {$row['name']}, URL: {$row['url']}, Sort: {$row['sort']}\n";
    }
}

$conn->close();
echo "\n✅ 完成！推荐系统菜单已添加到后台\n";
echo "\n访问路径:\n";
echo "  - 推荐配置: http://localhost:8080/index.php/store/referral/config\n";
echo "  - 推荐关系: http://localhost:8080/index.php/store/referral/relations\n";
echo "  - 奖励记录: http://localhost:8080/index.php/store/referral/rewards\n";
