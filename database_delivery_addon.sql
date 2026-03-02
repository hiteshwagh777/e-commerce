-- Delivery partner features: availability toggle
-- Run once. If "Duplicate column" on ALTER, column already exists.
USE ecommerce_marketplace;

ALTER TABLE delivery_partners ADD COLUMN is_available TINYINT(1) NOT NULL DEFAULT 1;

