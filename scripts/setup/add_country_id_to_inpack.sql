ALTER TABLE `yoshop_inpack`
ADD COLUMN `country_id` int(11) NULL DEFAULT NULL COMMENT '国家ID'
AFTER `address_id`;
