-- Migration 000: Khởi tạo database Bot
-- Chạy file này trước tất cả các migration khác

CREATE DATABASE IF NOT EXISTS `bot_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `bot_db`;

-- Ghi chú cấu trúc database:
-- platform_account  : Tài khoản FB/IG liên kết với khách hàng
-- order_session     : Phiên đặt hàng (state machine: collecting→ready→bound→closed)
-- order_package     : Mã bưu kiện liên kết với phiên đặt hàng
-- order_message     : Bằng chứng chat (ảnh, tin nhắn)

-- Thứ tự chạy migration:
-- 000_init.sql
-- 001_create_platform_account.sql
-- 002_create_order_session.sql
-- 003_create_order_package.sql
-- 004_create_order_message.sql
