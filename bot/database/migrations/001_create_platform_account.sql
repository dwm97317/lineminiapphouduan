-- Migration 001: Tạo bảng platform_account
-- Mô tả: Lưu trữ thông tin tài khoản mạng xã hội (Facebook/Instagram) của khách hàng

CREATE TABLE IF NOT EXISTS `platform_account` (
    `id` SERIAL PRIMARY KEY,
    `customer_id` INTEGER NOT NULL COMMENT 'ID khách hàng trong hệ thống vận chuyển',
    `platform` VARCHAR(20) NOT NULL COMMENT 'facebook / instagram',
    `platform_user_id` VARCHAR(100) NOT NULL COMMENT 'ID người dùng trên nền tảng',
    `platform_username` VARCHAR(100) COMMENT 'Tên người dùng trên nền tảng',
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_platform_user` (`platform`, `platform_user_id`),
    KEY `idx_customer_id` (`customer_id`),
    KEY `idx_platform_user` (`platform`, `platform_user_id`)
) COMMENT='Tài khoản mạng xã hội liên kết với khách hàng';
