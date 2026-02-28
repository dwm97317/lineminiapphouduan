-- 包裹性能优化 - 数据库索引
-- 创建时间: 2026-01-12
-- 说明: 为yoshop_package表添加索引以提升查询性能

-- 1. 复合索引：用于主查询条件 (member_id, status, is_delete, created_time)
-- 这个索引覆盖了最常用的查询条件组合
ALTER TABLE `yoshop_package` ADD INDEX `idx_member_status_delete_time` (`member_id`, `status`, `is_delete`, `created_time` DESC);

-- 2. 快递单号索引：用于搜索功能
-- 支持快递单号的精确查询和模糊搜索
ALTER TABLE `yoshop_package` ADD INDEX `idx_express_num` (`express_num`);

-- 3. 仓库ID索引：用于按仓库筛选
ALTER TABLE `yoshop_package` ADD INDEX `idx_storage_id` (`storage_id`);

-- 4. 国家ID索引：用于按国家筛选
ALTER TABLE `yoshop_package` ADD INDEX `idx_country_id` (`country_id`);

-- 查看索引创建结果
SHOW INDEX FROM `yoshop_package`;

-- 分析查询性能（示例）
-- EXPLAIN SELECT * FROM `yoshop_package` 
-- WHERE `member_id` = 1 
-- AND `status` = 2 
-- AND `is_delete` = 0 
-- ORDER BY `created_time` DESC 
-- LIMIT 20;
