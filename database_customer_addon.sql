-- Customer features: wishlist, wallet, order discount
-- Run once. If you get "Duplicate column" on the ALTERs, the columns already exist (skip or run only the CREATE TABLE part).
USE ecommerce_marketplace;

-- Wishlist
CREATE TABLE IF NOT EXISTS wishlist (
    wishlist_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- User wallet
CREATE TABLE IF NOT EXISTS user_wallet (
    user_id INT PRIMARY KEY,
    balance DECIMAL(12, 2) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Wallet transactions (add money, pay order, refund)
CREATE TABLE IF NOT EXISTS wallet_transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('add_money', 'order_payment', 'refund') NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    order_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE SET NULL
);

-- Add discount columns to orders (run once; if "Duplicate column" error, columns already exist)
ALTER TABLE orders ADD COLUMN discount_amount DECIMAL(10, 2) DEFAULT 0;
ALTER TABLE orders ADD COLUMN discount_code VARCHAR(50) NULL;
