<?php
/**
 * 推荐奖励系统 - 基础功能测试
 * 
 * 测试内容:
 * 1. 数据库连接
 * 2. 表结构完整性
 * 3. 配置数据读取
 * 4. 基础CRUD操作
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

$prefix = 'yoshop_';

echo "========================================\n";
echo "推荐奖励系统 - 基础功能测试\n";
echo "========================================\n\n";

try {
    // 连接数据库
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ 数据库连接成功\n\n";
    
    // 测试1: 读取系统配置
    echo "[测试1] 读取系统配置\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->prepare("SELECT config_key, config_value FROM {$prefix}referral_system_config WHERE config_key = ?");
    $stmt->execute(['max_referral_levels']);
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "✓ 读取配置成功\n";
        echo "  最大推荐级数: {$config['config_value']}\n\n";
    } else {
        throw new Exception("配置读取失败");
    }
    
    // 测试2: 读取任务配置
    echo "[测试2] 读取任务配置\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM {$prefix}referral_task_config WHERE is_enabled = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✓ 启用的任务配置: {$result['count']} 个\n\n";
    
    // 测试3: 读取奖励配置
    echo "[测试3] 读取奖励配置\n";
    echo "----------------------------------------\n";
    
    $stmt = $pdo->query("SELECT config_name, reward_amount FROM {$prefix}referral_reward_config WHERE is_enabled = 1");
    $rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✓ 启用的奖励配置: " . count($rewards) . " 个\n";
    foreach ($rewards as $reward) {
        echo "  - {$reward['config_name']}: {$reward['reward_amount']}元\n";
    }
    echo "\n";
    
    // 测试4: 模拟插入推荐码 (测试后回滚)
    echo "[测试4] 测试推荐码表插入\n";
    echo "----------------------------------------\n";
    
    $pdo->beginTransaction();
    
    try {
        $testUserId = 999999;
        $testCode = 'TEST01';
        $currentTime = time();
        
        $stmt = $pdo->prepare("
            INSERT INTO {$prefix}user_referral_code 
            (user_id, referral_code, share_count, click_count, register_count, success_count, total_reward, create_time, update_time)
            VALUES (?, ?, 0, 0, 0, 0, 0.00, ?, ?)
        ");
        
        $stmt->execute([$testUserId, $testCode, $currentTime, $currentTime]);
        
        echo "✓ 推荐码插入成功\n";
        echo "  用户ID: {$testUserId}\n";
        echo "  推荐码: {$testCode}\n";
        
        // 验证插入
        $stmt = $pdo->prepare("SELECT * FROM {$prefix}user_referral_code WHERE user_id = ?");
        $stmt->execute([$testUserId]);
        $inserted = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($inserted && $inserted['referral_code'] === $testCode) {
            echo "✓ 数据验证成功\n";
        } else {
            throw new Exception("数据验证失败");
        }
        
        // 回滚测试数据
        $pdo->rollBack();
        echo "✓ 测试数据已回滚\n\n";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
    // 测试5: 测试推荐关系表插入
    echo "[测试5] 测试推荐关系表插入\n";
    echo "----------------------------------------\n";
    
    $pdo->beginTransaction();
    
    try {
        $testReferrerId = 999998;
        $testRefereeId = 999999;
        $testCode = 'TEST01';
        $currentTime = time();
        
        $stmt = $pdo->prepare("
            INSERT INTO {$prefix}referral_relation 
            (referrer_user_id, referee_user_id, referral_code, level, status, 
             referrer_task_status, referee_task_status, create_time, update_time)
            VALUES (?, ?, ?, 1, 1, 0, 0, ?, ?)
        ");
        
        $stmt->execute([$testReferrerId, $testRefereeId, $testCode, $currentTime, $currentTime]);
        
        echo "✓ 推荐关系插入成功\n";
        echo "  推荐人ID: {$testReferrerId}\n";
        echo "  被推荐人ID: {$testRefereeId}\n";
        echo "  推荐级别: 1级\n";
        
        // 回滚测试数据
        $pdo->rollBack();
        echo "✓ 测试数据已回滚\n\n";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
    // 测试6: 测试奖励记录表插入
    echo "[测试6] 测试奖励记录表插入\n";
    echo "----------------------------------------\n";
    
    $pdo->beginTransaction();
    
    try {
        $testRelationId = 1;
        $testUserId = 999999;
        $currentTime = time();
        
        $stmt = $pdo->prepare("
            INSERT INTO {$prefix}referral_reward 
            (relation_id, user_id, user_type, reward_type, reward_amount, status, create_time, update_time)
            VALUES (?, ?, 1, 1, 50.00, 1, ?, ?)
        ");
        
        $stmt->execute([$testRelationId, $testUserId, $currentTime, $currentTime]);
        
        echo "✓ 奖励记录插入成功\n";
        echo "  用户ID: {$testUserId}\n";
        echo "  奖励类型: 现金\n";
        echo "  奖励金额: 50.00元\n";
        
        // 回滚测试数据
        $pdo->rollBack();
        echo "✓ 测试数据已回滚\n\n";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
    echo "========================================\n";
    echo "✓ 所有测试通过!\n";
    echo "========================================\n\n";
    
    echo "测试结果:\n";
    echo "  ✓ 数据库连接正常\n";
    echo "  ✓ 配置数据读取正常\n";
    echo "  ✓ 推荐码表操作正常\n";
    echo "  ✓ 推荐关系表操作正常\n";
    echo "  ✓ 奖励记录表操作正常\n";
    echo "  ✓ 事务回滚正常\n\n";
    
    echo "数据库表已就绪,可以开始阶段2开发!\n";

} catch (PDOException $e) {
    echo "\n✗ 数据库错误: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n✗ 错误: " . $e->getMessage() . "\n";
    exit(1);
}
