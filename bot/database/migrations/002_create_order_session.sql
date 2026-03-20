-- Create order_session table
CREATE TABLE IF NOT EXISTS order_session (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(255) NOT NULL UNIQUE COMMENT 'Unique session identifier',
    platform_account_id BIGINT NOT NULL COMMENT 'Reference to platform_account',
    user_id VARCHAR(255) NOT NULL COMMENT 'Platform user ID',
    user_name VARCHAR(255) COMMENT 'User display name',
    session_state VARCHAR(50) DEFAULT 'active' COMMENT 'Session state (active, completed, abandoned)',
    conversation_context JSON COMMENT 'Conversation context data',
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_activity_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at DATETIME,
    deleted_at DATETIME,
    FOREIGN KEY (platform_account_id) REFERENCES platform_account(id),
    INDEX idx_user_id (user_id),
    INDEX idx_session_state (session_state),
    INDEX idx_started_at (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
