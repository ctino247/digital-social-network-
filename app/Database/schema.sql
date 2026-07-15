-- Mimshack Database Schema (MySQL 8.0+)
-- Production Ready, Fully Normalized, Indexed, with Foreign Keys

CREATE DATABASE IF NOT EXISTS mimshack;
USE mimshack;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    bio TEXT,
    avatar_url VARCHAR(255) DEFAULT NULL,
    role ENUM('member', 'creator', 'admin') NOT NULL DEFAULT 'member',
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    is_sales_partner TINYINT(1) NOT NULL DEFAULT 0,
    remember_token VARCHAR(100) DEFAULT NULL,
    referred_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role),
    FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Follows Table (Social Graph)
CREATE TABLE IF NOT EXISTS follows (
    follower_id INT NOT NULL,
    followed_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (follower_id, followed_id),
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (followed_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_followed (followed_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Posts Table (Threads-Style)
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content VARCHAR(500) NOT NULL,
    parent_id INT DEFAULT NULL, -- For Replies
    quote_id INT DEFAULT NULL,  -- For Quote Posts
    product_id INT DEFAULT NULL, -- For Recommendation Posts
    referral_code VARCHAR(50) DEFAULT NULL, -- For Recommendation Posts
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES posts(id) ON DELETE SET NULL,
    FOREIGN KEY (quote_id) REFERENCES posts(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_user_posts (user_id),
    INDEX idx_parent_post (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Post Likes Table
CREATE TABLE IF NOT EXISTS post_likes (
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    INDEX idx_post_likes (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Post Bookmarks Table
CREATE TABLE IF NOT EXISTS post_bookmarks (
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    INDEX idx_post_bookmarks (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Polls Table
CREATE TABLE IF NOT EXISTS polls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL UNIQUE,
    question VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Poll Options Table
CREATE TABLE IF NOT EXISTS poll_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poll_id INT NOT NULL,
    option_text VARCHAR(100) NOT NULL,
    FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE,
    INDEX idx_poll_options (poll_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Poll Votes Table
CREATE TABLE IF NOT EXISTS poll_votes (
    user_id INT NOT NULL,
    poll_id INT NOT NULL,
    option_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, poll_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE,
    FOREIGN KEY (option_id) REFERENCES poll_options(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Messages Table (Private Messaging)
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    content TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_chat_users (sender_id, receiver_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Products Table (Digital Products)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    creator_id INT NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    type ENUM('PDF', 'ZIP', 'Template', 'Course', 'Source Code', 'Software', 'E-book', 'Audio', 'Video', 'Digital Download') NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(100) NOT NULL,
    status ENUM('pending', 'active', 'rejected') NOT NULL DEFAULT 'pending',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_product_status (status),
    INDEX idx_product_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Product Reviews Table
CREATE TABLE IF NOT EXISTS product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_user_product_review (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Coupons Table
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    creator_id INT DEFAULT NULL, -- NULL means platform-wide by Admin
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_percent INT DEFAULT NULL CHECK (discount_percent BETWEEN 1 AND 100),
    discount_amount DECIMAL(10, 2) DEFAULT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Referral Links Table (Reused for Recommendations)
CREATE TABLE IF NOT EXISTS referral_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_user_product_link (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Recommendation Events Table (Analytics tracking)
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

-- 16. Referral Clicks Table (Legacy, but kept for schema structure)
CREATE TABLE IF NOT EXISTS referral_clicks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    referral_link_id INT NOT NULL,
    referrer_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (referral_link_id) REFERENCES referral_links(id) ON DELETE CASCADE,
    FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_click_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Orders Table (Digital purchases)
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    discount_applied DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    final_amount DECIMAL(10, 2) NOT NULL,
    platform_fee DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    creator_royalty DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    referral_commission DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    referrer_id INT DEFAULT NULL, -- Direct referrer
    coupon_id INT DEFAULT NULL,
    status ENUM('pending', 'completed', 'refunded') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL,
    INDEX idx_order_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Wallet Table
CREATE TABLE IF NOT EXISTS wallets (
    user_id INT PRIMARY KEY,
    balance DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    pending_balance DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Transactions Table
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wallet_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    type ENUM('sale', 'commission', 'withdrawal', 'bonus', 'purchase') NOT NULL,
    order_id INT DEFAULT NULL,
    description VARCHAR(255) NOT NULL,
    status ENUM('pending', 'completed', 'failed') NOT NULL DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wallet_id) REFERENCES wallets(user_id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. Withdrawals Table
CREATE TABLE IF NOT EXISTS withdrawals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    destination TEXT NOT NULL, -- Bank info, PayPal email, Flutterwave details, etc.
    status ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    processed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_withdrawal_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. Creator Applications Table
CREATE TABLE IF NOT EXISTS creator_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    bio TEXT,
    portfolio_url VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    admin_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22. Commission Settings Table
CREATE TABLE IF NOT EXISTS commission_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    level_1_percent DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    level_2_percent DECIMAL(5,2) NOT NULL DEFAULT 5.00,
    level_3_percent DECIMAL(5,2) NOT NULL DEFAULT 3.00,
    level_4_percent DECIMAL(5,2) NOT NULL DEFAULT 2.00,
    level_5_percent DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    platform_fee_percent DECIMAL(5,2) NOT NULL DEFAULT 5.00,
    attribution_window_days INT NOT NULL DEFAULT 30,
    min_withdrawal_amount DECIMAL(10,2) NOT NULL DEFAULT 50.00,
    max_withdrawal_amount DECIMAL(10,2) NOT NULL DEFAULT 5000.00,
    withdrawal_fee DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    withdrawals_enabled TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23. System Settings Table (Key-Value)
CREATE TABLE IF NOT EXISTS system_settings (
    `key` VARCHAR(100) PRIMARY KEY,
    `value` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24. Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL, -- 'like', 'reply', 'follow', 'mention', 'commission', 'sale', 'announcement', 'message'
    sender_id INT DEFAULT NULL,
    source_id INT DEFAULT NULL, -- general reference ID (post_id, order_id, follow_id, etc)
    content TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_notification_user (user_id),
    INDEX idx_notification_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 25. Activity Logs
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 26. Announcements Table (Platform announcements)
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 27. Flutterwave Payments Table
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


-- Default Settings and Admin User Seeds
INSERT INTO commission_settings (id, level_1_percent, level_2_percent, level_3_percent, level_4_percent, level_5_percent, platform_fee_percent, attribution_window_days, min_withdrawal_amount, max_withdrawal_amount, withdrawal_fee, withdrawals_enabled)
VALUES (1, 10.00, 5.00, 3.00, 2.00, 1.00, 5.00, 30, 50.00, 5000.00, 0.00, 1)
ON DUPLICATE KEY UPDATE id=id;

-- Initial System Settings
INSERT INTO system_settings (`key`, `value`) VALUES
('platform_name', 'Mimshack'),
('platform_brand_color', '#10B981'), -- Emerald Green
('maintenance_mode', '0'),
('smtp_host', 'smtp.mailtrap.io'),
('smtp_port', '2525'),
('smtp_user', 'mock_user'),
('smtp_pass', 'mock_pass'),
('smtp_secure', 'tls'),
('paypal_client_id', 'mock_paypal_id'),
('paypal_secret', 'mock_paypal_secret'),
('stripe_key', 'mock_stripe_key'),
('stripe_secret', 'mock_stripe_secret'),
('flutterwave_public_key', 'FLWPUBK_TEST-mock-public-key'),
('flutterwave_secret_key', 'FLWSECK_TEST-mock-secret-key'),
('flutterwave_encryption_key', 'FLWENCK_TEST-mock-encryption-key'),
('feature_toggle_chat', '1'),
('feature_toggle_marketplace', '1'),
('feature_toggle_social', '1'),
('seo_title', 'Mimshack - The Ultimate Social Commerce Platform for Digital Products'),
('seo_description', 'Connect, share, recommend, and sell digital items with multi-level commission tracking.')
ON DUPLICATE KEY UPDATE `key`=`key`;

-- Insert default admin: username: admin, password: adminpassword (hashed)
-- Password hash for 'adminpassword' is '$2y$10$1mFsueThA1JNylU.hbld.uIZA.XvEUaIkoA12s4jGIqfxLalmwBCG'
INSERT INTO users (username, email, password_hash, full_name, role, is_verified, is_sales_partner)
VALUES ('admin', 'admin@mimshack.com', '$2y$10$1mFsueThA1JNylU.hbld.uIZA.XvEUaIkoA12s4jGIqfxLalmwBCG', 'System Administrator', 'admin', 1, 1)
ON DUPLICATE KEY UPDATE id=id;

-- Create admin wallet
INSERT INTO wallets (user_id, balance, pending_balance)
VALUES (1, 1000.00, 0.00)
ON DUPLICATE KEY UPDATE user_id=user_id;

-- Default categories
INSERT INTO categories (name, slug) VALUES
('E-books', 'e-books'),
('Templates & Assets', 'templates'),
('Software & Code', 'software-code'),
('Video & Audio Courses', 'video-audio')
ON DUPLICATE KEY UPDATE name=name;
