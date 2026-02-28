-- 修复 yoshop_logistics 表的 operate_id 字段
ALTER TABLE `yoshop_logistics` MODIFY COLUMN `operate_id` int(11) NULL DEFAULT 0;

-- 验证修复
SHOW COLUMNS FROM `yoshop_logistics` LIKE 'operate_id';
