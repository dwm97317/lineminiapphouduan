-- Create order_package table
CREATE TABLE IF NOT EXISTS order_package (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_session_id BIGINT NOT NULL COMMENT 'Reference to order_session',
    package_id VARCHAR(255) NOT NULL COMMENT 'Package identifier',
    package_name VARCHAR(255) NOT NULL COMMENT 'Package name',
    package_description TEXT COMMENT 'Package description',
    quantity INT DEFAULT 1 COMMENT 'Quantity ordered',
    unit_price DECIMAL(10, 2) NOT NULL COMMENT 'Unit price',
    total_price DECIMAL(10, 2) NOT NULL COMMENT 'Total price (quantity * unit_price)',
    package_status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME,
    FOREIGN KEY (order_session_id) REFERENCES order_session(id),
    INDEX idx_package_id (package_id),
    INDEX idx_package_status (package_status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
