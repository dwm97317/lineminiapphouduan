-- ============================================
-- Table structure for platform_account
-- 平台账户绑定表 (FB/IG Bot Customer ID 关联)
-- ============================================

DROP TABLE IF EXISTS `yoshop_platform_account`;

CREATE TABLE `yoshop_platform_account` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键 ID',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户 ID (关联 yoshop_user.user_id)',
  `platform_type` varchar(20) NOT NULL DEFAULT 'FACEBOOK' COMMENT '平台类型：FACEBOOK, INSTAGRAM',
  `customer_id` varchar(100) NOT NULL COMMENT 'Bot Customer ID (来自 FB/IG Bot)',
  `customer_name` varchar(255) DEFAULT NULL COMMENT '客户名称 (用于显示验证结果)',
  `is_anonymized` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否匿名化显示：1=是，0=否',
  `binding_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '绑定时间',
  `last_verify_time` timestamp NULL DEFAULT NULL COMMENT '最后验证时间',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态：1=有效，0=无效',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT '10001' COMMENT '小程序/商户 ID',
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_customer_id` (`customer_id`,`wxapp_id`) USING BTREE COMMENT 'Customer ID 唯一索引',
  UNIQUE KEY `uk_user_platform` (`user_id`,`platform_type`,`wxapp_id`) USING BTREE COMMENT '每个平台只能绑定一个 Customer ID',
  KEY `idx_user_id` (`user_id`) USING BTREE,
  KEY `idx_platform_type` (`platform_type`) USING BTREE,
  KEY `idx_wxapp_id` (`wxapp_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='平台账户绑定表 (FB/IG Bot)';

-- ============================================
-- Sample data (for testing)
-- ============================================
-- INSERT INTO `yoshop_platform_account` 
-- (`user_id`, `platform_type`, `customer_id`, `customer_name`, `is_anonymized`, `wxapp_id`) 
-- VALUES 
-- (1, 'FACEBOOK', 'CUST_123456', 'John D***', 1, 10001);
