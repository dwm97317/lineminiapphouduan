-- Migration 004: Tạo bảng order_message
-- Mô tả: Lưu trữ bằng chứng chat (ảnh chụp màn hình, tin nhắn chuyển tiếp)

CREATE TABLE IF NOT EXISTS `order_message` (
    `id` SERIAL PRIMARY KEY,
    `order_session_id` INTEGER NOT NULL COMMENT 'ID phiên đặt hàng',
    `message_type` VARCHAR(20) NOT NULL COMMENT 'text / image / link',
    `content` TEXT COMMENT 'Nội dung tin nhắn',
    `file_url` TEXT COMMENT 'URL ảnh/file đính kèm',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    KEY `idx_order_session` (`order_session_id`)
) COMMENT='Bằng chứng chat từ Facebook/Instagram';
