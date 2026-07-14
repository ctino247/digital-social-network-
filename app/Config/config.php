<?php
// Mimshack Application Configuration

define('APP_NAME', 'Mimshack');

// Dynamically detect current application URL (never hardcode localhost, 127.0.0.1, or fixed domains)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443 || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
define('APP_URL', $protocol . '://' . $host);

define('ROOT_PATH', dirname(__DIR__, 2));
define('APP_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// Database Configuration
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'mimshack');
define('DB_USER', 'mimshack_user');
define('DB_PASS', 'Mimshack_Pass_2024!');
define('DB_CHAR', 'utf8mb4');
