-- Create platform_account table
CREATE TABLE IF NOT EXISTS platform_account (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    platform VARCHAR(50) NOT NULL COMMENT 'Platform name (LINE, Facebook, etc)',
    account_id VARCHAR(255) NOT NULL UNIQUE COMMENT 'Platform account ID',
    access_token VARCHAR(500) COMMENT 'Platform access token',
    refresh_token VARCHAR(500) COMMENT 'Platform refresh token',
    token_expires_at DATETIME COMMENT 'Token expiration time',
    account_name VARCHAR(255) COMMENT 'Account display name',
    account_email VARCHAR(255) COMMENT 'Account email',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME,
    INDEX idx_platform (platform),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
