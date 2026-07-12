<?php

// Mimshack Framework Integration Test Script
require_once __DIR__ . '/../app/Config/config.php';

// Custom PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

echo "=== MIMSHACK INTEGRATION TEST ===\n";

try {
    // 1. Test Database connection
    echo "1. Testing Database Connection... ";
    $db = \App\Core\Database::connect();
    echo "PASSED\n";

    // 2. Test User Model find
    echo "2. Testing User Model findById... ";
    $userModel = new \App\Models\User();
    $admin = $userModel->findById(1);
    if ($admin && $admin['username'] === 'admin') {
        echo "PASSED (Admin User Found: {$admin['full_name']})\n";
    } else {
        throw new Exception("Admin user not found or incorrect username.");
    }

    // 3. Test Post Model trending hashtags
    echo "3. Testing Post Model trends... ";
    $postModel = new \App\Models\Post();
    $trends = $postModel->getTrendingHashtags(5);
    echo "PASSED (Trends count: " . count($trends) . ")\n";

    // 4. Test Product Model Categories
    echo "4. Testing Product Model categories list... ";
    $productModel = new \App\Models\Product();
    $categories = $productModel->getCategories();
    if (count($categories) > 0) {
        echo "PASSED (" . count($categories) . " categories found: " . implode(', ', array_column($categories, 'name')) . ")\n";
    } else {
        throw new Exception("Categories table is empty.");
    }

    echo "=== ALL INTEGRATION TESTS PASSED 100% ===\n";
    exit(0);

} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}
