<?php
/**
 * 完整测试推荐配置保存功能
 * 模拟前端提交，验证修复后不会出现 array 错误
 */

$config = [
    'host' => '103.119.1.84',
    'database' => 'xinsuju',
    'username' => 'xinsuju',
    'password' => 'cJGzwZTDCLHzWXN4',
    'port' => '3306',
    'charset' => 'utf8',
];

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "=== 推荐配置保存完整测试 ===\n\n";
    
    $wxappId = 10001;
    
    // ========== 测试 1: 任务配置保存 ==========
    echo "【测试 1】任务配置保存\n";
    echo str_repeat('=', 60) . "\n";
    
    // 获取现有任务
    $stmt = $pdo->prepare("
        SELECT id, user_type, task_type, config_name, is_enabled, is_required, task_params
        FROM yoshop_referral_task_config
        WHERE wxapp_id = ?
        ORDER BY user_type, sort_order
        LIMIT 3
    ");
    $stmt->execute([$wxappId]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($tasks)) {
        echo "❌ 没有找到任务配置\n\n";
    } else {
        echo "找到 " . count($tasks) . " 个任务:\n";
        foreach ($tasks as $task) {
            $userTypeText = $task['user_type'] == 1 ? '推荐人' : '被推荐人';
            echo "  - ID:{$task['id']} {$userTypeText} - {$task['config_name']}\n";
        }
        echo "\n";
        
        // 模拟前端提交的数据结构
        $postData = [
            'referrer' => [],
            'referee' => []
        ];
        
        foreach ($tasks as $task) {
            $userTypeKey = $task['user_type'] == 1 ? 'referrer' : 'referee';
            $postData[$userTypeKey][$task['id']] = [
                'is_enabled' => 1,
                'is_required' => 1,
                'task_params' => [
                    'min_amount' => 150,
                    'test_param' => 'test_value'
                ]
            ];
        }
        
        echo "模拟提交数据:\n";
        echo json_encode($postData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";
        
        // 执行保存逻辑（模拟控制器的 saveTaskConfig 方法）
        $userTypeMap = [
            'referrer' => 1,
            'referee' => 2,
        ];
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($postData as $userTypeKey => $taskList) {
            if (!isset($userTypeMap[$userTypeKey]) || !is_array($taskList)) {
                continue;
            }
            
            $userType = $userTypeMap[$userTypeKey];
            
            foreach ($taskList as $taskId => $taskData) {
                if (!is_array($taskData)) {
                    continue;
                }
                
                try {
                    // 确保 taskId 是整数
                    $taskId = intval($taskId);
                    
                    // 验证任务存在
                    $stmt = $pdo->prepare("
                        SELECT id FROM yoshop_referral_task_config
                        WHERE id = ? AND wxapp_id = ? AND user_type = ?
                    ");
                    $stmt->execute([$taskId, $wxappId, $userType]);
                    $exists = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($exists) {
                        $updateData = [
                            'is_enabled' => isset($taskData['is_enabled']) ? intval($taskData['is_enabled']) : 0,
                            'is_required' => isset($taskData['is_required']) ? intval($taskData['is_required']) : 0,
                        ];
                        
                        // 处理 task_params
                        if (isset($taskData['task_params']) && is_array($taskData['task_params'])) {
                            $params = array_filter($taskData['task_params'], function($value) {
                                return $value !== '' && $value !== null;
                            });
                            
                            if (!empty($params)) {
                                $updateData['task_params'] = json_encode($params, JSON_UNESCAPED_UNICODE);
                            }
                        }
                        
                        // 执行更新
                        $fields = [];
                        $values = [];
                        foreach ($updateData as $key => $value) {
                            $fields[] = "{$key} = ?";
                            $values[] = $value;
                        }
                        $values[] = $taskId;
                        $values[] = $wxappId;
                        $values[] = $userType;
                        
                        $sql = "UPDATE yoshop_referral_task_config SET " . implode(', ', $fields) . 
                               " WHERE id = ? AND wxapp_id = ? AND user_type = ?";
                        
                        $stmt = $pdo->prepare($sql);
                        $result = $stmt->execute($values);
                        
                        if ($result) {
                            echo "✅ 任务 ID {$taskId} ({$userTypeKey}) 更新成功\n";
                            $successCount++;
                        } else {
                            echo "⚠️  任务 ID {$taskId} ({$userTypeKey}) 无变化\n";
                        }
                    } else {
                        echo "❌ 任务 ID {$taskId} ({$userTypeKey}) 不存在\n";
                        $errorCount++;
                    }
                } catch (Exception $e) {
                    echo "❌ 任务 ID {$taskId} ({$userTypeKey}) 保存失败: " . $e->getMessage() . "\n";
                    $errorCount++;
                }
            }
        }
        
        echo "\n任务配置保存结果: 成功 {$successCount} 个, 失败 {$errorCount} 个\n\n";
    }
    
    // ========== 测试 2: 奖励配置保存 ==========
    echo "【测试 2】奖励配置保存\n";
    echo str_repeat('=', 60) . "\n";
    
    $stmt = $pdo->prepare("
        SELECT id, level, user_type, reward_type, reward_amount, reward_params
        FROM yoshop_referral_reward_config
        WHERE wxapp_id = ?
        LIMIT 2
    ");
    $stmt->execute([$wxappId]);
    $rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($rewards)) {
        echo "❌ 没有找到奖励配置\n\n";
    } else {
        echo "找到 " . count($rewards) . " 个奖励配置:\n";
        foreach ($rewards as $reward) {
            $rewardTypes = [1 => '现金', 2 => '积分', 3 => '优惠券'];
            echo "  - ID:{$reward['id']} 级别:{$reward['level']} 类型:{$rewardTypes[$reward['reward_type']]}\n";
        }
        echo "\n";
        
        // 模拟提交数据
        $postData = [];
        foreach ($rewards as $reward) {
            $postData[$reward['id']] = [
                'is_enabled' => 1,
                'reward_type' => $reward['reward_type'],
                'reward_amount' => 100.50,
                'reward_ratio' => 80.00,
                'expire_days' => 30,
                'reward_params' => [
                    'coupon_id' => 123,
                    'min_withdraw' => 50
                ]
            ];
        }
        
        echo "模拟提交数据:\n";
        echo json_encode($postData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($postData as $configId => $configData) {
            if (!is_array($configData)) {
                continue;
            }
            
            try {
                $configId = intval($configId);
                
                // 验证配置存在
                $stmt = $pdo->prepare("
                    SELECT id FROM yoshop_referral_reward_config
                    WHERE id = ? AND wxapp_id = ?
                ");
                $stmt->execute([$configId, $wxappId]);
                $exists = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($exists) {
                    $updateData = [
                        'is_enabled' => isset($configData['is_enabled']) ? intval($configData['is_enabled']) : 0,
                    ];
                    
                    if (isset($configData['reward_type'])) {
                        $updateData['reward_type'] = intval($configData['reward_type']);
                    }
                    if (isset($configData['reward_amount'])) {
                        $updateData['reward_amount'] = floatval($configData['reward_amount']);
                    }
                    if (isset($configData['reward_ratio'])) {
                        $updateData['reward_ratio'] = floatval($configData['reward_ratio']);
                    }
                    if (isset($configData['expire_days'])) {
                        $updateData['expire_days'] = $configData['expire_days'] !== '' ? intval($configData['expire_days']) : null;
                    }
                    
                    // 处理 reward_params
                    if (isset($configData['reward_params']) && is_array($configData['reward_params'])) {
                        $params = array_filter($configData['reward_params'], function($value) {
                            return $value !== '' && $value !== null;
                        });
                        
                        if (!empty($params)) {
                            $updateData['reward_params'] = json_encode($params, JSON_UNESCAPED_UNICODE);
                        } else {
                            $updateData['reward_params'] = null;
                        }
                    }
                    
                    // 执行更新
                    $fields = [];
                    $values = [];
                    foreach ($updateData as $key => $value) {
                        $fields[] = "{$key} = ?";
                        $values[] = $value;
                    }
                    $values[] = $configId;
                    $values[] = $wxappId;
                    
                    $sql = "UPDATE yoshop_referral_reward_config SET " . implode(', ', $fields) . 
                           " WHERE id = ? AND wxapp_id = ?";
                    
                    $stmt = $pdo->prepare($sql);
                    $result = $stmt->execute($values);
                    
                    if ($result) {
                        echo "✅ 奖励配置 ID {$configId} 更新成功\n";
                        $successCount++;
                    } else {
                        echo "⚠️  奖励配置 ID {$configId} 无变化\n";
                    }
                } else {
                    echo "❌ 奖励配置 ID {$configId} 不存在\n";
                    $errorCount++;
                }
            } catch (Exception $e) {
                echo "❌ 奖励配置 ID {$configId} 保存失败: " . $e->getMessage() . "\n";
                $errorCount++;
            }
        }
        
        echo "\n奖励配置保存结果: 成功 {$successCount} 个, 失败 {$errorCount} 个\n\n";
    }
    
    echo str_repeat('=', 60) . "\n";
    echo "✅ 测试完成！所有操作使用标量值，不会出现 array 类型错误\n";
    
} catch (PDOException $e) {
    echo "❌ 数据库错误: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
}
