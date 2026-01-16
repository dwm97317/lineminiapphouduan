/*
 Navicat Premium Data Transfer

 Source Server         : 101.33.208.14 [zalo]
 Source Server Type    : MySQL
 Source Server Version : 50650
 Source Host           : 101.33.208.14:3306
 Source Schema         : zhuanyun_sllowly

 Target Server Type    : MySQL
 Target Server Version : 50650
 File Encoding         : 65001

 Date: 19/01/2024 17:34:02
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for yoshop_admin_user
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_admin_user`;
CREATE TABLE `yoshop_admin_user`  (
  `admin_user_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `user_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '登录密码',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`admin_user_id`) USING BTREE,
  INDEX `user_name`(`user_name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10002 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '超管用户记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_ailog
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_ailog`;
CREATE TABLE `yoshop_ailog`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '日志内容',
  `user_id` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '识别的用户编号',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4163 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_apipost
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_apipost`;
CREATE TABLE `yoshop_apipost`  (
  `api_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `api_content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `api_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `create_time` int(11) UNSIGNED NULL DEFAULT 0,
  `update_time` int(11) UNSIGNED NULL DEFAULT 0,
  `sort` int(11) UNSIGNED NOT NULL DEFAULT 100,
  PRIMARY KEY (`api_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_article
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_article`;
CREATE TABLE `yoshop_article`  (
  `article_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '文章id',
  `article_title` varchar(300) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '文章标题',
  `show_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '列表显示方式(10小图展示 20大图展示)',
  `category_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文章分类id',
  `image_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '封面图id',
  `article_content` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '文章内容',
  `article_sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文章排序(数字越小越靠前)',
  `article_status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '文章状态(0隐藏 1显示)',
  `virtual_views` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '虚拟阅读量(仅用作展示)',
  `actual_views` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '实际阅读量',
  `is_delete` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`article_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10216 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文章记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_article_category
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_article_category`;
CREATE TABLE `yoshop_article_category`  (
  `category_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '商品分类id',
  `belong` tinyint(20) NOT NULL DEFAULT 0 COMMENT '0、默认 1、 违禁物品 2、新手问题 3、关于我们 4、包裹协议',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '分类名称',
  `sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序方式(数字越小越靠前)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`category_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10125 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文章分类表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_bank
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_bank`;
CREATE TABLE `yoshop_bank`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '银行名称',
  `bank_card` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '银行卡号',
  `open_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '开户人名字',
  `child_bank_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '支行名称',
  `bank_no` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '银行行号',
  `created_time` datetime(0) NULL DEFAULT NULL,
  `updated_time` datetime(0) NULL DEFAULT NULL,
  `status` tinyint(4) NULL DEFAULT 0 COMMENT '启用状态 默认0 删除1',
  `wxapp_id` int(11) NULL DEFAULT 0 COMMENT '所属平台id',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `yoshop_bank_id_uindex`(`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 18 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '汇款账号' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_banner
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_banner`;
CREATE TABLE `yoshop_banner`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '轮播标题',
  `image_id` int(11) NULL DEFAULT NULL COMMENT '轮播路径',
  `banner_site` tinyint(3) NULL DEFAULT NULL COMMENT '轮播位置 小程序 10 ，广告图 20 ，pc 30',
  `status` tinyint(3) NULL DEFAULT NULL COMMENT '状态(1 正常 2 禁用)',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '跳转路径',
  `redirect_type` tinyint(3) NULL DEFAULT NULL COMMENT '跳转类型',
  `wxapp_id` int(11) NULL DEFAULT NULL COMMENT '所属平台',
  `sort` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '排序',
  `created_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 154 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '轮播图表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_banner_log
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_banner_log`;
CREATE TABLE `yoshop_banner_log`  (
  `banner_id` int(11) NOT NULL COMMENT '弹窗公告id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `created_time` int(11) NOT NULL COMMENT '创建时间',
  `wxapp_id` int(11) NOT NULL COMMENT '所属商家'
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_bargain_active
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_bargain_active`;
CREATE TABLE `yoshop_bargain_active`  (
  `active_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '砍价活动id',
  `goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品id',
  `start_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '活动开始时间',
  `end_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '活动结束时间',
  `expiryt_time` int(11) UNSIGNED NOT NULL DEFAULT 1 COMMENT '砍价有效期(单位：小时)',
  `floor_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '砍价底价',
  `peoples` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '帮砍人数',
  `is_self_cut` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '可自砍一刀(0禁止 1允许)',
  `is_floor_buy` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '必须底价购买(0否 1是)',
  `share_title` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '分享标题',
  `prompt_words` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '砍价助力语',
  `actual_sales` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '活动销量(实际的)',
  `initial_sales` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '虚拟销量',
  `sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序(数字越小越靠前)',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '活动状态(1启用 0禁用)',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`active_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '砍价活动表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_bargain_setting
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_bargain_setting`;
CREATE TABLE `yoshop_bargain_setting`  (
  `key` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '设置项标示',
  `describe` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '设置项描述',
  `values` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '设置内容(json格式)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  UNIQUE INDEX `unique_key`(`key`, `wxapp_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '砍价活动设置表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_bargain_task
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_bargain_task`;
CREATE TABLE `yoshop_bargain_task`  (
  `task_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '砍价任务id',
  `active_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '砍价活动id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id(发起人)',
  `goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品id',
  `spec_sku_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品sku标识',
  `goods_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品原价',
  `floor_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '砍价底价',
  `peoples` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '帮砍人数',
  `cut_people` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '已砍人数',
  `section` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '砍价金额区间',
  `cut_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '已砍金额',
  `actual_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '实际购买金额',
  `is_floor` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否已砍到底价(0否 1是)',
  `end_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '任务截止时间',
  `is_buy` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否购买(0未购买 1已购买)',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '任务状态 (0已结束 1砍价中)',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`task_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '砍价任务表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_bargain_task_help
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_bargain_task_help`;
CREATE TABLE `yoshop_bargain_task_help`  (
  `help_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `active_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '砍价活动id',
  `task_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '砍价任务id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `is_creater` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否为发起人(0否 1是)',
  `cut_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '砍掉的金额',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`help_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '砍价任务助力记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_batch
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_batch`;
CREATE TABLE `yoshop_batch`  (
  `batch_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `batch_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '批次号',
  `is_over` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '默认0  结束=1',
  `batch_no` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '装箱代码',
  `last_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最近一次更新物流的时间',
  `template_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '批次使用的轨迹id',
  `step_num` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '自动更新物流轨迹的步骤数',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '默认0 待发货  1 运输中  2已到达',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '默认0  删除1',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `batch_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '空运=10 海运=20 陆运=30',
  `express` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '物流商ID',
  `express_no` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '物流商单号',
  `length` decimal(10, 2) NOT NULL COMMENT '长度',
  `width` decimal(10, 2) NOT NULL COMMENT '宽度',
  `height` decimal(10, 2) NOT NULL COMMENT '高度',
  `weigth` decimal(10, 2) NOT NULL COMMENT '重量',
  `wegihtvol` decimal(10, 2) NOT NULL COMMENT '体积重',
  `remark` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '备注信息',
  `shop_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `transfer` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '自有物流0  运输商1',
  PRIMARY KEY (`batch_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10095 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_batch_template
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_batch_template`;
CREATE TABLE `yoshop_batch_template`  (
  `template_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '模板名称',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`template_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_batch_template_item
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_batch_template_item`;
CREATE TABLE `yoshop_batch_template_item`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属模板',
  `wait_time` double(10, 1) NOT NULL COMMENT '小时数',
  `step_num` int(10) UNSIGNED NOT NULL COMMENT '步骤',
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '物流轨迹标题',
  `content` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '物流轨迹内容',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 86 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_buyer_bind
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_buyer_bind`;
CREATE TABLE `yoshop_buyer_bind`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_free` decimal(10, 2) NULL DEFAULT 0.00,
  `buyer_ids` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `wxapp_id` int(11) NULL DEFAULT NULL,
  `batch` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0',
  `create_time` int(11) NULL DEFAULT 0,
  `is_refund` tinyint(3) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 33 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_buyer_cart
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_buyer_cart`;
CREATE TABLE `yoshop_buyer_cart`  (
  `cart_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `spec` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `price` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `num` int(11) UNSIGNED NOT NULL DEFAULT 1,
  `member_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `wxapp_id` int(11) NOT NULL DEFAULT 0,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `address_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `free` decimal(6, 2) NULL DEFAULT NULL,
  PRIMARY KEY (`cart_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_buyer_order
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_buyer_order`;
CREATE TABLE `yoshop_buyer_order`  (
  `b_order_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `order_sn` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'url 购买链接',
  `spec` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '// 规格',
  `price` decimal(10, 2) NULL DEFAULT NULL COMMENT '// 价格',
  `num` int(11) UNSIGNED NULL DEFAULT 1 COMMENT '购买数量',
  `member_id` int(11) NULL DEFAULT NULL COMMENT '// 用户ID',
  `status` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '1' COMMENT '状态 1 : 待审核  2: 待采购  3 待发货 4 已发货  5 已入库  6: 已完成 7 已取消  -1 售后订单',
  `is_close` tinyint(3) UNSIGNED NULL DEFAULT 0 COMMENT '关闭状态',
  `created_time` datetime(0) NULL DEFAULT NULL,
  `updated_time` datetime(0) NULL DEFAULT NULL,
  `reason` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '原因',
  `wxapp_id` int(11) NULL DEFAULT NULL,
  `palform` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '平台来源 ，淘宝，天猫，京东',
  `real_payment` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '最终付款',
  `is_pay` tinyint(3) NULL DEFAULT 0 COMMENT '0 未支付  1已支付',
  `pay_time` datetime(0) NULL DEFAULT NULL COMMENT '支付时间',
  `free` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '应付款',
  `express_num` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '发货单号',
  `express_id` int(11) NULL DEFAULT NULL COMMENT '快递公司',
  `express_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '快递公司名称',
  `storage_id` int(11) NULL DEFAULT NULL COMMENT '寄往仓库',
  `address_id` int(11) NULL DEFAULT NULL COMMENT '地址',
  `service_free` decimal(10, 2) NULL DEFAULT NULL COMMENT '服务费',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '备注',
  `feedback` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '评价内容',
  `is_feed` tinyint(3) NULL DEFAULT 0 COMMENT '评价',
  `batch` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '批次',
  `rufund_step` tinyint(3) NULL DEFAULT 0 COMMENT '退货步骤',
  `refund_service` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '退款金额',
  PRIMARY KEY (`b_order_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '代购订单' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_category
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_category`;
CREATE TABLE `yoshop_category`  (
  `category_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '商品分类id',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '分类名称',
  `parent_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '上级分类id',
  `image_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分类图片id',
  `is_hot` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '热门分类 0 默认  1热门',
  `sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序方式(数字越小越靠前)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`category_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1940 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品分类表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_certificate
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_certificate`;
CREATE TABLE `yoshop_certificate`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cert_order` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '支付订单流水号',
  `cert_price` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '支付金额',
  `cert_bank` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '支付来源银行',
  `cert_date` datetime(0) NULL DEFAULT NULL COMMENT '充值时间',
  `cert_type` tinyint(3) NULL DEFAULT 0 COMMENT '充值币种 0人民币 1美元',
  `cert_status` tinyint(3) NULL DEFAULT 1 COMMENT '状态  1 待审核 2 确认打款 3 信息有误',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `user_id` int(11) NULL DEFAULT NULL COMMENT '所属用户',
  `wxapp_id` int(11) NULL DEFAULT NULL COMMENT '所属平台',
  `update_time` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12048 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = 'certificate 凭证表-用户提交支付凭证' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_certificate_image
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_certificate_image`;
CREATE TABLE `yoshop_certificate_image`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cert_id` int(11) NULL DEFAULT NULL COMMENT '支付凭证id',
  `image_id` int(11) NULL DEFAULT NULL COMMENT '图片',
  `wxapp_id` int(11) NULL DEFAULT NULL COMMENT '平台',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10105 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '凭证图片表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_comment
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_comment`;
CREATE TABLE `yoshop_comment`  (
  `comment_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '评价id',
  `score` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '10' COMMENT '评分 (10好评 20中评 30差评)',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '评价内容',
  `is_picture` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否为图片评价',
  `sort` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '评价排序',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态(0隐藏 1显示)',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '集运订单id',
  `goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品id',
  `order_goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单商品id',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `is_delete` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '软删除',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  `common_type` tinyint(4) NULL DEFAULT 1 COMMENT '1 集运订单  2 商城评价',
  PRIMARY KEY (`comment_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 568 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '订单评价记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_comment_image
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_comment_image`;
CREATE TABLE `yoshop_comment_image`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `comment_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '评价id',
  `image_id` int(11) NOT NULL DEFAULT 0 COMMENT '图片id(关联文件记录表)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 117 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '订单评价图片记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_countries
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_countries`;
CREATE TABLE `yoshop_countries`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `code` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `status` tinyint(3) NULL DEFAULT 1 COMMENT '1 显示 2 不显示',
  `sort` int(11) UNSIGNED NOT NULL DEFAULT 100 COMMENT '排序',
  `wxapp_id` int(11) NULL DEFAULT NULL,
  `is_hot` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0默认 1热门国家',
  `is_top` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '默认0，1 默认选中的国家',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6401 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_coupon
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_coupon`;
CREATE TABLE `yoshop_coupon`  (
  `coupon_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '优惠券id',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '优惠券名称',
  `color` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '优惠券颜色(10蓝 20红 30紫 40黄)',
  `coupon_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '优惠券类型(10满减券 20折扣券)',
  `reduce_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '满减券-减免金额',
  `discount` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '折扣券-折扣率(0-100)',
  `min_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '最低消费金额',
  `expire_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '到期类型(10领取后生效 20固定时间)',
  `expire_day` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '领取后生效-有效天数',
  `start_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '固定时间-开始时间',
  `end_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '固定时间-结束时间',
  `apply_range` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '适用范围(10全部商品 20指定商品)',
  `total_num` int(11) NOT NULL DEFAULT 0 COMMENT '发放总数量(-1为不限制)',
  `receive_num` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '已领取数量',
  `is_open` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否对外公开 0默认对外 1不对外',
  `sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序方式(数字越小越靠前)',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '软删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`coupon_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 138 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '优惠券记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_coupon_goods
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_coupon_goods`;
CREATE TABLE `yoshop_coupon_goods`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `coupon_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '优惠券id',
  `goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品id',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '优惠券指定商品记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_dealer_apply
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_dealer_apply`;
CREATE TABLE `yoshop_dealer_apply`  (
  `apply_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `real_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '姓名',
  `mobile` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `referee_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '推荐人用户id',
  `apply_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '申请方式(10需后台审核 20无需审核)',
  `apply_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '申请时间',
  `apply_status` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '审核状态 (10待审核 20审核通过 30驳回)',
  `audit_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '审核时间',
  `reject_reason` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '驳回原因',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`apply_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 280 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '分销商申请记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_dealer_capital
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_dealer_capital`;
CREATE TABLE `yoshop_dealer_capital`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分销商用户id',
  `flow_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '资金流动类型 (10佣金收入 20提现支出)',
  `money` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '金额',
  `describe` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '描述',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 251 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '分销商资金明细表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_dealer_order
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_dealer_order`;
CREATE TABLE `yoshop_dealer_order`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id (买家)',
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单id',
  `order_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '订单类型(10商城订单 20拼团订单 30 集运订单)',
  `order_no` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单号(废弃,勿用)',
  `order_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '订单总金额(不含运费)',
  `first_user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分销商用户id(一级)',
  `second_user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分销商用户id(二级)',
  `third_user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分销商用户id(三级)',
  `first_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '分销佣金(一级)',
  `second_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '分销佣金(二级)',
  `third_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '分销佣金(三级)',
  `is_invalid` tinyint(3) NOT NULL DEFAULT 0 COMMENT '订单是否失效(0未失效 1已失效)',
  `is_settled` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否已结算佣金(0未结算 1已结算)',
  `settle_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '结算时间',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `order_id`(`order_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 174 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '分销商订单记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_dealer_rating
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_dealer_rating`;
CREATE TABLE `yoshop_dealer_rating`  (
  `rating_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '等级名称',
  `weight` int(11) NOT NULL COMMENT '等级权重(1-9999)',
  `setting` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'json,设置分销比例',
  `upgrade` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '升级条件',
  `status` tinyint(3) NOT NULL COMMENT '状态 0 默认 1禁用',
  `wxapp_id` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '默认0   1删除',
  PRIMARY KEY (`rating_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10035 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_dealer_rating_log
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_dealer_rating_log`;
CREATE TABLE `yoshop_dealer_rating_log`  (
  `log_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `old_rating_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '变更前的等级id',
  `new_rating_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '变更后的等级id',
  `change_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '变更类型(10后台管理员设置 20自动升级)',
  `remark` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '管理员备注',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`log_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 122 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户会员等级变更记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_dealer_referee
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_dealer_referee`;
CREATE TABLE `yoshop_dealer_referee`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `dealer_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分销商用户id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id(被推荐人)',
  `level` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '推荐关系层级(1,2,3)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `dealer_id`(`dealer_id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 206 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '分销商推荐关系表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_dealer_setting
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_dealer_setting`;
CREATE TABLE `yoshop_dealer_setting`  (
  `key` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '设置项标示',
  `describe` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '设置项描述',
  `values` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '设置内容(json格式)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  UNIQUE INDEX `unique_key`(`key`, `wxapp_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '分销商设置表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_dealer_user
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_dealer_user`;
CREATE TABLE `yoshop_dealer_user`  (
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分销商用户id',
  `real_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '姓名',
  `mobile` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `rating_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员分销等级',
  `money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '当前可提现佣金',
  `freeze_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '已冻结佣金',
  `total_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '累积提现佣金',
  `referee_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '推荐人用户id',
  `first_num` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '成员数量(一级)',
  `second_num` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '成员数量(二级)',
  `third_num` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '成员数量(三级)',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`user_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '分销商用户记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_dealer_withdraw
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_dealer_withdraw`;
CREATE TABLE `yoshop_dealer_withdraw`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分销商用户id',
  `money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '提现金额',
  `pay_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '打款方式 (10微信 20支付宝 30银行卡)',
  `alipay_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '支付宝姓名',
  `alipay_account` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '支付宝账号',
  `bank_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '开户行名称',
  `bank_account` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '银行开户名',
  `bank_card` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '银行卡号',
  `apply_status` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '申请状态 (10待审核 20审核通过 30驳回 40已打款)',
  `audit_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '审核时间',
  `reject_reason` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '驳回原因',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  `cash_from` tinyint(3) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 49 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '分销商提现明细表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_delivery
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_delivery`;
CREATE TABLE `yoshop_delivery`  (
  `delivery_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '模板id',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '模板名称',
  `method` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '计费方式(10按件数 20按重量)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序d',
  `sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序方式(数字越小越靠前)',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`delivery_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10003 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '配送模板主表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_delivery_rule
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_delivery_rule`;
CREATE TABLE `yoshop_delivery_rule`  (
  `rule_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '规则id',
  `delivery_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '配送模板id',
  `region` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '可配送区域(城市id集)',
  `first` double UNSIGNED NOT NULL DEFAULT 0 COMMENT '首件(个)/首重(Kg)',
  `first_fee` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '运费(元)',
  `additional` double UNSIGNED NOT NULL DEFAULT 0 COMMENT '续件/续重',
  `additional_fee` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '续费(元)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`rule_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10003 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '配送模板区域及运费表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_ditch
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_ditch`;
CREATE TABLE `yoshop_ditch`  (
  `ditch_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ditch_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '渠道商名称',
  `ditch_no` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '渠道商编号或id',
  `website` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '官网',
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 默认 1没有',
  `api_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'api地址',
  `app_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '客户key',
  `app_token` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '客户密钥',
  `wxapp_id` int(11) NOT NULL,
  `sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 默认 1不启用',
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`ditch_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10075 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '渠道商' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_ecommerce_order
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_ecommerce_order`;
CREATE TABLE `yoshop_ecommerce_order`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_time` int(11) NULL DEFAULT NULL,
  `update_time` int(11) NULL DEFAULT NULL,
  `platform` tinyint(10) NULL DEFAULT 10 COMMENT '// 平台 10 淘宝',
  `order_sn` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '// 订单号/交易号',
  `extends` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '// 扩展信息',
  `wxapp_id` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_express
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_express`;
CREATE TABLE `yoshop_express`  (
  `express_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '物流公司id',
  `express_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '物流公司名称',
  `express_code` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '物流公司代码 (17track)',
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 默认都行，1只作为预报  2 只作为国际物流发货',
  `sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序 (数字越小越靠前)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`express_id`) USING BTREE,
  INDEX `express_code`(`express_code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10577 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '物流公司记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_feedback
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_feedback`;
CREATE TABLE `yoshop_feedback`  (
  `feed_id` int(11) NOT NULL AUTO_INCREMENT,
  `suggest` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0',
  `images` varchar(300) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0',
  `suggest_type` tinyint(4) NULL DEFAULT 0,
  `created_time` datetime(0) NULL DEFAULT '0000-00-00 00:00:00',
  `store_id` int(11) NULL DEFAULT 0,
  `real_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0',
  `mobile` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '11',
  PRIMARY KEY (`feed_id`) USING BTREE,
  UNIQUE INDEX `yoshop_feedback_feed_id_uindex`(`feed_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_goods
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_goods`;
CREATE TABLE `yoshop_goods`  (
  `goods_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '商品id',
  `goods_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品名称',
  `selling_point` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品卖点',
  `category_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品分类id',
  `spec_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品规格(10单规格 20多规格)',
  `deduct_stock_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 20 COMMENT '库存计算方式(10下单减库存 20付款减库存)',
  `content` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '商品详情',
  `sales_initial` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '初始销量',
  `sales_actual` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '实际销量',
  `goods_sort` int(11) UNSIGNED NOT NULL DEFAULT 100 COMMENT '商品排序(数字越小越靠前)',
  `delivery_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '配送模板id',
  `is_points_gift` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否开启积分赠送(1开启 0关闭)',
  `is_points_discount` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否允许使用积分抵扣(1允许 0不允许)',
  `is_enable_grade` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否开启会员折扣(1开启 0关闭)',
  `is_alone_grade` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员折扣设置(0默认等级折扣 1单独设置折扣)',
  `alone_grade_equity` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '单独设置折扣的配置',
  `is_ind_dealer` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否开启单独分销(0关闭 1开启)',
  `dealer_money_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '分销佣金类型(10百分比 20固定金额)',
  `first_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '分销佣金(一级)',
  `second_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '分销佣金(二级)',
  `third_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '分销佣金(三级)',
  `goods_status` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '商品状态(10上架 20下架)',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`goods_id`) USING BTREE,
  INDEX `category_id`(`category_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_goods_image
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_goods_image`;
CREATE TABLE `yoshop_goods_image`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品id',
  `image_id` int(11) NOT NULL COMMENT '图片id(关联文件记录表)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品图片记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_goods_sku
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_goods_sku`;
CREATE TABLE `yoshop_goods_sku`  (
  `goods_sku_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '商品规格id',
  `goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品id',
  `spec_sku_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '商品sku记录索引 (由规格id组成)',
  `image_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '规格图片id',
  `goods_no` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品编码',
  `goods_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品价格',
  `line_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品划线价',
  `stock_num` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '当前库存数量',
  `goods_sales` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品销量',
  `goods_weight` double UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品重量(Kg)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`goods_sku_id`) USING BTREE,
  UNIQUE INDEX `sku_idx`(`goods_id`, `spec_sku_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品规格表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_goods_spec_rel
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_goods_spec_rel`;
CREATE TABLE `yoshop_goods_spec_rel`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品id',
  `spec_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '规格组id',
  `spec_value_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '规格值id',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品与规格值关系记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_h5_setting
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_h5_setting`;
CREATE TABLE `yoshop_h5_setting`  (
  `key` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '设置项标示',
  `describe` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '设置项描述',
  `values` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '设置内容(json格式)',
  `wxapp_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商城ID',
  `update_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  UNIQUE INDEX `unique_key`(`key`, `wxapp_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = 'H5端设置表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_inpack
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_inpack`;
CREATE TABLE `yoshop_inpack`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '拣货状态',
  `order_sn` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '打包单号',
  `batch_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '批次id',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注',
  `usermark` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '唛头',
  `pack_ids` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '包裹ID',
  `pack_services_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '包装服务ID',
  `waitreceivedmoney` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '待收款',
  `address_id` int(11) NULL DEFAULT NULL COMMENT '取件地址',
  `jaddress_id` int(11) NULL DEFAULT NULL COMMENT '寄件地址',
  `status` tinyint(3) NULL DEFAULT 2 COMMENT '状态 1 待查验 2 待支付 3 待发货 4 拣货中 5 已打包  6已发货 7 已到货 8 已完成  9已取消 10草稿',
  `member_id` int(11) NULL DEFAULT NULL COMMENT '用户ID',
  `storage_id` int(11) NULL DEFAULT NULL COMMENT '寄件仓库ID',
  `shop_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '收件仓库',
  `wxapp_id` int(11) NULL DEFAULT NULL COMMENT '所属商家',
  `line_id` int(11) NULL DEFAULT 0 COMMENT '线路id',
  `real_payment` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '实付支付金额',
  `total_free` decimal(10, 2) NULL DEFAULT NULL,
  `free` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '基础线路费用',
  `pack_free` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '服务项目费用',
  `other_free` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '其他费用，如海关费用等',
  `user_coupon_money` decimal(11, 2) NULL DEFAULT NULL COMMENT '优惠券优惠的金额',
  `discount_price` decimal(10, 2) NULL DEFAULT NULL COMMENT '折扣价',
  `weight` double(10, 2) NULL DEFAULT 0.00 COMMENT '实际重量',
  `cale_weight` double(10, 2) NULL DEFAULT 0.00 COMMENT '计费重量',
  `volume` double(10, 2) NULL DEFAULT 0.00 COMMENT '体积重',
  `country` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '目标国家（可废弃，通过address获取）',
  `length` double NULL DEFAULT 0 COMMENT '长',
  `width` double NULL DEFAULT 0 COMMENT '宽',
  `height` double NULL DEFAULT 0 COMMENT '高',
  `is_pay` tinyint(3) NULL DEFAULT 2 COMMENT '支付状态 [1,已支付 2 未支付]',
  `is_pay_type` tinyint(3) UNSIGNED NULL DEFAULT 1 COMMENT '0 后台操作 1 微信 2 余额 3 汉特  4现金支付 5余额支付',
  `t_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '承运商',
  `t_number` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '物流商编号',
  `t_order_sn` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '转运单号',
  `source` tinyint(3) NULL DEFAULT 1 COMMENT '[1 集运包裹 打包   2 代购包裹打包 3 PC包裹生成集运订单 ]  4 直邮订单 5 第三方拉取 6 仓管端快速打包',
  `discount` decimal(10, 2) NULL DEFAULT 1.00 COMMENT '折扣',
  `user_coupon_id` int(11) NULL DEFAULT NULL COMMENT '使用优惠券的id',
  `logistics` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '物流信息',
  `is_delete` tinyint(3) NULL DEFAULT 0 COMMENT '0 默认  1删除',
  `is_exceed` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '默认0 超时=1',
  `is_settled` tinyint(3) UNSIGNED NULL DEFAULT 0 COMMENT '0 未结算 1已结算',
  `pay_type` tinyint(3) UNSIGNED NULL DEFAULT 0 COMMENT '0 默认立即发货   1 货到付款   2月结',
  `inpack_type` tinyint(3) UNSIGNED NULL DEFAULT 0 COMMENT '0默认普通单  1拼团订单  2直邮模式 3 拼邮订单',
  `unpack_time` datetime(0) NULL DEFAULT NULL COMMENT '提交打包时间',
  `pick_time` datetime(0) NULL DEFAULT NULL COMMENT '拣货完成时间',
  `pay_time` datetime(0) NULL DEFAULT NULL COMMENT '支付完成时间',
  `receipt_time` datetime(0) NULL DEFAULT NULL COMMENT '用户签收时间',
  `shoprk_time` datetime(0) NULL DEFAULT NULL COMMENT '到货入库时间',
  `settle_time` datetime(0) NULL DEFAULT NULL COMMENT '佣金结算时间',
  `exceed_date` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '订单超时时间',
  `sendout_time` datetime(0) NULL DEFAULT NULL COMMENT '订单发货时间',
  `created_time` datetime(0) NULL DEFAULT NULL,
  `updated_time` datetime(0) NULL DEFAULT NULL,
  `pay_order` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '支付订单号',
  `t2_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '转单单号',
  `t2_number` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '物流商编号',
  `t2_order_sn` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '物流单号',
  `transfer` int(11) UNSIGNED NULL DEFAULT 1 COMMENT '默认1  1为17track物流商  0为自有物流',
  `take_code` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '取货码',
  `is_focus_image` tinyint(3) UNSIGNED NULL DEFAULT 0 COMMENT '默认0  已上传1',
  `third` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '第三方订单信息',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 25396 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_inpack_image
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_inpack_image`;
CREATE TABLE `yoshop_inpack_image`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `inpack_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '集运id',
  `image_id` int(11) NOT NULL DEFAULT 0 COMMENT '图片id(关联文件记录表)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 94184 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '集运订单图片记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_inpack_item
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_inpack_item`;
CREATE TABLE `yoshop_inpack_item`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `inpack_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属集运单',
  `width` double(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '宽度',
  `height` double(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '高度',
  `length` double(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '长度',
  `weight` double(10, 2) UNSIGNED NOT NULL DEFAULT 0.00,
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `t_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '承运商',
  `t_number` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '物流商编号',
  `t_order_sn` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '转运单号',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_inpack_service
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_inpack_service`;
CREATE TABLE `yoshop_inpack_service`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `inpack_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '集运单id',
  `service_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '服务项目id',
  `service_sum` int(11) UNSIGNED NOT NULL DEFAULT 1 COMMENT '服务项目数量',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 28242 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '打包服务记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_line
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_line`;
CREATE TABLE `yoshop_line`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '状态',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '线路名称',
  `image_id` int(11) NULL DEFAULT NULL COMMENT '封面图片',
  `line_content` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '路线内容',
  `free_mode` tinyint(3) NULL DEFAULT NULL COMMENT '计费模式(1阶梯计费 2首续重 3 区间计费 4 重量区间计费)',
  `exceed_date` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '超时时间，0表示未定义不用处理，大于0 则为超时时间',
  `free_rule` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '计费规则',
  `limitationofdelivery` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '运送时效说明',
  `service_route` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '增值服务说明',
  `services_require` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '增值服务的编号',
  `line_special` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '线路特点说明',
  `goods_limit` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '物品限制说明',
  `length_limit` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '长度限制说明',
  `body` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '详情',
  `tariff` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '关税',
  `weight` float(11, 2) NULL DEFAULT NULL,
  `weight_min` decimal(10, 3) NULL DEFAULT NULL COMMENT '限制最小重量',
  `max_weight` decimal(10, 3) NULL DEFAULT NULL COMMENT '限制最大重量',
  `weight_limit` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '重量限制说明',
  `volumeweight_type` tinyint(3) UNSIGNED NULL DEFAULT 10 COMMENT '体积重模式 默认10=普通，20=实重+（长*宽*高/6000-实重）*70%',
  `bubble_weight` int(11) UNSIGNED NULL DEFAULT 70 COMMENT '泡重比',
  `volumeweight` int(11) NULL DEFAULT 6000 COMMENT '体积重10000/5000/6000/166',
  `countrys` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '支持的国家',
  `categorys` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '支持的分类',
  `fade_remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '// 反馈备注',
  `is_recommend` tinyint(11) NULL DEFAULT 2 COMMENT '是否推荐',
  `sort` int(11) NULL DEFAULT NULL COMMENT '排序',
  `status` tinyint(3) NULL DEFAULT NULL COMMENT '状态(1启用 2禁止)',
  `line_type` tinyint(3) NULL DEFAULT 0 COMMENT '0 按重量  1按体积',
  `line_type_unit` tinyint(3) NULL DEFAULT 20 COMMENT '10 g  20 kg 30 bl  40 cbm',
  `created_time` int(11) NULL DEFAULT NULL COMMENT '时间',
  `wxapp_id` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 960 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_line_services
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_line_services`;
CREATE TABLE `yoshop_line_services`  (
  `service_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '增值服务名称',
  `desc` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '说明',
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '10=重量模式  20=长度',
  `rule` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '增值服务的费用规则',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '启用=0  禁用=1',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`service_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 154 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_logistics
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_logistics`;
CREATE TABLE `yoshop_logistics`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '批次id',
  `order_sn` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '平台预报单号',
  `express_num` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '快递单号',
  `status` tinyint(3) NULL DEFAULT NULL,
  `logistics_describe` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `created_time` datetime(0) NULL DEFAULT NULL,
  `wxapp_id` int(11) NULL DEFAULT NULL,
  `status_cn` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `logistics_sn` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT ' 集运单号',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 553447 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_order
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_order`;
CREATE TABLE `yoshop_order`  (
  `order_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '订单id',
  `order_no` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `total_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品总金额(不含优惠折扣)',
  `order_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '订单金额(含优惠折扣)',
  `coupon_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '优惠券id',
  `coupon_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '优惠券抵扣金额',
  `points_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '积分抵扣金额',
  `points_num` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '积分抵扣数量',
  `pay_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '实际付款金额(包含运费)',
  `update_price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '后台修改的订单金额（差价）',
  `buyer_remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '买家留言',
  `pay_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 20 COMMENT '支付方式(10余额支付 20微信支付)',
  `pay_status` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '付款状态(10未付款 20已付款)',
  `pay_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '付款时间',
  `delivery_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '配送方式(10快递配送 20上门自提)',
  `extract_shop_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '自提门店id',
  `extract_clerk_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '核销店员id',
  `express_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '运费金额',
  `express_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '物流公司id',
  `express_company` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '物流公司',
  `express_no` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '物流单号',
  `delivery_status` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '发货状态(10未发货 20已发货)',
  `delivery_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '发货时间',
  `receipt_status` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '收货状态(10未收货 20已收货)',
  `receipt_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '收货时间',
  `order_status` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '订单状态(10进行中 20取消 21待取消 30已完成)',
  `points_bonus` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '赠送的积分数量',
  `is_settled` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单是否已结算(0未结算 1已结算)',
  `transaction_id` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '微信支付交易号',
  `is_comment` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否已评价(0否 1是)',
  `order_source` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '订单来源(10普通订单 20砍价订单 30秒杀订单)',
  `order_source_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '来源记录id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`order_id`) USING BTREE,
  UNIQUE INDEX `order_no`(`order_no`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '订单记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_order_address
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_order_address`;
CREATE TABLE `yoshop_order_address`  (
  `order_address_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '地址id',
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '收货人姓名',
  `phone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '联系电话',
  `province_id` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '所在省份id',
  `city_id` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '所在城市id',
  `region_id` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '所在区id',
  `detail` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '详细地址',
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`order_address_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '订单收货地址记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_order_extract
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_order_extract`;
CREATE TABLE `yoshop_order_extract`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单id',
  `linkman` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '联系人姓名',
  `phone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '联系电话',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '自提订单联系方式记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_order_goods
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_order_goods`;
CREATE TABLE `yoshop_order_goods`  (
  `order_goods_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品id',
  `goods_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品名称',
  `image_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品封面图id',
  `deduct_stock_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 20 COMMENT '库存计算方式(10下单减库存 20付款减库存)',
  `spec_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '规格类型(10单规格 20多规格)',
  `spec_sku_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品sku标识',
  `goods_sku_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品规格id',
  `goods_attr` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品规格信息',
  `content` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '商品详情',
  `goods_no` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品编码',
  `goods_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品价格(单价)',
  `line_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品划线价',
  `goods_weight` double UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品重量(Kg)',
  `is_user_grade` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否存在会员等级折扣',
  `grade_ratio` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员折扣比例(0-10)',
  `grade_goods_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '会员折扣的商品单价',
  `grade_total_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '会员折扣的总额差',
  `coupon_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '优惠券折扣金额',
  `points_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '积分金额',
  `points_num` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '积分抵扣数量',
  `points_bonus` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '赠送的积分数量',
  `total_num` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '购买数量',
  `total_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品总价(数量×单价)',
  `total_pay_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '实际付款价(折扣和优惠后)',
  `is_ind_dealer` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否开启单独分销(0关闭 1开启)',
  `dealer_money_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '分销佣金类型(10百分比 20固定金额)',
  `first_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '分销佣金(一级)',
  `second_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '分销佣金(二级)',
  `third_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '分销佣金(三级)',
  `is_comment` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否已评价(0否 1是)',
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `goods_source_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '来源记录id',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`order_goods_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '订单商品记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_order_refund
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_order_refund`;
CREATE TABLE `yoshop_order_refund`  (
  `order_refund_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '售后单id',
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单id',
  `order_goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单商品id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '售后类型(10退货退款 20换货)',
  `apply_desc` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户申请原因(说明)',
  `is_agree` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商家审核状态(0待审核 10已同意 20已拒绝)',
  `refuse_desc` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商家拒绝原因(说明)',
  `refund_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '实际退款金额',
  `is_user_send` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户是否发货(0未发货 1已发货)',
  `send_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户发货时间',
  `express_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户发货物流公司id',
  `express_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户发货物流单号',
  `is_receipt` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商家收货状态(0未收货 1已收货)',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '售后单状态(0进行中 10已拒绝 20已完成 30已取消)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`order_refund_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '售后单记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_order_refund_address
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_order_refund_address`;
CREATE TABLE `yoshop_order_refund_address`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `order_refund_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '售后单id',
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '收货人姓名',
  `phone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '联系电话',
  `detail` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '详细地址',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '售后单退货地址记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_order_refund_image
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_order_refund_image`;
CREATE TABLE `yoshop_order_refund_image`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `order_refund_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '售后单id',
  `image_id` int(11) NOT NULL DEFAULT 0 COMMENT '图片id(关联文件记录表)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '售后单图片记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_package
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_package`;
CREATE TABLE `yoshop_package`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `inpack_id` int(11) NULL DEFAULT NULL COMMENT '所属集运订单的编号',
  `batch_id` int(11) NULL DEFAULT NULL COMMENT '加入批次',
  `order_sn` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '订单号',
  `member_id` int(11) NULL DEFAULT 0 COMMENT '所属用户',
  `member_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '用户姓名',
  `express_id` int(11) NULL DEFAULT 0 COMMENT '物流ID',
  `express_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '物流名称',
  `express_num` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '物流单号',
  `origin_express_num` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '原单号',
  `usermark` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '唛头',
  `status` tinyint(3) NULL DEFAULT 1 COMMENT '1 待入库 2 已入库 3 已上架(提交打包准备)  4 待打包  5 待支付  6 已支付 7 加入批次(扫码入批次了)  8 已打包  9 已发货 10 已收货 11 已完成',
  `storage_id` int(11) NULL DEFAULT 0 COMMENT '// 仓库id',
  `price` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '总价值',
  `admin_remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '仓库的备注',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注',
  `country_id` int(11) NULL DEFAULT 0 COMMENT '国家',
  `real_payment` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '实际支付',
  `line_id` int(11) NULL DEFAULT 0 COMMENT '线路id',
  `line_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '线路名称',
  `wxapp_id` int(11) NULL DEFAULT 0 COMMENT '所属商家',
  `pay_type` tinyint(3) NULL DEFAULT 1 COMMENT '支付方式 1 [余额支付] 2 [微信支付]',
  `free` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '计费运费',
  `volumeweight` double(12, 4) NULL DEFAULT NULL COMMENT '体积重',
  `weight` double(12, 3) NULL DEFAULT 0.000 COMMENT '计费重量',
  `length` double NULL DEFAULT 0 COMMENT '长',
  `width` double NULL DEFAULT 0 COMMENT '宽',
  `height` double NULL DEFAULT 0 COMMENT '高',
  `volume` float(10, 4) NULL DEFAULT NULL COMMENT '体积',
  `num` int(11) NULL DEFAULT 1 COMMENT '1',
  `address_id` int(11) NULL DEFAULT 0 COMMENT '地址id',
  `visit_free` decimal(11, 2) NULL DEFAULT 0.00 COMMENT '上门服务费，目前仅在上门预约件里有',
  `pack_free` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '打包费',
  `pack_service` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '包装服务',
  `image` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '暂无用，使用package_image存包裹图片',
  `pack_attr` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '包裹属性',
  `goods_attr` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '物品属性',
  `source` tinyint(255) NULL DEFAULT 1 COMMENT '录入来源 [1小程序录入 2平台录入 3 代购同步 4 批量导入 5 PC端预报 6 拼团预报] 7预约取件 8仓管录入 9 api录入',
  `is_take` tinyint(3) NULL DEFAULT 1 COMMENT '是否 认领 1 待认领 2 已认领 3 已丢弃 4、退件',
  `is_pay` tinyint(3) NULL DEFAULT 0 COMMENT '是否支付 默认0  支付1',
  `is_delete` tinyint(255) NULL DEFAULT 0 COMMENT '默认0  删除1',
  `is_verify` tinyint(3) NULL DEFAULT 2 COMMENT '是否查验 1 已查验 2 待查验',
  `is_scan` tinyint(3) NULL DEFAULT 1 COMMENT '是否扫码出库 1 未扫码出库  2 已扫码出库',
  `pay_time` datetime(0) NULL DEFAULT NULL COMMENT '支付时间',
  `entering_warehouse_time` datetime(0) NULL DEFAULT NULL COMMENT '入库时间',
  `updated_time` datetime(0) NULL DEFAULT NULL,
  `created_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `单号`(`order_sn`, `express_num`, `inpack_id`, `storage_id`, `member_id`, `batch_id`, `line_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 349642 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_package_image
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_package_image`;
CREATE TABLE `yoshop_package_image`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `package_id` int(11) NOT NULL DEFAULT 0 COMMENT '包裹id',
  `image_id` int(11) NOT NULL COMMENT '关联图片id',
  `wxapp_id` int(11) NOT NULL DEFAULT 0 COMMENT '商家',
  `create_time` int(11) NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 276802 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_package_item
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_package_item`;
CREATE TABLE `yoshop_package_item`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NULL DEFAULT NULL COMMENT '包裹的id',
  `express_num` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '快递单号',
  `express_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '快递公司的id',
  `express_name` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '快递公司的名称',
  `class_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '分类名称',
  `class_id` int(11) NULL DEFAULT 0 COMMENT '分类ID',
  `class_name_en` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '英文品名',
  `distribution` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '配货',
  `product_num` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '1' COMMENT '商品数量',
  `all_price` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '商家总价',
  `customs_code` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '海关编码',
  `length` double(10, 2) NULL DEFAULT 0.00 COMMENT '长度',
  `width` double(10, 2) NULL DEFAULT 0.00 COMMENT '宽度',
  `height` double(10, 2) NULL DEFAULT 0.00 COMMENT '高度',
  `all_weight` double(10, 2) NULL DEFAULT 0.00 COMMENT '总重量',
  `unit_weight` float(10, 2) NULL DEFAULT 0.00 COMMENT '单个物品 重量',
  `volumeweight` double(10, 4) NULL DEFAULT NULL COMMENT '体积重',
  `volume` double(10, 4) NULL DEFAULT 0.0000 COMMENT '体积',
  `goods_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '货物名称',
  `one_price` decimal(10, 2) NULL DEFAULT NULL COMMENT '物品单价',
  `wxapp_id` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `order_id`(`order_id`, `class_name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 167340 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_package_pc
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_package_pc`;
CREATE TABLE `yoshop_package_pc`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `package_id` int(11) NULL DEFAULT NULL,
  `sale_url` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '销售地址',
  `image_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '商品图片地址',
  `insurance` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `other_price` decimal(10, 2) NULL DEFAULT 0.00,
  `is_back` tinyint(3) NULL DEFAULT 0,
  `recive_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0',
  `recive_address` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0',
  `recive_campany` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `recive_province` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `recive_city` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `recive_street` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0',
  `recive_email` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0',
  `recive_mobile` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0',
  `post_code` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `address_id` int(11) NULL DEFAULT 0,
  `wxapp_id` int(11) NULL DEFAULT NULL,
  `transfer_order_no` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `num` int(11) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `yoshop_package_pc_id_uindex`(`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 61 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_package_services
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_package_services`;
CREATE TABLE `yoshop_package_services`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `price` decimal(10, 2) NOT NULL,
  `percentage` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '运费百分比',
  `type` tinyint(3) NOT NULL COMMENT '0 固定金额  1按运费百分比',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `service_des` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '说明',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 163 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_pay
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_pay`;
CREATE TABLE `yoshop_pay`  (
  `id` int(11) NOT NULL,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `en` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_printer
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_printer`;
CREATE TABLE `yoshop_printer`  (
  `printer_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '打印机id',
  `printer_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '打印机名称',
  `printer_type` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '打印机类型',
  `printer_config` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '打印机配置',
  `print_times` smallint(6) UNSIGNED NOT NULL DEFAULT 0 COMMENT '打印联数(次数)',
  `sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序 (数字越小越靠前)',
  `is_delete` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`printer_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '小票打印机记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_recharge_order
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_recharge_order`;
CREATE TABLE `yoshop_recharge_order`  (
  `order_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '订单id',
  `order_no` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `recharge_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '充值方式(10自定义金额 20套餐充值)',
  `plan_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '充值套餐id',
  `pay_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '用户支付金额',
  `gift_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '赠送金额',
  `actual_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '实际到账金额',
  `pay_status` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '支付状态(10待支付 20已支付)',
  `pay_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '付款时间',
  `transaction_id` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '微信支付交易号',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序商城id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`order_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3331 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户充值订单表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_recharge_order_plan
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_recharge_order_plan`;
CREATE TABLE `yoshop_recharge_order_plan`  (
  `order_plan_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单id',
  `plan_id` int(11) UNSIGNED NOT NULL COMMENT '主键id',
  `plan_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '方案名称',
  `money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '充值金额',
  `gift_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '赠送金额',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序商城id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`order_plan_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1845 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户充值订单套餐快照表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_recharge_plan
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_recharge_plan`;
CREATE TABLE `yoshop_recharge_plan`  (
  `plan_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `plan_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '套餐名称',
  `money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '充值金额',
  `gift_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '赠送金额',
  `sort` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序(数字越小越靠前)',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序商城id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`plan_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 56 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '余额充值套餐表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_region
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_region`;
CREATE TABLE `yoshop_region`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int(11) NULL DEFAULT NULL COMMENT '父id',
  `shortname` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '简称',
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '名称',
  `merger_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '全称',
  `level` tinyint(4) UNSIGNED NULL DEFAULT 0 COMMENT '层级 1 2 3 省市区县',
  `pinyin` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '拼音',
  `code` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '长途区号',
  `zip_code` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '邮编',
  `first` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '首字母',
  `lng` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '经度',
  `lat` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '纬度',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `name,level`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4047 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_return_address
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_return_address`;
CREATE TABLE `yoshop_return_address`  (
  `address_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '退货地址id',
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '收货人姓名',
  `phone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '联系电话',
  `detail` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '详细地址',
  `sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序 (数字越小越靠前)',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`address_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '退货地址记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_send_order
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_send_order`;
CREATE TABLE `yoshop_send_order`  (
  `send_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '// 发货订单号',
  `pack_ids` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '包裹ID池',
  `created_time` datetime(0) NULL DEFAULT NULL,
  `updated_time` datetime(0) NULL DEFAULT NULL,
  `status` tinyint(3) NULL DEFAULT 2 COMMENT '2已发货 3 已完成',
  `wxapp_id` int(11) NULL DEFAULT NULL,
  `num` int(11) NULL DEFAULT NULL,
  `logistics` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '物流信息',
  PRIMARY KEY (`send_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_send_pre_order
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_send_pre_order`;
CREATE TABLE `yoshop_send_pre_order`  (
  `send_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '// 发货订单号',
  `pack_ids` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '包裹ID池',
  `created_time` datetime(0) NULL DEFAULT NULL,
  `updated_time` datetime(0) NULL DEFAULT NULL,
  `wxapp_id` int(11) NULL DEFAULT NULL,
  `num` int(11) NULL DEFAULT NULL COMMENT '包裹数量',
  `opration_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '操作员名称',
  `opration_id` int(11) NULL DEFAULT NULL COMMENT '操作员ID',
  PRIMARY KEY (`send_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_setting
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_setting`;
CREATE TABLE `yoshop_setting`  (
  `key` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '设置项标示',
  `describe` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '设置项描述',
  `values` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '设置内容（json格式）',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  UNIQUE INDEX `unique_key`(`key`, `wxapp_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商城设置记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_active
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_active`;
CREATE TABLE `yoshop_sharing_active`  (
  `active_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '拼单id',
  `goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '拼团商品id',
  `people` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '成团人数',
  `actual_people` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '当前已拼人数',
  `creator_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '团长用户id',
  `end_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '拼单结束时间',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '拼单状态(0未拼单 10拼单中 20拼单成功 30拼单失败)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`active_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '拼团拼单记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_active_users
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_active_users`;
CREATE TABLE `yoshop_sharing_active_users`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `active_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '拼单id',
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '拼团订单id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `is_creator` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否为创建者',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '拼团拼单成员记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_category
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_category`;
CREATE TABLE `yoshop_sharing_category`  (
  `category_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '商品分类id',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '分类名称',
  `parent_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '上级分类id',
  `image_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分类图片id',
  `sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序方式(数字越小越靠前)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`category_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '拼团商品分类表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_comment
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_comment`;
CREATE TABLE `yoshop_sharing_comment`  (
  `comment_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '评价id',
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '拼团订单id',
  `goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '拼团商品id',
  `order_goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单商品id',
  `score` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '评分(10好评 20中评 30差评)',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '评价内容',
  `is_picture` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否为图片评价',
  `sort` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '评价排序',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态(0隐藏 1显示)',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `is_delete` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '软删除',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`comment_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '拼团商品评价表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_comment_image
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_comment_image`;
CREATE TABLE `yoshop_sharing_comment_image`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `comment_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '评价id',
  `image_id` int(11) NOT NULL DEFAULT 0 COMMENT '图片id(关联文件记录表)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '拼团评价图片记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_goods
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_goods`;
CREATE TABLE `yoshop_sharing_goods`  (
  `goods_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '拼团商品id',
  `goods_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品名称',
  `category_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品分类id',
  `selling_point` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品卖点',
  `people` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '成团人数',
  `group_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '成团有效时间(单位:小时)',
  `is_alone` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否允许单买(0不允许 1允许)',
  `spec_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品规格(10单规格 20多规格)',
  `deduct_stock_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 20 COMMENT '库存计算方式(10下单减库存 20付款减库存)',
  `content` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '商品详情',
  `sales_initial` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '初始销量',
  `sales_actual` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '实际销量',
  `goods_sort` int(11) UNSIGNED NOT NULL DEFAULT 100 COMMENT '商品排序(数字越小越靠前)',
  `delivery_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '配送模板id',
  `is_points_gift` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否开启积分赠送(1开启 0关闭)',
  `is_points_discount` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否允许使用积分抵扣(1允许 0不允许)',
  `is_enable_grade` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否开启会员折扣(1开启 0关闭)',
  `is_alone_grade` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员折扣设置(0默认等级折扣 1单独设置折扣)',
  `alone_grade_equity` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '单独设置折扣的配置',
  `is_ind_dealer` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否开启单独分销(0关闭 1开启)',
  `dealer_money_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '分销佣金类型(10百分比 20固定金额)',
  `first_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '分销佣金(一级)',
  `second_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '分销佣金(二级)',
  `third_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '分销佣金(三级)',
  `goods_status` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '商品状态(10上架 20下架)',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`goods_id`) USING BTREE,
  INDEX `category_id`(`category_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '拼团商品记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_goods_image
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_goods_image`;
CREATE TABLE `yoshop_sharing_goods_image`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品id',
  `image_id` int(11) NOT NULL COMMENT '图片id(关联文件记录表)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品图片记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_goods_sku
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_goods_sku`;
CREATE TABLE `yoshop_sharing_goods_sku`  (
  `goods_sku_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '商品规格id',
  `goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品id',
  `spec_sku_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '商品sku记录索引(由规格id组成)',
  `image_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '规格图片id',
  `goods_no` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品编码',
  `sharing_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '拼团价格',
  `goods_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品价格(单买价)',
  `line_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品划线价',
  `stock_num` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '当前库存数量',
  `goods_sales` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品销量',
  `goods_weight` double UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品重量(Kg)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`goods_sku_id`) USING BTREE,
  UNIQUE INDEX `sku_idx`(`goods_id`, `spec_sku_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '拼团商品规格表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_goods_spec_rel
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_goods_spec_rel`;
CREATE TABLE `yoshop_sharing_goods_spec_rel`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品id',
  `spec_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '规格组id',
  `spec_value_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '规格值id',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '拼团商品与规格值关系记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_order
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_order`;
CREATE TABLE `yoshop_sharing_order`  (
  `order_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '订单id',
  `order_no` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `total_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品总金额(不含优惠折扣)',
  `order_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '订单金额(含优惠折扣)',
  `order_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单类型(10单独购买 20拼团)',
  `active_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '拼单id',
  `coupon_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '优惠券id',
  `coupon_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '优惠券抵扣金额',
  `points_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '积分抵扣金额',
  `points_num` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '积分抵扣数量',
  `pay_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '实际付款金额(包含运费、优惠)',
  `update_price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '后台修改的订单金额（差价）',
  `buyer_remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '买家留言',
  `pay_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 20 COMMENT '支付方式(10余额支付 20微信支付)',
  `pay_status` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '付款状态(10未付款 20已付款)',
  `pay_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '付款时间',
  `delivery_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '配送方式(10快递配送 20上门自提)',
  `extract_shop_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '自提门店id',
  `extract_clerk_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '核销店员id',
  `express_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '运费金额',
  `express_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '物流公司id',
  `express_company` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '物流公司',
  `express_no` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '物流单号',
  `delivery_status` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '发货状态(10未发货 20已发货)',
  `delivery_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '发货时间',
  `receipt_status` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '收货状态(10未收货 20已收货)',
  `receipt_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '收货时间',
  `order_status` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '订单状态(10进行中 20已取消 21待取消 30已完成)',
  `points_bonus` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '赠送的积分数量',
  `is_settled` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单是否已结算(0未结算 1已结算)',
  `is_refund` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '拼团未成功退款(0未退款 1已退款)',
  `transaction_id` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '微信支付交易号',
  `is_comment` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否已评价(0否 1是)',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`order_id`) USING BTREE,
  UNIQUE INDEX `order_no`(`order_no`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '订单记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_order_address
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_order_address`;
CREATE TABLE `yoshop_sharing_order_address`  (
  `order_address_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '地址id',
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '收货人姓名',
  `phone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '联系电话',
  `province_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '所在省份id',
  `city_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '所在城市id',
  `region_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '所在区id',
  `detail` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '详细地址',
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '拼团订单id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `tel_code` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `country` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `country_id` int(11) NULL DEFAULT NULL,
  `identitycard` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '身份证号',
  `clearancecode` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '通关代码（韩国）',
  PRIMARY KEY (`order_address_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '拼团订单收货地址记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_order_extract
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_order_extract`;
CREATE TABLE `yoshop_sharing_order_extract`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单id',
  `linkman` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '联系人姓名',
  `phone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '联系电话',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '自提订单联系方式记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_order_goods
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_order_goods`;
CREATE TABLE `yoshop_sharing_order_goods`  (
  `order_goods_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '拼团商品id',
  `goods_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品名称',
  `image_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品封面图id',
  `selling_point` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品卖点',
  `people` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '成团人数',
  `group_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '成团有效时间(单位:小时)',
  `is_alone` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否允许单买(0不允许 1允许)',
  `deduct_stock_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 20 COMMENT '库存计算方式(10下单减库存 20付款减库存)',
  `spec_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '规格类型(10单规格 20多规格)',
  `spec_sku_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品sku标识',
  `goods_sku_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品规格id',
  `goods_attr` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品规格信息',
  `content` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '商品详情',
  `goods_no` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品编码',
  `goods_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品价格(单价)',
  `line_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品划线价',
  `goods_weight` double UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品重量(Kg)',
  `is_user_grade` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否存在会员等级折扣',
  `grade_ratio` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员折扣比例(0-10)',
  `grade_goods_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '会员折扣的商品单价',
  `grade_total_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '会员折扣的总额差',
  `coupon_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '优惠券折扣金额',
  `points_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '积分金额',
  `points_num` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '积分抵扣数量',
  `points_bonus` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '赠送的积分数量',
  `total_num` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '购买数量',
  `total_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品总价(数量×单价)',
  `total_pay_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '实际付款价(包含优惠、折扣)',
  `is_ind_dealer` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否开启单独分销(0关闭 1开启)',
  `dealer_money_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '分销佣金类型(10百分比 20固定金额)',
  `first_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '分销佣金(一级)',
  `second_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '分销佣金(二级)',
  `third_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '分销佣金(三级)',
  `is_comment` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否已评价(0否 1是)',
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '拼团订单id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`order_goods_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '订单商品记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_order_refund
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_order_refund`;
CREATE TABLE `yoshop_sharing_order_refund`  (
  `order_refund_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '售后单id',
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '拼团订单id',
  `order_goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单商品id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '售后类型(10退货退款 20换货)',
  `apply_desc` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户申请原因(说明)',
  `is_agree` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商家审核状态(0待审核 10已同意 20已拒绝)',
  `refuse_desc` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商家拒绝原因(说明)',
  `refund_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '实际退款金额',
  `is_user_send` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户是否发货(0未发货 1已发货)',
  `send_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户发货时间',
  `express_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户发货物流公司id',
  `express_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户发货物流单号',
  `is_receipt` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商家收货状态(0未收货 1已收货)',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '售后单状态(0进行中 10已拒绝 20已完成 30已取消)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`order_refund_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '拼团售后单记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_order_refund_address
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_order_refund_address`;
CREATE TABLE `yoshop_sharing_order_refund_address`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `order_refund_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '售后单id',
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '收货人姓名',
  `phone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '联系电话',
  `detail` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '详细地址',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '拼团售后单退货地址记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_order_refund_image
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_order_refund_image`;
CREATE TABLE `yoshop_sharing_order_refund_image`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `order_refund_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '售后单id',
  `image_id` int(11) NOT NULL DEFAULT 0 COMMENT '图片id(关联文件记录表)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '拼团售后单图片记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_setting
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_setting`;
CREATE TABLE `yoshop_sharing_setting`  (
  `key` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '设置项标示',
  `describe` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '设置项描述',
  `values` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '设置内容(json格式)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  UNIQUE INDEX `unique_key`(`key`, `wxapp_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '拼团设置表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_tr_banner
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_tr_banner`;
CREATE TABLE `yoshop_sharing_tr_banner`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `file_id` int(11) NULL DEFAULT NULL COMMENT '文件ID',
  `create_time` int(11) NULL DEFAULT NULL,
  `update_time` int(11) NULL DEFAULT NULL,
  `wxapp_id` int(11) NULL DEFAULT NULL,
  `url` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `yoshop_sharing_tr_banner_id_uindex`(`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '拼团轮播图表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_tr_order
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_tr_order`;
CREATE TABLE `yoshop_sharing_tr_order`  (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '拼团单号',
  `title` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '标题 [如: 集运1000公斤优惠拼团活动]',
  `country_id` int(11) NULL DEFAULT NULL COMMENT '国家ID',
  `storage_id` int(11) NULL DEFAULT NULL COMMENT '仓库ID',
  `predict_weight` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '预估 拼团重量',
  `max_people` int(11) NULL DEFAULT 50 COMMENT '拼团最多参与人数',
  `group_leader_remark` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '团长备注',
  `start_time` int(12) NULL DEFAULT NULL COMMENT '开团时间',
  `end_time` int(12) NULL DEFAULT NULL COMMENT '拼团结束时间',
  `status` tinyint(3) NULL DEFAULT 2 COMMENT '【1 开团中 2 待开团 3 待打包 4 待付款 5 待发货 6 已发货 7 已完成 8 已取消】',
  `disband_time` int(11) NULL DEFAULT NULL COMMENT '解散时间',
  `disband_reason` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '解散原因',
  `disband_member_id` int(11) NULL DEFAULT NULL COMMENT '解散人',
  `address_id` int(11) NULL DEFAULT NULL COMMENT '团长拼团地址',
  `member_id` int(11) NOT NULL COMMENT '发起人ID',
  `wxapp_id` int(11) NULL DEFAULT NULL,
  `pay_type` tinyint(3) NULL DEFAULT 1 COMMENT '付款方式 [1 各自付款 2 团长付款]',
  `line_id` int(11) NULL DEFAULT NULL COMMENT '线路ID',
  `inpack_id` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '国际单号',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) NULL DEFAULT NULL COMMENT '更新时间',
  `min_weight` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '最低重量',
  `is_verify` tinyint(3) NULL DEFAULT 1 COMMENT '1通过  2 待审核 3拒绝',
  `lng` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT ' 发起人 经度',
  `lat` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT ' 发起人 纬度',
  `is_recommend` tinyint(3) NULL DEFAULT 0 COMMENT ' 是否推荐 0 否 1是',
  `is_hot` tinyint(3) UNSIGNED NULL DEFAULT 0 COMMENT '是否热门 0 否 1是',
  PRIMARY KEY (`order_id`) USING BTREE,
  UNIQUE INDEX `yoshop_sharing_tr_order_order_id_uindex`(`order_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 562 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '集运拼团单 主表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_tr_order_address
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_tr_order_address`;
CREATE TABLE `yoshop_sharing_tr_order_address`  (
  `order_address_id` int(11) NOT NULL AUTO_INCREMENT,
  `is_head` int(11) NULL DEFAULT NULL COMMENT '是否为团长地址',
  `province` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `city` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `region` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `address` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `country` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `wxapp_id` int(11) NULL DEFAULT NULL,
  `update_time` int(11) NULL DEFAULT NULL,
  `create_time` int(11) NULL DEFAULT NULL,
  `order_id` int(11) NULL DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `phone` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `identitycard` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '身份证号',
  `clearancecode` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '通关代码（韩国）',
  `street` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '街道',
  `door` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '门牌号',
  `detail` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '详细地址',
  `code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '邮编',
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `tel_code` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '86' COMMENT '前缀',
  `country_id` int(11) NULL DEFAULT NULL COMMENT '国家id',
  PRIMARY KEY (`order_address_id`) USING BTREE,
  UNIQUE INDEX `yoshop_sharing_tr_order_address_order_address_id_uindex`(`order_address_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 47 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_tr_order_item
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_tr_order_item`;
CREATE TABLE `yoshop_sharing_tr_order_item`  (
  `order_item_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '拼团项目id',
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '拼团ID',
  `package_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '集运单ID',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  `status` tinyint(3) NULL DEFAULT 2 COMMENT '[1 已加入拼团 2 待团长审核 3 待打包 4 待付款 5 待发货 6 已发货 7 已完成 8 已取消 9 已拒绝]',
  `reject_reason` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '拒绝原因',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`order_item_id`) USING BTREE,
  UNIQUE INDEX `yoshop_sharing_tr_order_item_order_item_id_uindex`(`order_item_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 164 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '拼团项目' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_tr_setting
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_tr_setting`;
CREATE TABLE `yoshop_sharing_tr_setting`  (
  `key` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `describe` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `values` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `wxapp_id` int(11) NULL DEFAULT NULL,
  `update_time` int(11) NULL DEFAULT NULL
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharing_user
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharing_user`;
CREATE TABLE `yoshop_sharing_user`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NULL DEFAULT NULL,
  `truename` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '姓名',
  `idcard` varchar(18) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '身份证',
  `idcard_font` int(11) NULL DEFAULT NULL COMMENT '正面',
  `idcard_background` int(11) NULL DEFAULT NULL COMMENT '反面',
  `idcard_hand` int(11) NULL DEFAULT NULL COMMENT '手持身份证',
  `create_time` int(11) NULL DEFAULT NULL,
  `update_time` int(11) NULL DEFAULT NULL,
  `mobile` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '手机号码',
  `status` tinyint(3) NULL DEFAULT 2 COMMENT '[1 正常 2 待审核 3 已拒绝]',
  `reason` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '拒绝理由',
  `wxapp_id` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `yoshop_sharing_user_id_uindex`(`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 45 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharp_active
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharp_active`;
CREATE TABLE `yoshop_sharp_active`  (
  `active_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '活动会场ID',
  `active_date` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '活动日期',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '活动状态(0禁用 1启用)',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`active_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '整点秒杀-活动会场表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharp_active_goods
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharp_active_goods`;
CREATE TABLE `yoshop_sharp_active_goods`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `active_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '活动会场ID',
  `active_time_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '活动场次ID',
  `sharp_goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '秒杀商品ID',
  `sales_actual` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '实际销量',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '整点秒杀-活动会场与商品关联表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharp_active_time
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharp_active_time`;
CREATE TABLE `yoshop_sharp_active_time`  (
  `active_time_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '场次ID',
  `active_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '活动会场ID',
  `active_time` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '场次时间(0点-23点)',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '活动状态(0禁用 1启用)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`active_time_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '整点秒杀-活动会场场次表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharp_goods
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharp_goods`;
CREATE TABLE `yoshop_sharp_goods`  (
  `sharp_goods_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '秒杀商品ID',
  `goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品ID',
  `deduct_stock_type` tinyint(3) UNSIGNED NULL DEFAULT 10 COMMENT '库存计算方式(10下单减库存 20付款减库存)',
  `limit_num` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '限购数量',
  `seckill_stock` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品库存总量',
  `total_sales` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '累积销量',
  `sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品排序(数字越小越靠前)',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '商品状态(0下架 1上架)',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`sharp_goods_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '整点秒杀-商品表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharp_goods_sku
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharp_goods_sku`;
CREATE TABLE `yoshop_sharp_goods_sku`  (
  `goods_sku_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '商品规格id',
  `spec_sku_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '商品sku记录索引 (由规格id组成)',
  `sharp_goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '秒杀商品id',
  `seckill_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品价格',
  `seckill_stock` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '秒杀库存数量',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`goods_sku_id`) USING BTREE,
  UNIQUE INDEX `sku_idx`(`sharp_goods_id`, `spec_sku_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '整点秒杀-秒杀商品sku信息表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_sharp_setting
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_sharp_setting`;
CREATE TABLE `yoshop_sharp_setting`  (
  `key` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '设置项标示',
  `describe` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '设置项描述',
  `values` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '设置内容(json格式)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  UNIQUE INDEX `unique_key`(`key`, `wxapp_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '整点秒杀设置表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_shelf
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_shelf`;
CREATE TABLE `yoshop_shelf`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `shelf_no` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '货架编号',
  `shelf_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '货架名称',
  `ware_no` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '对应仓库',
  `wxapp_id` int(11) NULL DEFAULT NULL COMMENT '所属平台',
  `shelf_row` int(11) NULL DEFAULT NULL COMMENT '列',
  `shelf_column` int(11) NULL DEFAULT NULL COMMENT '行',
  `status` tinyint(3) NULL DEFAULT NULL COMMENT '状态 0 禁用 1 启用',
  `created_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 413 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_shelf_unit
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_shelf_unit`;
CREATE TABLE `yoshop_shelf_unit`  (
  `shelf_unit_id` int(11) NOT NULL AUTO_INCREMENT,
  `shelf_unit_no` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '货位号',
  `shelf_unit_qrcode` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '货位二维码',
  `shelf_unit_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '货位二维码数据',
  `shelf_id` int(11) NULL DEFAULT NULL COMMENT '货架ID',
  `shelf_unit_floor` int(255) NULL DEFAULT NULL COMMENT '货架层数',
  `wxapp_id` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`shelf_unit_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 20816 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_shelf_unit_item
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_shelf_unit_item`;
CREATE TABLE `yoshop_shelf_unit_item`  (
  `shelf_unit_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `shelf_unit_id` int(11) NULL DEFAULT NULL,
  `pack_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `wxapp_id` int(11) NULL DEFAULT NULL,
  `created_time` datetime(0) NULL DEFAULT NULL,
  `express_num` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `user_id` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`shelf_unit_item_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 242868 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_site_sms
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_site_sms`;
CREATE TABLE `yoshop_site_sms`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NULL DEFAULT NULL,
  `content` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `is_read` tinyint(3) NULL DEFAULT 0 COMMENT '0 未读  1已读',
  `created_time` datetime(0) NULL DEFAULT NULL,
  `updated_time` datetime(0) NULL DEFAULT NULL,
  `wxapp_id` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 39 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_spec
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_spec`;
CREATE TABLE `yoshop_spec`  (
  `spec_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '规格组id',
  `spec_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '规格组名称',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`spec_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品规格组记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_spec_value
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_spec_value`;
CREATE TABLE `yoshop_spec_value`  (
  `spec_value_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '规格值id',
  `spec_value` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '规格值',
  `spec_id` int(11) NOT NULL COMMENT '规格组id',
  `wxapp_id` int(11) NOT NULL COMMENT '小程序id',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`spec_value_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品规格值记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_store_access
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_store_access`;
CREATE TABLE `yoshop_store_access`  (
  `access_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '权限名称',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '权限url',
  `parent_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级id',
  `sort` tinyint(3) UNSIGNED NOT NULL DEFAULT 100 COMMENT '排序(数字越小越靠前)',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`access_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11215 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商家用户权限表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_store_role
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_store_role`;
CREATE TABLE `yoshop_store_role`  (
  `role_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '角色id',
  `role_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '角色名称',
  `parent_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级角色id',
  `sort` tinyint(3) UNSIGNED NOT NULL DEFAULT 100 COMMENT '排序(数字越小越靠前)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`role_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 60 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商家用户角色表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_store_role_access
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_store_role_access`;
CREATE TABLE `yoshop_store_role_access`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `role_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '角色id',
  `access_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '权限id',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `role_id`(`role_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12565 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商家用户角色权限关系表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_store_shop
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_store_shop`;
CREATE TABLE `yoshop_store_shop`  (
  `shop_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '仓库id',
  `shop_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '仓库名称',
  `shop_alias_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '仓库别名简称',
  `logo_image_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '仓库logo图片id',
  `linkman` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '联系人',
  `phone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '联系电话',
  `shop_hours` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '营业时间',
  `province_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '所在省份id',
  `post` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '邮编',
  `country_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '海外仓绑定国家',
  `city_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '所在城市id',
  `region_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '所在辖区id',
  `address` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '详细地址',
  `longitude` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '仓库坐标经度',
  `latitude` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '仓库坐标纬度',
  `geohash` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '高德地图hash码',
  `summary` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '门店简介',
  `sort` tinyint(3) NOT NULL DEFAULT 0 COMMENT '门店排序(数字越小越靠前)',
  `is_check` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否支持自提核销(0否 1支持)',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '门店状态(0禁用 1启用)',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '国内0 国外1',
  `code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '邮编',
  `is_join` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 自营默认  1加盟',
  `send_bonus` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '寄件分成比例',
  `pick_bonus` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '取件分成比例',
  `service_bonus` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '服务项目分成比例',
  `create_time` int(11) NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT 0 COMMENT '更新时间',
  `income` decimal(11, 2) NOT NULL COMMENT '仓库收益，可提现部分',
  `freeze_income` decimal(11, 2) NOT NULL COMMENT '冻结部分，暂时不可提现',
  `total_money` decimal(11, 2) NOT NULL COMMENT '累计收益',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '仓管员id（微信零钱收钱的人）',
  `is_see` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '不对外公开0  默认1',
  PRIMARY KEY (`shop_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 150 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '仓库记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_store_shop_apply
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_store_shop_apply`;
CREATE TABLE `yoshop_store_shop_apply`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '仓库名称',
  `linkman` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '联系人',
  `phone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '电话',
  `code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '邮编',
  `address` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '详细地址',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '默认0 审核中  1审核通过  2拒绝',
  `reason` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '拒绝理由',
  `create_time` int(11) UNSIGNED NOT NULL COMMENT '申请时间',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '申请用户id',
  `referee_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '推广人id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10012 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '仓库加盟申请' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_store_shop_bonus
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_store_shop_bonus`;
CREATE TABLE `yoshop_store_shop_bonus`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '仓库id',
  `line_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '线路id，或者服务项目id',
  `bonus_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '默认0 固定金额  1按比例分成',
  `sr_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 寄件  1取件',
  `proportion` decimal(11, 2) NOT NULL DEFAULT 0.00 COMMENT '金额或者比例',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属商家',
  `source` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '分成类型，10 仓库集运分成  20 服务项目',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10019 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '分红规则表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_store_shop_capital
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_store_shop_capital`;
CREATE TABLE `yoshop_store_shop_capital`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `shop_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'shop_id',
  `inpack_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '来自哪个集运单',
  `flow_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '资金流动类型 (10寄件收入  20 收件收入 30提现支出)',
  `money` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '金额',
  `describe` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '描述',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3757 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '加盟商资金明细表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_store_shop_clerk
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_store_shop_clerk`;
CREATE TABLE `yoshop_store_shop_clerk`  (
  `clerk_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '店员id',
  `shop_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属门店id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `real_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '店员姓名',
  `mobile` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '密码',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态(0禁用 1启用)',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT 0 COMMENT '更新时间',
  `clerk_type` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '员工类型(1普通店员 2分拣员 3打包员 4签收员 5仓管员 6到达仓入库员 7客服人员)',
  `mes_status` tinyint(3) NULL DEFAULT 0 COMMENT '打包通知  0默认启动  1禁用',
  `send_status` tinyint(3) UNSIGNED NULL DEFAULT 1 COMMENT '发货通知  0默认启动  1禁用',
  PRIMARY KEY (`clerk_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 227 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '平台仓库店员表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_store_shop_order
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_store_shop_order`;
CREATE TABLE `yoshop_store_shop_order`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单id',
  `order_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '订单类型(10商城订单 20拼团订单)',
  `shop_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '门店id',
  `clerk_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '核销员id',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商家门店核销订单记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_store_shop_setting
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_store_shop_setting`;
CREATE TABLE `yoshop_store_shop_setting`  (
  `key` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '设置项标示',
  `describe` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '设置项描述',
  `values` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '设置内容(json格式)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  UNIQUE INDEX `unique_key`(`key`, `wxapp_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '加盟商设置表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_store_shop_withdraw
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_store_shop_withdraw`;
CREATE TABLE `yoshop_store_shop_withdraw`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `shop_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'shop_id',
  `money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '提现金额',
  `pay_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '打款方式 (10微信 20支付宝 30银行卡 40私下转账)',
  `alipay_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '支付宝姓名',
  `alipay_account` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '支付宝账号',
  `bank_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '开户行名称',
  `bank_account` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '银行开户名',
  `bank_card` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '银行卡号',
  `weixinhao` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '个人微信号',
  `apply_status` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '申请状态 (10待审核 20审核通过 30驳回 40已打款)',
  `audit_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '审核时间',
  `reject_reason` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '驳回原因',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  `cash_from` tinyint(3) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10015 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '加盟商提现明细表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_store_user
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_store_user`;
CREATE TABLE `yoshop_store_user`  (
  `store_user_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `user_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '登录密码',
  `real_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '姓名',
  `is_super` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否为超级管理员',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `shop_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '所属仓库 0 所有仓库  ',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`store_user_id`) USING BTREE,
  INDEX `user_name`(`user_name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10128 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商家用户记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_store_user_role
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_store_user_role`;
CREATE TABLE `yoshop_store_user_role`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `store_user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '超管用户id',
  `role_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '角色id',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `admin_user_id`(`store_user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 149 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商家用户角色记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_updatelog
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_updatelog`;
CREATE TABLE `yoshop_updatelog`  (
  `log_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `log_title` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '更新标题',
  `log_content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '更新内容',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `sort` int(11) UNSIGNED NOT NULL DEFAULT 100 COMMENT '排序',
  PRIMARY KEY (`log_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10012 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_upload_file
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_upload_file`;
CREATE TABLE `yoshop_upload_file`  (
  `file_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '文件id',
  `storage` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '存储方式',
  `group_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文件分组id',
  `file_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '存储域名',
  `file_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '文件路径',
  `file_size` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文件大小(字节)',
  `file_type` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '文件类型',
  `extension` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '文件扩展名',
  `is_user` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否为c端用户上传',
  `is_recycle` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否已回收',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '软删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`file_id`) USING BTREE,
  UNIQUE INDEX `path_idx`(`file_name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 317745 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文件库记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_upload_group
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_upload_group`;
CREATE TABLE `yoshop_upload_group`  (
  `group_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '分类id',
  `group_type` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '文件类型',
  `group_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '分类名称',
  `sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分类排序(数字越小越靠前)',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`group_id`) USING BTREE,
  INDEX `type_index`(`group_type`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 21 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文件库分组记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_user
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_user`;
CREATE TABLE `yoshop_user`  (
  `user_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `user_code` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '用户编号',
  `union_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'unionid(对应开放平台)',
  `open_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '微信openid(唯一标示)',
  `gzh_openid` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '微信公众号open_id',
  `paytype` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 付款发货  1 货到付款  2  月结 ',
  `u_source` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '1 小程序  2公众号  3pc端手机端',
  `birthday` timestamp(0) NOT NULL ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '生日',
  `wechat` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '微信号',
  `identification_card` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '身份证号',
  `user_image_id` int(11) NULL DEFAULT 0 COMMENT '身份证图片',
  `nickName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '微信昵称',
  `tel_code` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '86' COMMENT '手机号前缀',
  `mobile` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '手机号',
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '用于系统登录',
  `avatarUrl` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '微信头像',
  `gender` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '性别',
  `country` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '国家',
  `province` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '省份',
  `city` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '城市',
  `address_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '默认收货地址',
  `balance` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '用户可用余额',
  `points` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户可用积分',
  `pay_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '用户总支付的金额',
  `expend_money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '实际消费的金额(不含退款)',
  `grade_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员等级id',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  `user_type` tinyint(3) UNSIGNED NULL DEFAULT 0 COMMENT '用户类型(0普通用户 1 普通用户 2 仓管员 3 拣货员   4 打包员)',
  `income` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '用户收益',
  `freeze_income` decimal(10, 2) NULL DEFAULT NULL COMMENT '冻结收益',
  `service_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '绑定的客服专员',
  `platform` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `last_login_time` timestamp(0) NULL DEFAULT NULL,
  `referee_id` int(11) NULL DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '用于PC 端 用户系统',
  `email_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '邮箱验证辅助字段',
  `contact_address` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT 'PC 端 独有字段 [联系地址]',
  `contact_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT 'PC 端独有字段 [联系人姓名]',
  `post_code` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT 'PC 端 独有字段',
  `real_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '真实姓名',
  `is_sharp` tinyint(3) NULL DEFAULT NULL COMMENT '是否未团长 [1:是 0:否]',
  PRIMARY KEY (`user_id`) USING BTREE,
  INDEX `openid`(`open_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 18622 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_user_address
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_user_address`;
CREATE TABLE `yoshop_user_address`  (
  `address_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '收货人姓名',
  `tel_code` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '86' COMMENT '手机号前缀',
  `phone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '联系电话',
  `country` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '国家',
  `country_id` int(11) NULL DEFAULT NULL COMMENT '国家id',
  `province` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '所在省份id',
  `city` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '所在城市id',
  `region` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '所在区id',
  `district` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '新市辖区(该字段用于记录region表中没有的市辖区)',
  `detail` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '详细地址',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '邮箱',
  `code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '邮编',
  `street` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  `door` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '门牌号',
  `address_type` tinyint(3) NULL DEFAULT 0 COMMENT '0 集运地址  1 商城地址   2  代收点地址',
  `clearancecode` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '通关代码（韩国）',
  `identitycard` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '身份证号',
  `addressty` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '默认0 收件人  1寄件人',
  `takecode` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '取货邮编列表',
  `is_moren` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0，  1默认地址',
  PRIMARY KEY (`address_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11922 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户收货地址表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_user_balance_log
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_user_balance_log`;
CREATE TABLE `yoshop_user_balance_log`  (
  `log_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `scene` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '余额变动场景(10用户充值 20用户消费 30管理员操作 40订单退款 50分销佣金)',
  `money` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '变动金额',
  `describe` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '描述/说明',
  `remark` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '管理员备注',
  `sence_type` tinyint(3) NULL DEFAULT 1 COMMENT '[1 recharge  2 withdraw 3 ]',
  `type` tinyint(3) NULL DEFAULT 1 COMMENT '0',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序商城id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`log_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6934 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户余额变动明细表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_user_coupon
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_user_coupon`;
CREATE TABLE `yoshop_user_coupon`  (
  `user_coupon_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `coupon_id` int(11) UNSIGNED NOT NULL COMMENT '优惠券id',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '优惠券名称',
  `color` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '优惠券颜色(10蓝 20红 30紫 40黄)',
  `coupon_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '优惠券类型(10满减券 20折扣券)',
  `reduce_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '满减券-减免金额',
  `discount` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '折扣券-折扣率(0-100)',
  `min_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '最低消费金额',
  `expire_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '到期类型(10领取后生效 20固定时间)',
  `expire_day` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '领取后生效-有效天数',
  `start_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '有效期开始时间',
  `end_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '有效期结束时间',
  `apply_range` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '适用范围(10全部商品 20指定商品)',
  `is_expire` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否过期(0未过期 1已过期)',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除(0未删除 1已删除)',
  `is_use` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否已使用(0未使用 1已使用)',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`user_coupon_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1969 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户优惠券记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_user_grade
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_user_grade`;
CREATE TABLE `yoshop_user_grade`  (
  `grade_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '等级ID',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '等级名称',
  `weight` int(11) UNSIGNED NOT NULL DEFAULT 1 COMMENT '等级权重(1-9999)',
  `upgrade` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '升级条件',
  `equity` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '等级权益(折扣率0-100)',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态(1启用 0禁用)',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`grade_id`) USING BTREE,
  INDEX `wxapp_id`(`wxapp_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 50 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户会员等级表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_user_grade_log
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_user_grade_log`;
CREATE TABLE `yoshop_user_grade_log`  (
  `log_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `old_grade_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '变更前的等级id',
  `new_grade_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '变更后的等级id',
  `change_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '变更类型(10后台管理员设置 20自动升级)',
  `remark` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '管理员备注',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`log_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 194 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户会员等级变更记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_user_line
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_user_line`;
CREATE TABLE `yoshop_user_line`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NULL DEFAULT NULL COMMENT '用户编号',
  `line_id` int(11) NULL DEFAULT NULL COMMENT '路线id',
  `discount` decimal(3, 2) NULL DEFAULT NULL COMMENT '折扣',
  `create_time` int(12) NULL DEFAULT NULL COMMENT '创建时间',
  `wxapp_id` int(11) NULL DEFAULT NULL,
  `update_time` int(12) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10045 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_user_mark
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_user_mark`;
CREATE TABLE `yoshop_user_mark`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `mark` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '唛头',
  `markdes` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '唛头描述',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 243 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_user_points_log
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_user_points_log`;
CREATE TABLE `yoshop_user_points_log`  (
  `log_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `value` int(11) NOT NULL DEFAULT 0 COMMENT '变动数量',
  `describe` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '描述/说明',
  `remark` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '管理员备注',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序商城id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`log_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户积分变动明细表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_wow_order
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_wow_order`;
CREATE TABLE `yoshop_wow_order`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单id',
  `order_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '订单类型(10商城订单 20拼团订单)',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单状态(3支付完成 4已发货 5已退款 100已完成)',
  `last_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后更新时间',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '好物圈订单同步记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_wow_setting
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_wow_setting`;
CREATE TABLE `yoshop_wow_setting`  (
  `key` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '设置项标示',
  `describe` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '设置项描述',
  `values` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '设置内容(json格式)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  UNIQUE INDEX `unique_key`(`key`, `wxapp_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '好物圈设置表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_wow_shoping
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_wow_shoping`;
CREATE TABLE `yoshop_wow_shoping`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `goods_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '好物圈商品收藏记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_wxapp
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_wxapp`;
CREATE TABLE `yoshop_wxapp`  (
  `wxapp_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '小程序id',
  `token` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'api电子秤token',
  `app_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '小程序AppID',
  `app_secret` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '小程序AppSecret',
  `app_wxname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '公众号名称',
  `app_wxrealid` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '公众号原始id',
  `app_wxappid` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '微信公众号appid',
  `app_wxsecret` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '公众号密钥',
  `mchid` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '微信商户号id',
  `apikey` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '微信支付密钥',
  `cert_pem` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '证书文件cert',
  `key_pem` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '证书文件key',
  `is_recycle` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否回收',
  `end_time` int(12) NULL DEFAULT NULL COMMENT '有效期',
  `baiduai` int(11) UNSIGNED NOT NULL DEFAULT 20 COMMENT '默认赠送20个',
  `ware_num` int(11) NULL DEFAULT NULL COMMENT '仓库数量',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  `system_style` tinyint(3) NULL DEFAULT 10 COMMENT '10原始 20 blue 30 加了个背景的',
  `copyright` tinyint(3) NULL DEFAULT 0 COMMENT '默认0 1为显示',
  `copyright_des` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '版权说明',
  `copyright_phone` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '版权电话',
  `url_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'url_encode',
  `version` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '版本',
  `other_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '跳转域名',
  `filing_number` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备案号',
  `wx_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '默认0=用小程序的  1=用公众号的',
  PRIMARY KEY (`wxapp_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10046 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '微信小程序记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_wxapp_category
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_wxapp_category`;
CREATE TABLE `yoshop_wxapp_category`  (
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `category_style` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '分类页样式(10一级分类[大图] 11一级分类[小图] 20二级分类)',
  `share_title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '分享标题',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`wxapp_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '微信小程序分类页模板' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_wxapp_formid
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_wxapp_formid`;
CREATE TABLE `yoshop_wxapp_formid`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `form_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '小程序form_id',
  `expiry_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '过期时间',
  `is_used` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否已使用',
  `used_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '使用时间',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '小程序form_id记录表(已废弃)' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_wxapp_help
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_wxapp_help`;
CREATE TABLE `yoshop_wxapp_help`  (
  `help_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '帮助标题',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '帮助内容',
  `sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序(数字越小越靠前)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`help_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10057 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '微信小程序帮助' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_wxapp_live_room
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_wxapp_live_room`;
CREATE TABLE `yoshop_wxapp_live_room`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `room_id` int(11) UNSIGNED NOT NULL COMMENT '直播间id',
  `room_name` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '直播间名称',
  `cover_img` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '分享卡片封面',
  `share_img` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '直播间背景墙封面',
  `anchor_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '主播昵称',
  `start_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '开播时间',
  `end_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '结束时间',
  `live_status` tinyint(3) UNSIGNED NOT NULL DEFAULT 102 COMMENT '直播状态(101: 直播中, 102: 未开始, 103: 已结束, 104: 禁播, 105: 暂停中, 106: 异常, 107: 已过期)',
  `is_top` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '置顶状态(0未置顶 1已置顶)',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '软删除(0未删除 1已删除)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `room_id`(`room_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '微信小程序直播间记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_wxapp_nav
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_wxapp_nav`;
CREATE TABLE `yoshop_wxapp_nav`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '导航名称',
  `nav_icon` int(11) NULL DEFAULT NULL COMMENT '导航图标id',
  `nav_linktype` tinyint(3) NULL DEFAULT 1 COMMENT '1 小程序内部链接  2外部链接',
  `nav_link` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '链接地址',
  `sort` int(11) NULL DEFAULT NULL COMMENT '排序',
  `is_use` int(11) NOT NULL DEFAULT 0 COMMENT '0 是  1否',
  `create_time` int(12) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(12) NULL DEFAULT NULL COMMENT '更新时间',
  `wxapp_id` int(11) NOT NULL COMMENT '所属商家',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 151 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '菜单导航表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_wxapp_page
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_wxapp_page`;
CREATE TABLE `yoshop_wxapp_page`  (
  `page_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '页面id',
  `page_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '页面类型(10首页 20自定义页)',
  `page_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '页面名称',
  `page_data` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '页面数据',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '微信小程序id',
  `is_delete` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '软删除',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`page_id`) USING BTREE,
  INDEX `wxapp_id`(`wxapp_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10046 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '微信小程序diy页面表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for yoshop_wxapp_prepay_id
-- ----------------------------
DROP TABLE IF EXISTS `yoshop_wxapp_prepay_id`;
CREATE TABLE `yoshop_wxapp_prepay_id`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单id',
  `order_type` tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '订单类型(10商城订单 20拼团订单)',
  `prepay_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '微信支付prepay_id',
  `can_use_times` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '可使用次数',
  `used_times` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '已使用次数',
  `pay_status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '支付状态(1已支付)',
  `wxapp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序id',
  `expiry_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '过期时间',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `order_id`(`order_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '小程序prepay_id记录(已废弃)' ROW_FORMAT = Compact;

SET FOREIGN_KEY_CHECKS = 1;
