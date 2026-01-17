-- --------------------------------------------------------
-- 面单打印记录表
-- 创建时间: 2026-01-17
-- --------------------------------------------------------

DROP TABLE IF EXISTS `yoshop_waybill_record`;
CREATE TABLE `yoshop_waybill_record` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `inpack_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '集运订单ID',
  `order_sn` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `express_type` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '快递公司类型 (zhongtong/shunfeng)',
  `express_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '快递公司名称',
  `waybill_no` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '运单号',
  `operation_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '操作类型 (1:打印 2:只下单)',
  `operator_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '操作人ID',
  `operator_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '操作人姓名',
  `print_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '打印时间',
  `api_response` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'API响应数据',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序ID',
  `created_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_inpack_id` (`inpack_id`) USING BTREE,
  INDEX `idx_order_sn` (`order_sn`) USING BTREE,
  INDEX `idx_waybill_no` (`waybill_no`) USING BTREE,
  INDEX `idx_created_time` (`created_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='面单打印记录表';
