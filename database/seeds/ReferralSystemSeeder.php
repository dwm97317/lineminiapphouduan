<?php

use think\migration\Seeder;

/**
 * 推荐奖励系统 - 初始数据填充
 * 
 * 创建日期: 2026-01-17
 * 版本: 1.0.0
 */
class ReferralSystemSeeder extends Seeder
{
    /**
     * 执行数据填充
     */
    public function run()
    {
        // 1. 填充系统配置
        $this->seedSystemConfig();
        
        // 2. 填充任务配置
        $this->seedTaskConfig();
        
        // 3. 填充奖励配置
        $this->seedRewardConfig();
    }
    
    /**
     * 填充系统配置
     */
    protected function seedSystemConfig()
    {
        $data = [
            [
                'config_key' => 'max_referral_levels',
                'config_value' => '1',
                'config_type' => 'int',
                'description' => '最大推荐级数(1/2/3等)',
                'is_enabled' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ],
            [
                'config_key' => 'referral_code_length',
                'config_value' => '6',
                'config_type' => 'int',
                'description' => '推荐码长度(6-8)',
                'is_enabled' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ],
            [
                'config_key' => 'referral_limit_enabled',
                'config_value' => '0',
                'config_type' => 'int',
                'description' => '是否启用推荐上限(0=否,1=是)',
                'is_enabled' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ],
            [
                'config_key' => 'referral_limit_per_month',
                'config_value' => '100',
                'config_type' => 'int',
                'description' => '每月推荐上限',
                'is_enabled' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ],
            [
                'config_key' => 'expire_days',
                'config_value' => '30',
                'config_type' => 'int',
                'description' => '推荐关系失效天数',
                'is_enabled' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ],
            [
                'config_key' => 'leaderboard_enabled',
                'config_value' => '1',
                'config_type' => 'int',
                'description' => '是否启用排行榜(0=否,1=是)',
                'is_enabled' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ],
            [
                'config_key' => 'leaderboard_top_count',
                'config_value' => '100',
                'config_type' => 'int',
                'description' => '排行榜显示人数',
                'is_enabled' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ],
            [
                'config_key' => 'anti_fraud_enabled',
                'config_value' => '1',
                'config_type' => 'int',
                'description' => '是否启用防刷机制(0=否,1=是)',
                'is_enabled' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ],
        ];
        
        $this->table('referral_system_config')->insert($data)->save();
        
        echo "✓ 系统配置数据填充完成 (" . count($data) . " 条)\n";
    }
    
    /**
     * 填充任务配置
     */
    protected function seedTaskConfig()
    {
        $data = [
            [
                'config_name' => '被推荐人-完成注册',
                'user_type' => 2,
                'task_type' => 'register',
                'task_params' => null,
                'is_required' => 1,
                'sort_order' => 1,
                'is_enabled' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ],
            [
                'config_name' => '被推荐人-完成首次充值',
                'user_type' => 2,
                'task_type' => 'first_recharge',
                'task_params' => json_encode(['min_amount' => 100]),
                'is_required' => 1,
                'sort_order' => 2,
                'is_enabled' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ],
            [
                'config_name' => '推荐人-邀请成功',
                'user_type' => 1,
                'task_type' => 'invite_success',
                'task_params' => null,
                'is_required' => 1,
                'sort_order' => 1,
                'is_enabled' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ],
        ];
        
        $this->table('referral_task_config')->insert($data)->save();
        
        echo "✓ 任务配置数据填充完成 (" . count($data) . " 条)\n";
    }
    
    /**
     * 填充奖励配置
     */
    protected function seedRewardConfig()
    {
        $data = [
            [
                'config_name' => '一级推荐-推荐人现金奖励',
                'level' => 1,
                'user_type' => 1,
                'reward_type' => 1,
                'reward_amount' => '50.00',
                'reward_ratio' => '100.00',
                'expire_days' => null,
                'is_enabled' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ],
            [
                'config_name' => '一级推荐-被推荐人现金奖励',
                'level' => 1,
                'user_type' => 2,
                'reward_type' => 1,
                'reward_amount' => '30.00',
                'reward_ratio' => '100.00',
                'expire_days' => null,
                'is_enabled' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ],
        ];
        
        $this->table('referral_reward_config')->insert($data)->save();
        
        echo "✓ 奖励配置数据填充完成 (" . count($data) . " 条)\n";
    }
}
