-- ====================================================================
-- Multiple Product Images Feature - Complete Database Setup
-- ====================================================================
-- This file contains everything needed to add multiple image support
-- to your ecommerce platform.
-- 
-- IMPORTANT: Back up your database before running this script!
-- ====================================================================

-- Step 1: Create the product_images table
-- ====================================================================
CREATE TABLE IF NOT EXISTS product_images (
    image_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    display_order INT DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_primary (product_id, is_primary),
    UNIQUE KEY unique_image_path (image_path)
);

-- Step 2: Migrate existing product images
-- ====================================================================
-- This will copy any existing images from the products table
-- to the new product_images table as primary images

INSERT IGNORE INTO product_images (product_id, image_path, is_primary, display_order)
SELECT product_id, image, 1, 0 
FROM products 
WHERE image IS NOT NULL 
AND image != ''
AND image != 'default.jpg';

-- Step 3: Verify migration
-- ====================================================================
-- Run this query to check if migration was successful

SELECT 
    COUNT(*) as total_product_images,
    SUM(CASE WHEN is_primary = 1 THEN 1 ELSE 0 END) as primary_images,
    (SELECT COUNT(*) FROM products WHERE image IS NOT NULL AND image != '') as legacy_images
FROM product_images;

-- Step 4: View products with their image galleries
-- ====================================================================
-- This helpful query shows all products and their associated images

SELECT 
    p.product_id,
    p.name,
    p.seller_id,
    COUNT(pi.image_id) as image_count,
    GROUP_CONCAT(pi.image_path ORDER BY pi.display_order SEPARATOR '|') as all_images,
    MAX(CASE WHEN pi.is_primary = 1 THEN pi.image_path END) as primary_image
FROM products p
LEFT JOIN product_images pi ON p.product_id = pi.product_id
GROUP BY p.product_id, p.name, p.seller_id
ORDER BY p.product_id DESC;

-- Step 5: Optional - Add image count tracking
-- ====================================================================
-- This creates a view to easily see image statistics

CREATE OR REPLACE VIEW product_image_stats AS
SELECT 
    p.product_id,
    p.name,
    s.shop_name,
    COUNT(pi.image_id) as total_images,
    SUM(CASE WHEN pi.is_primary = 1 THEN 1 ELSE 0 END) as primary_count,
    p.created_at,
    MAX(pi.uploaded_at) as last_image_upload
FROM products p
LEFT JOIN sellers s ON p.seller_id = s.seller_id
LEFT JOIN product_images pi ON p.product_id = pi.product_id
GROUP BY p.product_id, p.name, s.shop_name, p.created_at;

-- Step 6: Helpful Maintenance Queries
-- ====================================================================

-- Find products with NO images
SELECT p.product_id, p.name, p.seller_id 
FROM products p
WHERE p.product_id NOT IN (SELECT DISTINCT product_id FROM product_images);

-- Find duplicate images (same file name)
SELECT image_path, COUNT(*) as occurrences, GROUP_CONCAT(product_id) as products
FROM product_images
GROUP BY image_path
HAVING COUNT(*) > 1;

-- Find orphaned images (products that were deleted)
-- Note: With CASCADE delete, there shouldn't be any, but this checks anyway
SELECT image_id, product_id, image_path
FROM product_images
WHERE product_id NOT IN (SELECT product_id FROM products);

-- Step 7: Performance Optimization
-- ====================================================================
-- These indexes help queries run faster

CREATE INDEX idx_product_images_product_id ON product_images(product_id);
CREATE INDEX idx_product_images_primary ON product_images(product_id, is_primary);
CREATE INDEX idx_product_images_order ON product_images(product_id, display_order);

-- ====================================================================
-- DATABASE SETUP COMPLETE
-- ====================================================================
-- 
-- Next steps:
-- 1. Upload the updated PHP files
-- 2. Test adding products with multiple images
-- 3. Verify gallery displays correctly on product detail pages
--
-- Files to upload:
-- - seller/products.php (multiple image upload form)
-- - product_detail.php (gallery display with thumbnails)
-- - style.css (gallery styling)
--
-- ====================================================================
