-- Create order_message table
CREATE TABLE IF NOT EXISTS order_message (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_session_id BIGINT NOT NULL COMMENT 'Reference to order_session',
    message_type ENUM('user', 'bot', 'system') NOT NULL COMMENT 'Message type',
    message_content TEXT NOT NULL COMMENT 'Message content',
    message_metadata JSON COMMENT 'Additional message metadata',
    platform_message_id VARCHAR(255) COMMENT 'Platform message ID',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME,
    FOREIGN KEY (order_session_id) REFERENCES order_session(id),
    INDEX idx_message_type (message_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
