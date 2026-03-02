-- Flash Sales System Database Schema

-- Flash sales table
CREATE TABLE IF NOT EXISTS flash_sales (
    sale_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    original_price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) NOT NULL,
    discount_percentage INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    stock_limit INT DEFAULT NULL,
    sold_count INT DEFAULT 0,
    status ENUM('scheduled', 'active', 'expired', 'cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES sellers(seller_id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_status_time (status, end_time),
    INDEX idx_active_sales (status, start_time, end_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Flash sale purchases tracking (to enforce stock limits)
CREATE TABLE IF NOT EXISTS flash_sale_purchases (
    purchase_id INT PRIMARY KEY AUTO_INCREMENT,
    sale_id INT NOT NULL,
    user_id INT NOT NULL,
    quantity INT NOT NULL,
    purchase_price DECIMAL(10,2) NOT NULL,
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES flash_sales(sale_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_sale_user (sale_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
