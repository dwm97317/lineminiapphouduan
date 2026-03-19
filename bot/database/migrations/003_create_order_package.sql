-- Migration 003: Tạo bảng order_package
-- Mô tả: Liên kết phiên đặt hàng với mã bưu kiện (express_num)

CREATE TABLE IF NOT EXISTS `order_package` (
    `id` SERIAL PRIMARY KEY,
    `order_session_id` INTEGER NOT NULL COMMENT 'ID phiên đặt hàng',
    `package_no` VARCHAR(100) NOT NULL COMMENT 'Mã bưu kiện (express_num) từ người bán',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_session_package` (`order_session_id`, `package_no`),
    KEY `idx_order_session` (`order_session_id`),
    KEY `idx_package_no` (`package_no`)
) COMMENT='Liên kết đơn hàng với mã bưu kiện quốc tế';

-- Ghi chú:
-- package_no = express_num trong bảng yoshop_package của hệ thống vận chuyển
-- Một đơn hàng có thể có nhiều mã bưu kiện (người bán gửi nhiều kiện)
