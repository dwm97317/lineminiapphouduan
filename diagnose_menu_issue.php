<?php
/**
 * 诊断推荐系统菜单问题
 * 分析为什么进入推荐系统页面后，设置菜单的子菜单消失
 */

echo "菜单结构诊断\n";
echo "===================\n\n";

// 加载菜单配置
$menus = include __DIR__ . '/source/application/store/extra/menus.php';

// 找到设置菜单
$settingMenu = $menus['setting'] ?? null;

if (!$settingMenu) {
    echo "错误: 找不到设置菜单\n";
    exit;
}

echo "设置菜单结构:\n";
echo "- 名称: {$settingMenu['name']}\n";
echo "- 图标: {$settingMenu['icon']}\n";
echo "- 索引: {$settingMenu['index']}\n";
echo "- 子菜单数量: " . count($settingMenu['submenu']) . "\n\n";

// 查找推荐系统菜单
$referralMenu = null;
$referralIndex = null;

foreach ($settingMenu['submenu'] as $index => $submenu) {
    if (isset($submenu['name']) && strpos($submenu['name'], '推荐') !== false) {
        $referralMenu = $submenu;
        $referralIndex = $index;
        break;
    }
}

if (!$referralMenu) {
    echo "错误: 在设置菜单中找不到推荐系统\n";
    exit;
}

echo "推荐系统菜单 (索引 {$referralIndex}):\n";
echo "- 名称: {$referralMenu['name']}\n";
echo "- 是否有index: " . (isset($referralMenu['index']) ? '是 (' . $referralMenu['index'] . ')' : '否') . "\n";
echo "- 是否有active: " . (isset($referralMenu['active']) ? '是 (' . ($referralMenu['active'] ? 'true' : 'false') . ')' : '否') . "\n";
echo "- 是否有submenu: " . (isset($referralMenu['submenu']) ? '是 (' . count($referralMenu['submenu']) . '项)' : '否') . "\n\n";

if (isset($referralMenu['submenu'])) {
    echo "推荐系统子菜单:\n";
    foreach ($referralMenu['submenu'] as $i => $item) {
        echo ($i + 1) . ". {$item['name']}\n";
        echo "   - index: {$item['index']}\n";
        if (isset($item['uris'])) {
            echo "   - uris: " . implode(', ', $item['uris']) . "\n";
        }
        echo "\n";
    }
}

// 对比其他有子菜单的项
echo "\n对比分析 - 其他有子菜单的设置项:\n";
echo "===================\n\n";

$examplesFound = 0;
foreach ($settingMenu['submenu'] as $index => $submenu) {
    if (isset($submenu['submenu']) && $submenu['name'] !== '推荐系统') {
        echo "示例 " . (++$examplesFound) . ": {$submenu['name']}\n";
        echo "- 是否有index: " . (isset($submenu['index']) ? '是 (' . $submenu['index'] . ')' : '否') . "\n";
        echo "- 是否有active: " . (isset($submenu['active']) ? '是' : '否') . "\n";
        echo "- 子菜单数量: " . count($submenu['submenu']) . "\n\n";
        
        if ($examplesFound >= 3) break;
    }
}

echo "\n问题分析:\n";
echo "===================\n";

$issues = [];

// 检查1: 是否同时有index和active
if (isset($referralMenu['index']) && isset($referralMenu['active'])) {
    $issues[] = "⚠ 推荐系统同时有 'index' 和 'active' 字段，可能导致冲突";
}

// 检查2: 对比其他菜单项
$hasIndexCount = 0;
$hasActiveCount = 0;
foreach ($settingMenu['submenu'] as $submenu) {
    if (isset($submenu['submenu'])) {
        if (isset($submenu['index'])) $hasIndexCount++;
        if (isset($submenu['active'])) $hasActiveCount++;
    }
}

echo "统计: 设置菜单中有子菜单的项\n";
echo "- 有 'index' 字段: {$hasIndexCount} 项\n";
echo "- 有 'active' 字段: {$hasActiveCount} 项\n\n";

if (count($issues) > 0) {
    echo "发现的问题:\n";
    foreach ($issues as $i => $issue) {
        echo ($i + 1) . ". {$issue}\n";
    }
} else {
    echo "✓ 未发现明显的配置问题\n";
}

echo "\n建议:\n";
echo "===================\n";
echo "1. 有子菜单的父菜单项应该只使用 'active' => true，不要同时使用 'index'\n";
echo "2. 确保所有子菜单项都有正确的 'uris' 配置\n";
echo "3. 清除缓存后测试: runtime/temp/ 和 runtime/cache/\n";
