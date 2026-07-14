<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

class CreatorController extends Controller
{
    protected Product $productModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->productModel = new Product();
    }

    public function dashboard(): void
    {
        $userId = $this->authId();

        // 1. Fetch metrics
        $royalties = $this->productModel->fetch(
            "SELECT SUM(creator_royalty) as total_royalties, COUNT(*) as sales_count
             FROM orders o
             JOIN products p ON o.product_id = p.id
             WHERE p.creator_id = :userId AND o.status = 'completed'",
            ['userId' => $userId]
        );

        $totalRoyalties = (float)($royalties['total_royalties'] ?? 0.00);
        $salesCount = (int)($royalties['sales_count'] ?? 0);

        // 2. Fetch list of products created
        $products = $this->productModel->fetchAll(
            "SELECT p.*, c.name as category_name,
                    (SELECT COUNT(*) FROM orders WHERE product_id = p.id AND status = 'completed') as sales_qty
             FROM products p
             JOIN categories c ON p.category_id = c.id
             WHERE p.creator_id = :userId
             ORDER BY p.created_at DESC",
            ['userId' => $userId]
        );

        // 3. Followers list overview
        $followers = $this->productModel->fetchAll(
            "SELECT u.username, u.full_name, u.avatar_url
             FROM follows f
             JOIN users u ON f.follower_id = u.id
             WHERE f.followed_id = :userId LIMIT 10",
            ['userId' => $userId]
        );

        $this->view('creator.dashboard', [
            'totalRoyalties' => $totalRoyalties,
            'salesCount'     => $salesCount,
            'products'       => $products,
            'followers'      => $followers,
            'csrf_token'     => $this->session->generateCsrfToken()
        ]);
    }

    public function newProduct(): void
    {
        $categories = $this->productModel->getCategories();
        $this->view('creator.new_product', [
            'categories' => $categories,
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }

    public function storeProduct(): void
    {
        $this->validateCsrf();
        $userId = $this->authId();

        $name = trim($this->request->get('name', ''));
        $description = trim($this->request->get('description', ''));
        $price = (float)$this->request->get('price', 0);
        $type = $this->request->get('type', '');
        $categoryId = (int)$this->request->get('category_id', 0);

        $errors = [];

        if (empty($name)) $errors[] = "Product Name is required.";
        if (empty($type)) $errors[] = "Product Type is required.";
        if ($price <= 0) $errors[] = "Price must be greater than $0.";
        if (!$categoryId) $errors[] = "Please select a Category.";

        // Handle File upload securely
        $files = $this->request->getFiles();
        if (!isset($files['product_file']) || $files['product_file']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Please upload the digital product file.";
        }

        if (!empty($errors)) {
            $this->session->setFlash('errors', implode('<br>', $errors));
            $this->redirect('/creator/products/new');
        }

        $file = $files['product_file'];

        // Generate secure storage directory
        $storageDir = STORAGE_PATH . '/products/';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $uniqueName = 'prod_' . bin2hex(random_bytes(10)) . '.' . $extension;
        $destPath = $storageDir . $uniqueName;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9\-]+/', '-', $name)) . '-' . rand(1000, 9999);

            // Save in Database
            $db = \App\Core\Database::connect();
            $stmt = $db->prepare(
                "INSERT INTO products (creator_id, category_id, name, slug, description, type, price, file_path, file_name, status)
                 VALUES (:creator_id, :category_id, :name, :slug, :description, :type, :price, :file_path, :file_name, 'active')"
            );
            $stmt->execute([
                'creator_id'  => $userId,
                'category_id' => $categoryId,
                'name'        => $name,
                'slug'        => $slug,
                'description' => $description,
                'type'        => $type,
                'price'       => $price,
                'file_path'   => $uniqueName,
                'file_name'   => $file['name']
            ]);
            $productId = (int)$db->lastInsertId();

            // Trigger retargeting launch emails to previous buyers
            \App\Services\Mailer::sendNewProductAlertToPreviousBuyers($productId);

            $this->session->setFlash('success', "Digital product '{$name}' has been uploaded and listed successfully!");
            $this->redirect('/creator/dashboard');
        } else {
            $this->session->setFlash('error', "Failed to save digital product files. Please check directory permissions.");
            $this->redirect('/creator/products/new');
        }
    }

    public function editProduct(int $id): void
    {
        $userId = $this->authId();
        $product = $this->productModel->findById($id);

        if (!$product || (int)$product['creator_id'] !== $userId) {
            die("Unauthorized access to product editor.");
        }

        $categories = $this->productModel->getCategories();
        $this->view('creator.edit_product', [
            'product'    => $product,
            'categories' => $categories,
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }

    public function updateProduct(int $id): void
    {
        $this->validateCsrf();
        $userId = $this->authId();
        $product = $this->productModel->findById($id);

        if (!$product || (int)$product['creator_id'] !== $userId) {
            die("Unauthorized access.");
        }

        $name = trim($this->request->get('name', ''));
        $description = trim($this->request->get('description', ''));
        $price = (float)$this->request->get('price', 0);
        $type = $this->request->get('type', '');
        $categoryId = (int)$this->request->get('category_id', 0);

        if (empty($name) || $price <= 0 || !$categoryId) {
            $this->session->setFlash('error', 'Please fill all required fields with valid values.');
            $this->redirect("/creator/products/{$id}/edit");
        }

        // Setup base query
        $sql = "UPDATE products SET name = :name, description = :description, price = :price, type = :type, category_id = :category_id";
        $params = [
            'id'          => $id,
            'name'        => $name,
            'description' => $description,
            'price'       => $price,
            'type'        => $type,
            'category_id' => $categoryId
        ];

        // Handle Optional New File upload
        $files = $this->request->getFiles();
        if (isset($files['product_file']) && $files['product_file']['error'] === UPLOAD_ERR_OK) {
            $file = $files['product_file'];
            $storageDir = STORAGE_PATH . '/products/';
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $uniqueName = 'prod_' . bin2hex(random_bytes(10)) . '.' . $extension;
            $destPath = $storageDir . $uniqueName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                $sql .= ", file_path = :file_path, file_name = :file_name";
                $params['file_path'] = $uniqueName;
                $params['file_name'] = $file['name'];
            }
        }

        $sql .= " WHERE id = :id";
        $this->productModel->query($sql, $params);

        $this->session->setFlash('success', "Product details updated successfully.");
        $this->redirect('/creator/dashboard');
    }

    public function coupons(): void
    {
        $userId = $this->authId();
        $coupons = $this->productModel->fetchAll(
            "SELECT * FROM coupons WHERE creator_id = :userId ORDER BY created_at DESC",
            ['userId' => $userId]
        );

        $this->view('creator.coupons', [
            'coupons'    => $coupons,
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }

    public function newCoupon(): void
    {
        $this->validateCsrf();
        $userId = $this->authId();

        $code = strtoupper(trim($this->request->get('code', '')));
        $discountPercent = (int)$this->request->get('discount_percent', 0);
        $discountAmount = (float)$this->request->get('discount_amount', 0);
        $expiresAt = $this->request->get('expires_at', null);

        if (empty($code)) {
            $this->session->setFlash('error', "Coupon Code cannot be empty.");
            $this->redirect('/creator/coupons');
        }

        // Validate duplicates
        $dupe = $this->productModel->fetch("SELECT 1 FROM coupons WHERE code = :code", ['code' => $code]);
        if ($dupe) {
            $this->session->setFlash('error', "The coupon code '{$code}' already exists in the system.");
            $this->redirect('/creator/coupons');
        }

        $expiresVal = !empty($expiresAt) ? date('Y-m-d H:i:s', strtotime($expiresAt)) : null;

        $this->productModel->query(
            "INSERT INTO coupons (creator_id, code, discount_percent, discount_amount, expires_at)
             VALUES (:creator_id, :code, :percent, :amount, :expires_at)",
            [
                'creator_id'       => $userId,
                'code'             => $code,
                'percent'          => $discountPercent ?: null,
                'amount'           => $discountAmount ?: null,
                'expires_at'       => $expiresVal
            ]
        );

        $this->session->setFlash('success', "Discount coupon code '{$code}' has been added successfully.");
        $this->redirect('/creator/coupons');
    }
}
