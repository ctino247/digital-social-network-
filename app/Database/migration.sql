-- Mimshack Schema Enhancements Migration

USE mimshack;

-- 1. Update Posts Table to support product cards in recommendations posts
ALTER TABLE posts
ADD COLUMN product_id INT DEFAULT NULL,
ADD COLUMN referral_code VARCHAR(50) DEFAULT NULL,
ADD CONSTRAINT fk_posts_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL;

-- 2. Create Recommendation Events Table (for granular clicks, views, and conversion analytics)
CREATE TABLE IF NOT EXISTS recommendation_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    referral_link_id INT NOT NULL,
    event_type ENUM('click', 'view', 'checkout_start', 'purchase') NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (referral_link_id) REFERENCES referral_links(id) ON DELETE CASCADE,
    INDEX idx_ref_link_event (referral_link_id, event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create Flutterwave Payments Table to log checkout transactions securely
CREATE TABLE IF NOT EXISTS flutterwave_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tx_ref VARCHAR(100) NOT NULL UNIQUE,
    transaction_id VARCHAR(100) DEFAULT NULL,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(10) NOT NULL DEFAULT 'USD',
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    coupon_id INT DEFAULT NULL,
    referrer_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL,
    FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Update Commission Settings with Administrator controls for withdrawals
ALTER TABLE commission_settings
ADD COLUMN max_withdrawal_amount DECIMAL(10,2) NOT NULL DEFAULT 5000.00,
ADD COLUMN withdrawal_fee DECIMAL(5,2) NOT NULL DEFAULT 0.00,
ADD COLUMN withdrawals_enabled TINYINT(1) NOT NULL DEFAULT 1;

-- Seed system setting for Flutterwave Keys
INSERT INTO system_settings (`key`, `value`) VALUES
('flutterwave_public_key', 'FLWPUBK_TEST-mock-public-key'),
('flutterwave_secret_key', 'FLWSECK_TEST-mock-secret-key'),
('flutterwave_encryption_key', 'FLWENCK_TEST-mock-encryption-key')
ON DUPLICATE KEY UPDATE `value` = `value`;
