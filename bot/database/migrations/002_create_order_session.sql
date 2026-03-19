-- Migration 002: Tạo bảng order_session
-- Mô tả: Lưu trữ phiên đặt hàng từ Facebook/Instagram

CREATE TABLE IF NOT EXISTS `order_session` (
    `id` SERIAL PRIMARY KEY,
    `customer_id` INTEGER NOT NULL COMMENT 'ID khách hàng',
    `platform_account_id` INTEGER NOT NULL COMMENT 'ID tài khoản nền tảng',

    -- Trạng thái (State Machine)
    `status` VARCHAR(20) NOT NULL DEFAULT 'collecting' COMMENT 'collecting / ready / bound / closed',

    -- Thông tin đơn hàng
    `seller_name` VARCHAR(255) COMMENT 'Tên người bán / shop',
    `buy_date` DATE COMMENT 'Ngày mua hàng',
    `item_desc` TEXT COMMENT 'Mô tả sản phẩm',
    `amount` DECIMAL(10,2) COMMENT 'Số tiền',
    `currency` VARCHAR(10) DEFAULT 'VND' COMMENT 'Đơn vị tiền tệ',
    `seller_order_no` VARCHAR(100) COMMENT 'Mã đơn hàng của người bán',

    -- Metadata
    `session_key` VARCHAR(50) UNIQUE COMMENT 'Khóa phiên (dùng cho Redis)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `closed_at` TIMESTAMP NULL COMMENT 'Thời gian đóng phiên',

    KEY `idx_customer_id` (`customer_id`),
    KEY `idx_status` (`status`),
    KEY `idx_session_key` (`session_key`),
    KEY `idx_platform_account` (`platform_account_id`)
) COMMENT='Phiên đặt hàng từ mạng xã hội';
