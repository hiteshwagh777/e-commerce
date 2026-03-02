-- Add multiple image support to products
-- Run this migration to update your existing database

-- Create product_images table
CREATE TABLE IF NOT EXISTS product_images (
    image_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    display_order INT DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_primary (product_id, is_primary)
);

-- Migrate existing product images to product_images table
INSERT INTO product_images (product_id, image_path, is_primary, display_order)
SELECT product_id, image, 1, 0 FROM products WHERE image IS NOT NULL AND image != '';

-- Optional: You can keep the image column in products table as backup, or drop it after verifying migration
-- ALTER TABLE products DROP COLUMN image;
