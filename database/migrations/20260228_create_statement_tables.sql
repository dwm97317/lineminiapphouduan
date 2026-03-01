-- ============================================
-- 账单系统数据库迁移脚本
-- 创建时间: 2026-02-28
-- 说明: 创建账单相关表和索引
-- ============================================

-- 1. 账单表
CREATE TABLE IF NOT EXISTS `yoshop_statement` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '账单ID',
  `statement_no` varchar(20) NOT NULL COMMENT '账单编号',
  `member_id` int(11) unsigned NOT NULL COMMENT '客户ID',
  `member_name` varchar(50) NOT NULL COMMENT '客户姓名',
  `start_date` date DEFAULT NULL COMMENT '账单开始日期',
  `end_date` date DEFAULT NULL COMMENT '账单结束日期',
  `total_packages` int(11) NOT NULL DEFAULT '0' COMMENT '订单数量',
  `total_weight` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总重量(KG)',
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总金额(元)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态(1正常 2已作废)',
  `pay_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '支付状态(1未支付 2已支付)',
  `pay_time` datetime DEFAULT NULL COMMENT '支付时间',
  `pay_remark` varchar(255) DEFAULT NULL COMMENT '支付备注',
  `excel_path` varchar(255) DEFAULT NULL COMMENT 'Excel文件路径',
  `wxapp_id` int(11) unsigned NOT NULL COMMENT '小程序ID',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_statement_no` (`statement_no`),
  KEY `idx_member` (`member_id`, `wxapp_id`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='账单表';

-- 2. 财务配置表
CREATE TABLE IF NOT EXISTS `yoshop_finance_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `member_id` int(11) unsigned DEFAULT NULL COMMENT '客户ID(NULL为全局配置)',
  `price_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '计价方式(1固定 2阶梯 3线路 4区间 5公式)',
  `unit_price` decimal(10,2) DEFAULT NULL COMMENT '固定单价',
  `price_tier_json` text COMMENT '阶梯价格JSON',
  `price_line_json` text COMMENT '线路价格JSON',
  `price_range_json` text COMMENT '区间价格JSON',
  `price_formula` varchar(255) DEFAULT NULL COMMENT '自定义公式',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态(0禁用 1启用)',
  `wxapp_id` int(11) unsigned NOT NULL COMMENT '小程序ID',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_member` (`member_id`, `wxapp_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='财务配置表';

-- 3. 历史单价表
CREATE TABLE IF NOT EXISTS `yoshop_history_price` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `member_id` int(11) unsigned NOT NULL COMMENT '客户ID',
  `unit_price` decimal(10,2) NOT NULL COMMENT '单价',
  `wxapp_id` int(11) unsigned NOT NULL COMMENT '小程序ID',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_member` (`member_id`, `wxapp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='历史单价表';

-- 4. Excel模板配置表
CREATE TABLE IF NOT EXISTS `yoshop_statement_template` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '模板ID',
  `template_name` varchar(50) NOT NULL COMMENT '模板名称',
  `title` varchar(100) DEFAULT NULL COMMENT '账单标题',
  `logo_path` varchar(255) DEFAULT NULL COMMENT 'LOGO路径',
  `alipay_qr_path` varchar(255) DEFAULT NULL COMMENT '支付宝二维码路径',
  `wechat_qr_path` varchar(255) DEFAULT NULL COMMENT '微信二维码路径',
  `notice_text` text COMMENT '温馨提示文本',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认模板',
  `wxapp_id` int(11) unsigned NOT NULL COMMENT '小程序ID',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_wxapp` (`wxapp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='账单模板表';

-- 5. 修改订单表：添加账单关联字段
ALTER TABLE `yoshop_package` 
ADD COLUMN `statement_id` int(11) unsigned DEFAULT NULL COMMENT '账单ID' AFTER `is_pay`;

-- 添加索引
ALTER TABLE `yoshop_package` 
ADD INDEX `idx_statement` (`statement_id`);

-- 6. 修改集运订单表：添加账单关联字段
ALTER TABLE `yoshop_inpack` 
ADD COLUMN `statement_id` int(11) unsigned DEFAULT NULL COMMENT '账单ID' AFTER `batch_id`;

-- 添加索引
ALTER TABLE `yoshop_inpack` 
ADD INDEX `idx_statement` (`statement_id`);

-- 7. 插入全局默认配置
INSERT INTO `yoshop_finance_config` 
(`member_id`, `price_type`, `unit_price`, `status`, `wxapp_id`, `create_time`, `update_time`)
VALUES 
(NULL, 1, 46.00, 1, 10001, NOW(), NOW());

-- 8. 插入默认Excel模板
INSERT INTO `yoshop_statement_template` 
(`template_name`, `title`, `notice_text`, `is_default`, `wxapp_id`, `create_time`, `update_time`)
VALUES 
('默认模板', '集运订单对账单', '请核对账单信息，如有疑问请及时联系客服。', 1, 10001, NOW(), NOW());

-- ============================================
-- 迁移完成
-- ============================================
