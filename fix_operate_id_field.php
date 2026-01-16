<?php
/**
 * 修复 operate_id 字段问题
 * 错误: Field 'operate_id' doesn't have a default value
 */

// 数据库配置
$host = '103.119.1.84';
$database = 'xinsuju';
$username = 'xinsuju';
$password = 'cJGzwZTDCLHzWXN4';
$prefix = 'yoshop_';

try {
    // 连接数据库
    $pdo = new PDO(
        "mysql:host={$host};dbname={$database};charset=utf8",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ 数据库连接成功\n\n";
    
    // 步骤1: 检查表是否存在
    echo "=== 步骤1: 检查 inpack 表 ===\n";
    $stmt = $pdo->query("SHOW TABLES LIKE '{$prefix}inpack'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "❌ 表 {$prefix}inpack 不存在\n";
        exit(1);
    }
    
    echo "✅ 找到表: " . implode(', ', $tables) . "\n\n";
    
    // 步骤2: 检查 operate_id 字段
    echo "=== 步骤2: 检查 operate_id 字段 ===\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM `{$prefix}inpack` LIKE 'operate_id'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$column) {
        echo "❌ operate_id 字段不存在\n";
        echo "这很奇怪，错误提示字段缺少默认值，但字段本身不存在\n";
        echo "可能需要检查代码中是否有其他地方引用了这个字段\n";
        exit(1);
    }
    
    echo "✅ 找到 operate_id 字段\n";
    echo "字段信息:\n";
    print_r($column);
    echo "\n";
    
    // 步骤3: 分析字段定义
    echo "=== 步骤3: 分析字段定义 ===\n";
    $isNullable = ($column['Null'] === 'YES');
    $hasDefault = ($column['Default'] !== null || $column['Null'] === 'YES');
    
    echo "可为空: " . ($isNullable ? '是' : '否') . "\n";
    echo "默认值: " . ($column['Default'] ?? 'NULL') . "\n";
    
    if (!$isNullable && $column['Default'] === null) {
        echo "⚠️  问题确认: 字段不允许NULL且没有默认值\n\n";
        
        // 步骤4: 修复字段
        echo "=== 步骤4: 修复 operate_id 字段 ===\n";
        echo "执行 SQL: ALTER TABLE `{$prefix}inpack` MODIFY COLUMN `operate_id` int(11) NULL DEFAULT 0;\n";
        
        $pdo->exec("ALTER TABLE `{$prefix}inpack` MODIFY COLUMN `operate_id` int(11) NULL DEFAULT 0");
        
        echo "✅ 字段修复成功\n\n";
        
        // 步骤5: 验证修复
        echo "=== 步骤5: 验证修复结果 ===\n";
        $stmt = $pdo->query("SHOW COLUMNS FROM `{$prefix}inpack` LIKE 'operate_id'");
        $newColumn = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "修复后的字段信息:\n";
        print_r($newColumn);
        
        if ($newColumn['Null'] === 'YES' && $newColumn['Default'] === '0') {
            echo "\n✅ 修复成功! operate_id 字段现在允许NULL且默认值为0\n";
        } else {
            echo "\n⚠️  修复可能不完整，请检查字段定义\n";
        }
    } else {
        echo "✅ 字段定义正常，不需要修复\n";
        echo "如果仍然出现错误，可能是其他原因\n";
    }
    
    echo "\n=== 修复完成 ===\n";
    echo "请重新测试打包功能: /store/package.index/uodatepackstatus\n";
    
} catch (PDOException $e) {
    echo "❌ 数据库错误: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
    exit(1);
}
