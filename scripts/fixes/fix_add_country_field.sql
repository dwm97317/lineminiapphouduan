ALTER TABLE `yoshop_inpack` ADD COLUMN `country` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '目标国家（可废弃，通过address获取）' AFTER `volume`;
