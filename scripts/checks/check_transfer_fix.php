<?php
/**
 * 检查转单功能修复状态
 */

echo "=== 转单功能修复状态检查 ===\n\n";

// 检查 Inpack.php 文件
$inpack_file = __DIR__ . '/source/application/store/model/Inpack.php';
if (!file_exists($inpack_file)) {
    die("错误: 找不到 Inpack.php 文件\n");
}

$inpack_content = file_get_contents($inpack_file);

echo "1. 检查字段白名单是否包含 tt_number 和 transfer\n";
if (strpos($inpack_content, "'tt_number'") !== false && strpos($inpack_content, "'transfer'") !== false) {
    echo "   ✓ 字段白名单已更新\n";
    
    // 提取字段白名单行
    if (preg_match('/\$field\s*=\s*\[(.*?)\];/s', $inpack_content, $matches)) {
        echo "   字段列表: " . trim($matches[1]) . "\n";
    }
} else {
    echo "   ✗ 字段白名单缺少 tt_number 或 transfer\n";
    echo "   需要在第377行添加这两个字段\n";
}

echo "\n2. 检查转单模式是否有承运商查询逻辑\n";
if (strpos($inpack_content, "if(\$data['type']=='change')") !== false) {
    echo "   ✓ 找到转单模式判断\n";
    
    if (strpos($inpack_content, "\$carrier_name = '';") !== false) {
        echo "   ✓ 找到承运商查询逻辑\n";
    } else {
        echo "   ✗ 缺少承运商查询逻辑\n";
    }
    
    if (strpos($inpack_content, "Express())->where('express_code',\$data['tt_number'])") !== false) {
        echo "   ✓ 找到外部承运商查询\n";
    } else {
        echo "   ✗ 缺少外部承运商查询\n";
    }
    
    if (strpos($inpack_content, "DitchModel::detail(\$data['t_number'])") !== false) {
        echo "   ✓ 找到自有物流查询\n";
    } else {
        echo "   ✗ 缺少自有物流查询\n";
    }
} else {
    echo "   ✗ 未找到转单模式判断\n";
}

echo "\n3. 检查调试代码是否已添加\n";
if (strpos($inpack_content, 'debug_transfer_log.txt') !== false) {
    echo "   ✓ 调试代码已添加\n";
} else {
    echo "   ⚠ 未找到调试代码（可选）\n";
}

// 检查 TrOrder.php 文件
echo "\n4. 检查 TrOrder.php 控制器\n";
$trorder_file = __DIR__ . '/source/application/store/controller/TrOrder.php';
if (file_exists($trorder_file)) {
    $trorder_content = file_get_contents($trorder_file);
    
    if (strpos($trorder_content, 'debug_transfer_log.txt') !== false) {
        echo "   ✓ 调试代码已添加到 deliverySave()\n";
    } else {
        echo "   ⚠ 未找到调试代码（可选）\n";
    }
} else {
    echo "   ✗ 找不到 TrOrder.php 文件\n";
}

// 检查表单文件
echo "\n5. 检查转单表单\n";
$form_file = __DIR__ . '/source/application/store/view/tr_order/changesn.php';
if (file_exists($form_file)) {
    $form_content = file_get_contents($form_file);
    
    if (strpos($form_content, "name=\"delivery[type]\" value=\"change\"") !== false) {
        echo "   ✓ 表单包含 type=change 字段\n";
    } else {
        echo "   ✗ 表单缺少 type=change 字段\n";
    }
    
    if (strpos($form_content, "name=\"delivery[transfer]\"") !== false) {
        echo "   ✓ 表单包含 transfer 字段\n";
    } else {
        echo "   ✗ 表单缺少 transfer 字段\n";
    }
    
    if (strpos($form_content, "name=\"delivery[tt_number]\"") !== false) {
        echo "   ✓ 表单包含 tt_number 字段\n";
    } else {
        echo "   ✗ 表单缺少 tt_number 字段\n";
    }
} else {
    echo "   ✗ 找不到表单文件\n";
}

echo "\n=== 检查完成 ===\n\n";

echo "下一步:\n";
echo "1. 如果所有检查都通过（✓），请执行转单操作并查看日志\n";
echo "2. 如果有失败项（✗），请先修复这些问题\n";
echo "3. 查看详细说明: DEBUG_TRANSFER_INSTRUCTIONS.md\n";
