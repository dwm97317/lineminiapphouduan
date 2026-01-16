-- 创建转账充值申请表
CREATE TABLE IF NOT EXISTS `yoshop_recharge_apply` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '申请ID',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `transfer_date` varchar(20) NOT NULL DEFAULT '' COMMENT '转账日期',
  `transfer_time` varchar(20) NOT NULL DEFAULT '' COMMENT '转账时间',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '充值金额',
  `screenshots` text COMMENT '转账截图(JSON数组)',
  `remarks` varchar(500) DEFAULT '' COMMENT '备注',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态: 0=待审核, 1=已通过, 2=已拒绝',
  `admin_remark` varchar(500) DEFAULT '' COMMENT '管理员备注',
  `reviewed_by` int(11) unsigned DEFAULT NULL COMMENT '审核人ID',
  `reviewed_time` int(11) unsigned DEFAULT NULL COMMENT '审核时间',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='转账充值申请表';
