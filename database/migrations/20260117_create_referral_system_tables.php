<?php

use think\migration\Migrator;
use think\migration\db\Column;

/**
 * 推荐奖励系统 - 数据库迁移
 * 
 * 创建日期: 2026-01-17
 * 版本: 1.0.0
 * 
 * 包含表:
 * 1. user_referral_code - 用户推荐码表
 * 2. referral_relation - 推荐关系表
 * 3. referral_reward - 推荐奖励记录表
 * 4. referral_task_config - 推荐任务配置表
 * 5. referral_reward_config - 推荐奖励配置表
 * 6. referral_system_config - 推荐系统配置表
 * 7. referral_leaderboard - 推荐排行榜表
 */
class CreateReferralSystemTables extends Migrator
{
    /**
     * 执行迁移
     */
    public function change()
    {
        // 1. 用户推荐码表
        $this->createUserReferralCodeTable();
        
        // 2. 推荐关系表
        $this->createReferralRelationTable();
        
        // 3. 推荐奖励记录表
        $this->createReferralRewardTable();
        
        // 4. 推荐任务配置表
        $this->createReferralTaskConfigTable();
        
        // 5. 推荐奖励配置表
        $this->createReferralRewardConfigTable();
        
        // 6. 推荐系统配置表
        $this->createReferralSystemConfigTable();
        
        // 7. 推荐排行榜表
        $this->createReferralLeaderboardTable();
    }
    
    /**
     * 创建用户推荐码表
     */
    protected function createUserReferralCodeTable()
    {
        $table = $this->table('user_referral_code', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
            'comment' => '用户推荐码表'
        ]);
        
        $table->addColumn('id', 'integer', [
                'limit' => 11,
                'signed' => false,
                'identity' => true,
                'comment' => '主键ID'
            ])
            ->addColumn('user_id', 'integer', [
                'limit' => 11,
                'signed' => false,
                'comment' => '用户ID'
            ])
            ->addColumn('referral_code', 'string', [
                'limit' => 8,
                'comment' => '推荐码(6-8位)'
            ])
            ->addColumn('share_count', 'integer', [
                'limit' => 11,
                'default' => 0,
                'comment' => '分享次数'
            ])
            ->addColumn('click_count', 'integer', [
                'limit' => 11,
                'default' => 0,
                'comment' => '点击次数'
            ])
            ->addColumn('register_count', 'integer', [
                'limit' => 11,
                'default' => 0,
                'comment' => '注册人数'
            ])
            ->addColumn('success_count', 'integer', [
                'limit' => 11,
                'default' => 0,
                'comment' => '成功推荐数'
            ])
            ->addColumn('total_reward', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'default' => '0.00',
                'comment' => '累计奖励金额'
            ])
            ->addColumn('create_time', 'integer', [
                'limit' => 11,
                'comment' => '创建时间'
            ])
            ->addColumn('update_time', 'integer', [
                'limit' => 11,
                'comment' => '更新时间'
            ])
            ->addIndex(['user_id'], ['unique' => true, 'name' => 'uk_user_id'])
            ->addIndex(['referral_code'], ['unique' => true, 'name' => 'uk_referral_code'])
            ->addIndex(['create_time'], ['name' => 'idx_create_time'])
            ->create();
    }
    
    /**
     * 创建推荐关系表
     */
    protected function createReferralRelationTable()
    {
        $table = $this->table('referral_relation', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
            'comment' => '推荐关系表'
        ]);
        
        $table->addColumn('id', 'integer', [
                'limit' => 11,
                'signed' => false,
                'identity' => true,
                'comment' => '主键ID'
            ])
            ->addColumn('referrer_user_id', 'integer', [
                'limit' => 11,
                'signed' => false,
                'comment' => '推荐人用户ID'
            ])
            ->addColumn('referee_user_id', 'integer', [
                'limit' => 11,
                'signed' => false,
                'comment' => '被推荐人用户ID'
            ])
            ->addColumn('referral_code', 'string', [
                'limit' => 8,
                'comment' => '使用的推荐码'
            ])
            ->addColumn('level', 'integer', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY,
                'default' => 1,
                'comment' => '推荐级别(1=一级,2=二级...)'
            ])
            ->addColumn('parent_relation_id', 'integer', [
                'limit' => 11,
                'signed' => false,
                'null' => true,
                'comment' => '上级推荐关系ID(用于多级)'
            ])
            ->addColumn('status', 'integer', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY,
                'default' => 1,
                'comment' => '状态(1=待完成,2=已完成,3=已失效)'
            ])
            ->addColumn('referrer_task_status', 'integer', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY,
                'default' => 0,
                'comment' => '推荐人任务状态(0=未完成,1=已完成)'
            ])
            ->addColumn('referee_task_status', 'integer', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY,
                'default' => 0,
                'comment' => '被推荐人任务状态(0=未完成,1=已完成)'
            ])
            ->addColumn('referrer_task_complete_time', 'integer', [
                'limit' => 11,
                'null' => true,
                'comment' => '推荐人任务完成时间'
            ])
            ->addColumn('referee_task_complete_time', 'integer', [
                'limit' => 11,
                'null' => true,
                'comment' => '被推荐人任务完成时间'
            ])
            ->addColumn('reward_issued', 'boolean', [
                'default' => 0,
                'comment' => '奖励是否已发放(0=否,1=是)'
            ])
            ->addColumn('reward_issue_time', 'integer', [
                'limit' => 11,
                'null' => true,
                'comment' => '奖励发放时间'
            ])
            ->addColumn('expire_time', 'integer', [
                'limit' => 11,
                'null' => true,
                'comment' => '失效时间'
            ])
            ->addColumn('create_time', 'integer', [
                'limit' => 11,
                'comment' => '创建时间'
            ])
            ->addColumn('update_time', 'integer', [
                'limit' => 11,
                'comment' => '更新时间'
            ])
            ->addIndex(['referee_user_id'], ['unique' => true, 'name' => 'uk_referee'])
            ->addIndex(['referrer_user_id'], ['name' => 'idx_referrer'])
            ->addIndex(['referral_code'], ['name' => 'idx_code'])
            ->addIndex(['status'], ['name' => 'idx_status'])
            ->addIndex(['parent_relation_id'], ['name' => 'idx_parent'])
            ->addIndex(['create_time'], ['name' => 'idx_create_time'])
            ->create();
    }
    
    /**
     * 创建推荐奖励记录表
     */
    protected function createReferralRewardTable()
    {
        $table = $this->table('referral_reward', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
            'comment' => '推荐奖励记录表'
        ]);
        
        $table->addColumn('id', 'integer', [
                'limit' => 11,
                'signed' => false,
                'identity' => true,
                'comment' => '主键ID'
            ])
            ->addColumn('relation_id', 'integer', [
                'limit' => 11,
                'signed' => false,
                'comment' => '推荐关系ID'
            ])
            ->addColumn('user_id', 'integer', [
                'limit' => 11,
                'signed' => false,
                'comment' => '获得奖励的用户ID'
            ])
            ->addColumn('user_type', 'integer', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY,
                'comment' => '用户类型(1=推荐人,2=被推荐人)'
            ])
            ->addColumn('reward_type', 'integer', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY,
                'comment' => '奖励类型(1=现金,2=积分,3=优惠券)'
            ])
            ->addColumn('reward_amount', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'comment' => '奖励金额/数量'
            ])
            ->addColumn('coupon_id', 'integer', [
                'limit' => 11,
                'null' => true,
                'comment' => '优惠券ID(如果是优惠券)'
            ])
            ->addColumn('status', 'integer', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY,
                'default' => 1,
                'comment' => '状态(1=待发放,2=已发放,3=已回收)'
            ])
            ->addColumn('issue_time', 'integer', [
                'limit' => 11,
                'null' => true,
                'comment' => '发放时间'
            ])
            ->addColumn('expire_time', 'integer', [
                'limit' => 11,
                'null' => true,
                'comment' => '过期时间'
            ])
            ->addColumn('recycle_time', 'integer', [
                'limit' => 11,
                'null' => true,
                'comment' => '回收时间'
            ])
            ->addColumn('recycle_reason', 'string', [
                'limit' => 255,
                'null' => true,
                'comment' => '回收原因'
            ])
            ->addColumn('create_time', 'integer', [
                'limit' => 11,
                'comment' => '创建时间'
            ])
            ->addColumn('update_time', 'integer', [
                'limit' => 11,
                'comment' => '更新时间'
            ])
            ->addIndex(['relation_id'], ['name' => 'idx_relation'])
            ->addIndex(['user_id'], ['name' => 'idx_user'])
            ->addIndex(['status'], ['name' => 'idx_status'])
            ->addIndex(['create_time'], ['name' => 'idx_create_time'])
            ->create();
    }
    
    /**
     * 创建推荐任务配置表
     */
    protected function createReferralTaskConfigTable()
    {
        $table = $this->table('referral_task_config', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
            'comment' => '推荐任务配置表'
        ]);
        
        $table->addColumn('id', 'integer', [
                'limit' => 11,
                'signed' => false,
                'identity' => true,
                'comment' => '主键ID'
            ])
            ->addColumn('config_name', 'string', [
                'limit' => 100,
                'comment' => '配置名称'
            ])
            ->addColumn('user_type', 'integer', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY,
                'comment' => '用户类型(1=推荐人,2=被推荐人)'
            ])
            ->addColumn('task_type', 'string', [
                'limit' => 50,
                'comment' => '任务类型(register/first_recharge/first_order/real_name等)'
            ])
            ->addColumn('task_params', 'text', [
                'null' => true,
                'comment' => '任务参数(JSON格式,如最低金额等)'
            ])
            ->addColumn('is_required', 'boolean', [
                'default' => 1,
                'comment' => '是否必须完成(1=是,0=否)'
            ])
            ->addColumn('sort_order', 'integer', [
                'limit' => 11,
                'default' => 0,
                'comment' => '排序'
            ])
            ->addColumn('is_enabled', 'boolean', [
                'default' => 1,
                'comment' => '是否启用'
            ])
            ->addColumn('create_time', 'integer', [
                'limit' => 11,
                'comment' => '创建时间'
            ])
            ->addColumn('update_time', 'integer', [
                'limit' => 11,
                'comment' => '更新时间'
            ])
            ->addIndex(['user_type'], ['name' => 'idx_user_type'])
            ->addIndex(['is_enabled'], ['name' => 'idx_enabled'])
            ->create();
    }
    
    /**
     * 创建推荐奖励配置表
     */
    protected function createReferralRewardConfigTable()
    {
        $table = $this->table('referral_reward_config', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
            'comment' => '推荐奖励配置表'
        ]);
        
        $table->addColumn('id', 'integer', [
                'limit' => 11,
                'signed' => false,
                'identity' => true,
                'comment' => '主键ID'
            ])
            ->addColumn('config_name', 'string', [
                'limit' => 100,
                'comment' => '配置名称'
            ])
            ->addColumn('level', 'integer', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY,
                'default' => 1,
                'comment' => '推荐级别(1=一级,2=二级...)'
            ])
            ->addColumn('user_type', 'integer', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY,
                'comment' => '用户类型(1=推荐人,2=被推荐人)'
            ])
            ->addColumn('reward_type', 'integer', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY,
                'comment' => '奖励类型(1=现金,2=积分,3=优惠券)'
            ])
            ->addColumn('reward_amount', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'comment' => '奖励金额/数量'
            ])
            ->addColumn('reward_ratio', 'decimal', [
                'precision' => 5,
                'scale' => 2,
                'default' => '100.00',
                'comment' => '奖励比例(%,用于多级推荐)'
            ])
            ->addColumn('expire_days', 'integer', [
                'limit' => 11,
                'null' => true,
                'comment' => '有效期(天数,NULL=永久)'
            ])
            ->addColumn('is_enabled', 'boolean', [
                'default' => 1,
                'comment' => '是否启用'
            ])
            ->addColumn('create_time', 'integer', [
                'limit' => 11,
                'comment' => '创建时间'
            ])
            ->addColumn('update_time', 'integer', [
                'limit' => 11,
                'comment' => '更新时间'
            ])
            ->addIndex(['level'], ['name' => 'idx_level'])
            ->addIndex(['is_enabled'], ['name' => 'idx_enabled'])
            ->create();
    }
    
    /**
     * 创建推荐系统配置表
     */
    protected function createReferralSystemConfigTable()
    {
        $table = $this->table('referral_system_config', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
            'comment' => '推荐系统配置表'
        ]);
        
        $table->addColumn('id', 'integer', [
                'limit' => 11,
                'signed' => false,
                'identity' => true,
                'comment' => '主键ID'
            ])
            ->addColumn('config_key', 'string', [
                'limit' => 100,
                'comment' => '配置键'
            ])
            ->addColumn('config_value', 'text', [
                'comment' => '配置值'
            ])
            ->addColumn('config_type', 'string', [
                'limit' => 20,
                'default' => 'string',
                'comment' => '配置类型(string/int/json等)'
            ])
            ->addColumn('description', 'string', [
                'limit' => 255,
                'null' => true,
                'comment' => '配置说明'
            ])
            ->addColumn('is_enabled', 'boolean', [
                'default' => 1,
                'comment' => '是否启用'
            ])
            ->addColumn('create_time', 'integer', [
                'limit' => 11,
                'comment' => '创建时间'
            ])
            ->addColumn('update_time', 'integer', [
                'limit' => 11,
                'comment' => '更新时间'
            ])
            ->addIndex(['config_key'], ['unique' => true, 'name' => 'uk_config_key'])
            ->create();
    }
    
    /**
     * 创建推荐排行榜表
     */
    protected function createReferralLeaderboardTable()
    {
        $table = $this->table('referral_leaderboard', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci',
            'comment' => '推荐排行榜表'
        ]);
        
        $table->addColumn('id', 'integer', [
                'limit' => 11,
                'signed' => false,
                'identity' => true,
                'comment' => '主键ID'
            ])
            ->addColumn('period_type', 'string', [
                'limit' => 20,
                'comment' => '周期类型(daily/weekly/monthly)'
            ])
            ->addColumn('period_date', 'date', [
                'comment' => '周期日期'
            ])
            ->addColumn('user_id', 'integer', [
                'limit' => 11,
                'signed' => false,
                'comment' => '用户ID'
            ])
            ->addColumn('referral_count', 'integer', [
                'limit' => 11,
                'default' => 0,
                'comment' => '推荐人数'
            ])
            ->addColumn('success_count', 'integer', [
                'limit' => 11,
                'default' => 0,
                'comment' => '成功推荐数'
            ])
            ->addColumn('rank', 'integer', [
                'limit' => 11,
                'default' => 0,
                'comment' => '排名'
            ])
            ->addColumn('reward_amount', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'default' => '0.00',
                'comment' => '排行榜奖励金额'
            ])
            ->addColumn('reward_issued', 'boolean', [
                'default' => 0,
                'comment' => '奖励是否已发放'
            ])
            ->addColumn('create_time', 'integer', [
                'limit' => 11,
                'comment' => '创建时间'
            ])
            ->addColumn('update_time', 'integer', [
                'limit' => 11,
                'comment' => '更新时间'
            ])
            ->addIndex(['period_type', 'period_date', 'user_id'], [
                'unique' => true, 
                'name' => 'uk_period_user'
            ])
            ->addIndex(['period_type', 'period_date'], ['name' => 'idx_period'])
            ->addIndex(['rank'], ['name' => 'idx_rank'])
            ->create();
    }
}
