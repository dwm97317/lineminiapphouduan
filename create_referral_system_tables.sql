-- ============================================
-- 推荐奖励系统 - 数据库表结构
-- 创建日期: 2026-01-17
-- 数据库: xinsuju
-- 表前缀: yoshop_
-- ============================================

-- 设置字符集
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- 1. 用户推荐码表 (user_referral_code)
-- ============================================
DROP TABLE IF EXISTS `yoshop_user_referral_code`;
CREATE TABLE `yoshop_user_referral_code` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `referral_code` varchar(8) NOT NULL COMMENT '推荐码(6-8位)',
  `share_count` int(11) DEFAULT 0 COMMENT '分享次数',
  `click_count` int(11) DEFAULT 0 COMMENT '点击次数',
  `register_count` int(11) DEFAULT 0 COMMENT '注册人数',
  `success_count` int(11) DEFAULT 0 COMMENT '成功推荐数',
  `total_reward` decimal(10,2) DEFAULT 0.00 COMMENT '累计奖励金额',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_id` (`user_id`),
  UNIQUE KEY `uk_referral_code` (`referral_code`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户推荐码表';

-- ============================================
-- 2. 推荐关系表 (referral_relation)
-- ============================================
DROP TABLE IF EXISTS `yoshop_referral_relation`;
CREATE TABLE `yoshop_referral_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `referrer_user_id` int(11) unsigned NOT NULL COMMENT '推荐人用户ID',
  `referee_user_id` int(11) unsigned NOT NULL COMMENT '被推荐人用户ID',
  `referral_code` varchar(8) NOT NULL COMMENT '使用的推荐码',
  `level` tinyint(2) DEFAULT 1 COMMENT '推荐级别(1=一级,2=二级...)',
  `parent_relation_id` int(11) unsigned DEFAULT NULL COMMENT '上级推荐关系ID(用于多级)',
  `status` tinyint(2) DEFAULT 1 COMMENT '状态(1=待完成,2=已完成,3=已失效)',
  `referrer_task_status` tinyint(2) DEFAULT 0 COMMENT '推荐人任务状态(0=未完成,1=已完成)',
  `referee_task_status` tinyint(2) DEFAULT 0 COMMENT '被推荐人任务状态(0=未完成,1=已完成)',
  `referrer_task_complete_time` int(11) DEFAULT NULL COMMENT '推荐人任务完成时间',
  `referee_task_complete_time` int(11) DEFAULT NULL COMMENT '被推荐人任务完成时间',
  `reward_issued` tinyint(1) DEFAULT 0 COMMENT '奖励是否已发放(0=否,1=是)',
  `reward_issue_time` int(11) DEFAULT NULL COMMENT '奖励发放时间',
  `expire_time` int(11) DEFAULT NULL COMMENT '失效时间',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_referee` (`referee_user_id`),
  KEY `idx_referrer` (`referrer_user_id`),
  KEY `idx_code` (`referral_code`),
  KEY `idx_status` (`status`),
  KEY `idx_parent` (`parent_relation_id`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='推荐关系表';

-- ============================================
-- 3. 推荐奖励记录表 (referral_reward)
-- ============================================
DROP TABLE IF EXISTS `yoshop_referral_reward`;
CREATE TABLE `yoshop_referral_reward` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `relation_id` int(11) unsigned NOT NULL COMMENT '推荐关系ID',
  `user_id` int(11) unsigned NOT NULL COMMENT '获得奖励的用户ID',
  `user_type` tinyint(2) NOT NULL COMMENT '用户类型(1=推荐人,2=被推荐人)',
  `reward_type` tinyint(2) NOT NULL COMMENT '奖励类型(1=现金,2=积分,3=优惠券)',
  `reward_amount` decimal(10,2) NOT NULL COMMENT '奖励金额/数量',
  `coupon_id` int(11) DEFAULT NULL COMMENT '优惠券ID(如果是优惠券)',
  `status` tinyint(2) DEFAULT 1 COMMENT '状态(1=待发放,2=已发放,3=已回收)',
  `issue_time` int(11) DEFAULT NULL COMMENT '发放时间',
  `expire_time` int(11) DEFAULT NULL COMMENT '过期时间',
  `recycle_time` int(11) DEFAULT NULL COMMENT '回收时间',
  `recycle_reason` varchar(255) DEFAULT NULL COMMENT '回收原因',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_relation` (`relation_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='推荐奖励记录表';

-- ============================================
-- 4. 推荐任务配置表 (referral_task_config)
-- ============================================
DROP TABLE IF EXISTS `yoshop_referral_task_config`;
CREATE TABLE `yoshop_referral_task_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `config_name` varchar(100) NOT NULL COMMENT '配置名称',
  `user_type` tinyint(2) NOT NULL COMMENT '用户类型(1=推荐人,2=被推荐人)',
  `task_type` varchar(50) NOT NULL COMMENT '任务类型(register/first_recharge/first_order/real_name等)',
  `task_params` text COMMENT '任务参数(JSON格式,如最低金额等)',
  `is_required` tinyint(1) DEFAULT 1 COMMENT '是否必须完成(1=是,0=否)',
  `sort_order` int(11) DEFAULT 0 COMMENT '排序',
  `is_enabled` tinyint(1) DEFAULT 1 COMMENT '是否启用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_type` (`user_type`),
  KEY `idx_enabled` (`is_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='推荐任务配置表';

-- ============================================
-- 5. 推荐奖励配置表 (referral_reward_config)
-- ============================================
DROP TABLE IF EXISTS `yoshop_referral_reward_config`;
CREATE TABLE `yoshop_referral_reward_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `config_name` varchar(100) NOT NULL COMMENT '配置名称',
  `level` tinyint(2) DEFAULT 1 COMMENT '推荐级别(1=一级,2=二级...)',
  `user_type` tinyint(2) NOT NULL COMMENT '用户类型(1=推荐人,2=被推荐人)',
  `reward_type` tinyint(2) NOT NULL COMMENT '奖励类型(1=现金,2=积分,3=优惠券)',
  `reward_amount` decimal(10,2) NOT NULL COMMENT '奖励金额/数量',
  `reward_ratio` decimal(5,2) DEFAULT 100.00 COMMENT '奖励比例(%,用于多级推荐)',
  `expire_days` int(11) DEFAULT NULL COMMENT '有效期(天数,NULL=永久)',
  `is_enabled` tinyint(1) DEFAULT 1 COMMENT '是否启用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_level` (`level`),
  KEY `idx_enabled` (`is_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='推荐奖励配置表';

-- ============================================
-- 6. 推荐系统配置表 (referral_system_config)
-- ============================================
DROP TABLE IF EXISTS `yoshop_referral_system_config`;
CREATE TABLE `yoshop_referral_system_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL COMMENT '配置键',
  `config_value` text NOT NULL COMMENT '配置值',
  `config_type` varchar(20) DEFAULT 'string' COMMENT '配置类型(string/int/json等)',
  `description` varchar(255) DEFAULT NULL COMMENT '配置说明',
  `is_enabled` tinyint(1) DEFAULT 1 COMMENT '是否启用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='推荐系统配置表';

-- ============================================
-- 7. 推荐排行榜表 (referral_leaderboard)
-- ============================================
DROP TABLE IF EXISTS `yoshop_referral_leaderboard`;
CREATE TABLE `yoshop_referral_leaderboard` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `period_type` varchar(20) NOT NULL COMMENT '周期类型(daily/weekly/monthly)',
  `period_date` date NOT NULL COMMENT '周期日期',
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `referral_count` int(11) DEFAULT 0 COMMENT '推荐人数',
  `success_count` int(11) DEFAULT 0 COMMENT '成功推荐数',
  `rank` int(11) DEFAULT 0 COMMENT '排名',
  `reward_amount` decimal(10,2) DEFAULT 0.00 COMMENT '排行榜奖励金额',
  `reward_issued` tinyint(1) DEFAULT 0 COMMENT '奖励是否已发放',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_period_user` (`period_type`,`period_date`,`user_id`),
  KEY `idx_period` (`period_type`,`period_date`),
  KEY `idx_rank` (`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='推荐排行榜表';

-- ============================================
-- 初始化系统配置数据
-- ============================================
INSERT INTO `yoshop_referral_system_config` (`config_key`, `config_value`, `config_type`, `description`, `is_enabled`, `create_time`, `update_time`) VALUES
('max_referral_levels', '1', 'int', '最大推荐级数(1/2/3等)', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('referral_code_length', '6', 'int', '推荐码长度(6-8)', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('referral_limit_enabled', '0', 'int', '是否启用推荐上限(0=否,1=是)', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('referral_limit_per_month', '100', 'int', '每月推荐上限', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('expire_days', '30', 'int', '推荐关系失效天数', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('leaderboard_enabled', '1', 'int', '是否启用排行榜(0=否,1=是)', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('leaderboard_top_count', '100', 'int', '排行榜显示人数', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('anti_fraud_enabled', '1', 'int', '是否启用防刷机制(0=否,1=是)', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- ============================================
-- 初始化任务配置数据(示例)
-- ============================================
INSERT INTO `yoshop_referral_task_config` (`config_name`, `user_type`, `task_type`, `task_params`, `is_required`, `sort_order`, `is_enabled`, `create_time`, `update_time`) VALUES
('被推荐人-完成注册', 2, 'register', NULL, 1, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('被推荐人-完成首次充值', 2, 'first_recharge', '{"min_amount": 100}', 1, 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('推荐人-邀请成功', 1, 'invite_success', NULL, 1, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- ============================================
-- 初始化奖励配置数据(示例)
-- ============================================
INSERT INTO `yoshop_referral_reward_config` (`config_name`, `level`, `user_type`, `reward_type`, `reward_amount`, `reward_ratio`, `expire_days`, `is_enabled`, `create_time`, `update_time`) VALUES
('一级推荐-推荐人现金奖励', 1, 1, 1, 50.00, 100.00, NULL, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('一级推荐-被推荐人现金奖励', 1, 2, 1, 30.00, 100.00, NULL, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- 执行完成提示
-- ============================================
-- 表创建完成!
-- 请使用以下命令执行此SQL文件:
-- mysql -h 103.119.1.84 -u xinsuju -p xinsuju < create_referral_system_tables.sql
-- ============================================
