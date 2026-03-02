-- Seller features: order item status (Accept / Processing / Ready for Delivery), category requests
-- Run once. If "Duplicate column" on ALTER, column already exists.
USE ecommerce_marketplace;

ALTER TABLE order_items ADD COLUMN seller_status ENUM('pending', 'accepted', 'processing', 'ready_for_delivery') DEFAULT 'pending';

CREATE TABLE IF NOT EXISTS category_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    seller_id INT NOT NULL,
    category_name VARCHAR(100) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES sellers(seller_id) ON DELETE CASCADE
);
