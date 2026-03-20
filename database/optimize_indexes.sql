-- ============================================
-- Database Index Optimization Script
-- Analyze and optimize indexes for key tables
-- Run monthly after archive script
-- ============================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- 1. Order Table Index Analysis & Optimization
-- ============================================

-- Current index analysis
SELECT 'Analyzing yoshop_order table...' AS task;

-- Show current indexes
SHOW INDEX FROM `yoshop_order`;

-- Drop redundant/unused indexes (if any exist)
-- ALTER TABLE `yoshop_order` DROP INDEX `idx_unused_index`;

-- Add composite index for common query patterns
-- Pattern: WHERE user_id AND order_status ORDER BY created_time DESC
ALTER TABLE `yoshop_order` 
ADD INDEX `idx_user_status_time` (`user_id`, `order_status`, `created_time` DESC);

-- Add index for order status queries
ALTER TABLE `yoshop_order` 
ADD INDEX `idx_status_pay_time` (`order_status`, `pay_time`);

-- Add index for shipping queries
ALTER TABLE `yoshop_order` 
ADD INDEX `idx_shipping` (`shipping_time`, `express_num`) 
WHERE `shipping_time` IS NOT NULL;

-- Add index for financial queries
ALTER TABLE `yoshop_order` 
ADD INDEX `idx_payment` (`real_payment`, `pay_time`);

-- Analyze table statistics
ANALYZE TABLE `yoshop_order`;

-- Optimize table (defragmentation)
OPTIMIZE TABLE `yoshop_order`;


-- ============================================
-- 2. Platform Account Table Index Optimization
-- ============================================

SELECT 'Analyzing yoshop_platform_account table...' AS task;

-- Show current indexes
SHOW INDEX FROM `yoshop_platform_account`;

-- The unique constraint already provides good indexing
-- uk_user_platform: (user_id, platform_type, customer_id, wxapp_id)

-- Add additional index for customer_id lookups (Bot queries)
ALTER TABLE `yoshop_platform_account` 
ADD INDEX `idx_customer_wxapp` (`customer_id`, `wxapp_id`, `status`);

-- Add index for binding time queries (for archiving)
ALTER TABLE `yoshop_platform_account` 
ADD INDEX `idx_binding_time` (`binding_time`, `last_verify_time`, `status`);

-- Analyze and optimize
ANALYZE TABLE `yoshop_platform_account`;
OPTIMIZE TABLE `yoshop_platform_account`;


-- ============================================
-- 3. Package Table Additional Indexes
-- ============================================

SELECT 'Analyzing yoshop_package table...' AS task;

SHOW INDEX FROM `yoshop_package`;

-- Existing indexes from database_performance_indexes.sql:
-- idx_member_status_delete_time (member_id, status, is_delete, created_time)
-- idx_express_num (express_num)
-- idx_storage_id (storage_id)
-- idx_country_id (country_id)

-- Add index for status-based queries (warehouse operations)
ALTER TABLE `yoshop_package` 
ADD INDEX `idx_status_warehouse` (`status`, `storage_id`, `is_delete`);

-- Add index for weight/volume queries (shipping calculations)
ALTER TABLE `yoshop_package` 
ADD INDEX `idx_dimensions` (`weight`, `volume`) 
WHERE `weight` > 0 OR `volume` > 0;

-- Analyze and optimize
ANALYZE TABLE `yoshop_package`;
OPTIMIZE TABLE `yoshop_package`;


-- ============================================
-- 4. Logistics Table Index Optimization
-- ============================================

SELECT 'Analyzing yoshop_logistics table...' AS task;

SHOW INDEX FROM `yoshop_logistics`;

-- Add composite index for tracking queries
ALTER TABLE `yoshop_logistics` 
ADD INDEX `idx_order_tracking` (`order_sn`, `created_time` DESC);

-- Add index for express number queries
ALTER TABLE `yoshop_logistics` 
ADD INDEX `idx_express_lookup` (`express_num`, `status`);

-- Analyze and optimize
ANALYZE TABLE `yoshop_logistics`;
OPTIMIZE TABLE `yoshop_logistics`;


-- ============================================
-- 5. Inpack Table Index Optimization
-- ============================================

SELECT 'Analyzing yoshop_inpack table...' AS task;

SHOW INDEX FROM `yoshop_inpack`;

-- Add index for status-based processing
ALTER TABLE `yoshop_inpack` 
ADD INDEX `idx_status_process` (`status`, `created_time`, `is_delete`);

-- Add index for order lookup
ALTER TABLE `yoshop_inpack` 
ADD INDEX `idx_order_lookup` (`order_sn`, `t_order_sn`);

-- Analyze and optimize
ANALYZE TABLE `yoshop_inpack`;
OPTIMIZE TABLE `yoshop_inpack`;


-- ============================================
-- 6. Archive Tables Indexes
-- ============================================

SELECT 'Creating indexes for archive tables...' AS task;

-- Order Archive indexes
CREATE TABLE IF NOT EXISTS `yoshop_order_archive` (
  `archive_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `original_order_id` INT UNSIGNED NOT NULL,
  `order_sn` VARCHAR(50) NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `order_status` TINYINT UNSIGNED NOT NULL,
  `payment` DECIMAL(10,2) DEFAULT NULL,
  `created_time` DATETIME NOT NULL,
  `pay_time` DATETIME DEFAULT NULL,
  `shipping_time` DATETIME DEFAULT NULL,
  `completed_time` DATETIME DEFAULT NULL,
  `archived_time` DATETIME NOT NULL,
  `wxapp_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`archive_id`),
  KEY `idx_original_id` (`original_order_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_archived_time` (`archived_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Package Archive indexes
CREATE TABLE IF NOT EXISTS `yoshop_package_archive` (
  `archive_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `original_package_id` INT UNSIGNED NOT NULL,
  `package_code` VARCHAR(100) NOT NULL,
  `member_id` INT UNSIGNED NOT NULL,
  `status` TINYINT UNSIGNED NOT NULL,
  `weight` DECIMAL(10,2) DEFAULT NULL,
  `volume` DECIMAL(10,2) DEFAULT NULL,
  `created_time` DATETIME NOT NULL,
  `archived_time` DATETIME NOT NULL,
  `wxapp_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`archive_id`),
  KEY `idx_original_id` (`original_package_id`),
  KEY `idx_member_id` (`member_id`),
  KEY `idx_archived_time` (`archived_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Platform Account Archive indexes
CREATE TABLE IF NOT EXISTS `yoshop_platform_account_archive` (
  `archive_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `original_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `customer_id` VARCHAR(100) NOT NULL,
  `platform_type` VARCHAR(20) NOT NULL,
  `binding_time` DATETIME DEFAULT NULL,
  `last_verify_time` DATETIME DEFAULT NULL,
  `archived_time` DATETIME NOT NULL,
  `wxapp_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`archive_id`),
  KEY `idx_original_id` (`original_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_archived_time` (`archived_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Logistics Archive indexes
CREATE TABLE IF NOT EXISTS `yoshop_logistics_archive` (
  `archive_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `original_id` INT UNSIGNED NOT NULL,
  `order_sn` VARCHAR(50) NOT NULL,
  `express_num` VARCHAR(100) DEFAULT NULL,
  `status` TINYINT UNSIGNED NOT NULL,
  `logistics_describe` TEXT,
  `created_time` DATETIME NOT NULL,
  `archived_time` DATETIME NOT NULL,
  `wxapp_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`archive_id`),
  KEY `idx_original_id` (`original_id`),
  KEY `idx_order_sn` (`order_sn`),
  KEY `idx_archived_time` (`archived_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================
-- 7. Performance Analysis Queries
-- ============================================

SELECT 'Running performance analysis...' AS task;

-- Check table sizes
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
    table_rows,
    data_free
FROM information_schema.TABLES
WHERE table_schema = DATABASE()
AND table_name IN (
    'yoshop_order',
    'yoshop_package',
    'yoshop_platform_account',
    'yoshop_logistics',
    'yoshop_inpack'
)
ORDER BY size_mb DESC;

-- Check index usage statistics (MySQL 5.7+)
SELECT 
    object_name,
    index_name,
    COUNT_READ,
    COUNT_WRITE,
    NUMBER_ROWS
FROM performance_schema.table_io_waits_summary_by_index_usage
WHERE OBJECT_SCHEMA = DATABASE()
AND index_name IS NOT NULL
ORDER BY COUNT_READ DESC
LIMIT 20;


-- ============================================
-- 8. Final Optimization
-- ============================================

SELECT 'Final optimization...' AS task;

-- Update table statistics
ANALYZE TABLE `yoshop_order`;
ANALYZE TABLE `yoshop_package`;
ANALYZE TABLE `yoshop_platform_account`;
ANALYZE TABLE `yoshop_logistics`;
ANALYZE TABLE `yoshop_inpack`;

-- Reset foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

SELECT '✅ Index optimization completed!' AS status;
