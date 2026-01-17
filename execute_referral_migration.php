<?php
/**
 * 推荐奖励系统 - 数据库迁移执行脚本
 * 
 * 功能:
 * 1. 连接数据库
 * 2. 执行SQL迁移文件
 * 3. 验证表创建结果
 * 4. 显示详细执行日志
 */

// 数据库配置
$config = [
    'host' => '103.119.1.84',
    'database' => 'xinsuju',
    'username' => 'xinsuju',
    'password' => 'cJGzwZTDCLHzWXN4',
    'port' => '3306',
    'charset' => 'utf8mb4',
];

// 表前缀
$prefix = 'yoshop_';

// 需要创建的表列表
$tables = [
    'user_referral_code' => '用户推荐码表',
    'referral_relation' => '推荐关系表',
    'referral_reward' => '推荐奖励记录表',
    'referral_task_config' => '推荐任务配置表',
    'referral_reward_config' => '推荐奖励配置表',
    'referral_system_config' => '推荐系统配置表',
    'referral_leaderboard' => '推荐排行榜表',
];

echo "========================================\n";
echo "推荐奖励系统 - 数据库迁移\n";
echo "========================================\n\n";

try {
    // 1. 连接数据库
    echo "[1/4] 连接数据库...\n";
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ 数据库连接成功\n";
    echo "  - 主机: {$config['host']}\n";
    echo "  - 数据库: {$config['database']}\n\n";

    // 2. 读取SQL文件
    echo "[2/4] 读取SQL迁移文件...\n";
    $sqlFile = __DIR__ . '/create_referral_system_tables.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL文件不存在: {$sqlFile}");
    }
    
    $sql = file_get_contents($sqlFile);
    echo "✓ SQL文件读取成功\n";
    echo "  - 文件路径: {$sqlFile}\n";
    echo "  - 文件大小: " . number_format(strlen($sql)) . " 字节\n\n";

    // 3. 执行SQL语句
    echo "[3/4] 执行SQL迁移...\n";
    
    // 分割SQL语句(按分号分割,但忽略注释中的分号)
    $statements = [];
    $lines = explode("\n", $sql);
    $currentStatement = '';
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // 跳过注释和空行
        if (empty($line) || strpos($line, '--') === 0) {
            continue;
        }
        
        $currentStatement .= $line . ' ';
        
        // 如果行以分号结尾,表示一条完整的SQL语句
        if (substr($line, -1) === ';') {
            $statements[] = trim($currentStatement);
            $currentStatement = '';
        }
    }
    
    // 执行每条SQL语句
    $executedCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement)) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $executedCount++;
            
            // 显示执行的语句类型
            if (stripos($statement, 'DROP TABLE') !== false) {
                preg_match('/DROP TABLE.*?`([^`]+)`/i', $statement, $matches);
                if (isset($matches[1])) {
                    echo "  - 删除表: {$matches[1]}\n";
                }
            } elseif (stripos($statement, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE.*?`([^`]+)`/i', $statement, $matches);
                if (isset($matches[1])) {
                    echo "  ✓ 创建表: {$matches[1]}\n";
                }
            } elseif (stripos($statement, 'INSERT INTO') !== false) {
                preg_match('/INSERT INTO.*?`([^`]+)`/i', $statement, $matches);
                if (isset($matches[1])) {
                    echo "  + 插入数据: {$matches[1]}\n";
                }
            }
        } catch (PDOException $e) {
            $errorCount++;
            echo "  ✗ 执行失败: " . substr($statement, 0, 50) . "...\n";
            echo "    错误: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✓ SQL迁移执行完成\n";
    echo "  - 成功执行: {$executedCount} 条语句\n";
    echo "  - 执行失败: {$errorCount} 条语句\n\n";

    // 4. 验证表创建结果
    echo "[4/4] 验证表创建结果...\n";
    
    $allSuccess = true;
    foreach ($tables as $tableName => $description) {
        $fullTableName = $prefix . $tableName;
        
        // 检查表是否存在
        $stmt = $pdo->query("SHOW TABLES LIKE '{$fullTableName}'");
        $exists = $stmt->rowCount() > 0;
        
        if ($exists) {
            // 获取表的行数
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM `{$fullTableName}`");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $rowCount = $row['count'];
            
            echo "  ✓ {$description} ({$fullTableName})\n";
            echo "    - 记录数: {$rowCount}\n";
        } else {
            echo "  ✗ {$description} ({$fullTableName}) - 表不存在\n";
            $allSuccess = false;
        }
    }
    
    echo "\n========================================\n";
    if ($allSuccess) {
        echo "✓ 迁移成功! 所有表已创建\n";
        echo "========================================\n\n";
        
        // 显示初始配置数据
        echo "初始配置数据:\n";
        echo "----------------------------------------\n";
        
        // 系统配置
        $stmt = $pdo->query("SELECT config_key, config_value, description FROM `{$prefix}referral_system_config` WHERE is_enabled = 1");
        $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n系统配置:\n";
        foreach ($configs as $config) {
            echo "  - {$config['description']}: {$config['config_value']}\n";
        }
        
        // 任务配置
        $stmt = $pdo->query("SELECT config_name, task_type FROM `{$prefix}referral_task_config` WHERE is_enabled = 1");
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n任务配置:\n";
        foreach ($tasks as $task) {
            echo "  - {$task['config_name']} ({$task['task_type']})\n";
        }
        
        // 奖励配置
        $stmt = $pdo->query("SELECT config_name, reward_amount FROM `{$prefix}referral_reward_config` WHERE is_enabled = 1");
        $rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n奖励配置:\n";
        foreach ($rewards as $reward) {
            echo "  - {$reward['config_name']}: {$reward['reward_amount']} 元\n";
        }
        
        echo "\n========================================\n";
        echo "下一步:\n";
        echo "1. 检查配置数据是否符合需求\n";
        echo "2. 根据需要调整奖励金额和任务要求\n";
        echo "3. 开始实施阶段2: 后端核心服务开发\n";
        echo "========================================\n";
    } else {
        echo "✗ 迁移失败! 部分表创建失败\n";
        echo "========================================\n";
        echo "请检查错误信息并重新执行\n";
    }

} catch (PDOException $e) {
    echo "\n✗ 数据库错误: " . $e->getMessage() . "\n";
    echo "========================================\n";
    exit(1);
} catch (Exception $e) {
    echo "\n✗ 错误: " . $e->getMessage() . "\n";
    echo "========================================\n";
    exit(1);
}
