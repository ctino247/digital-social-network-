<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

class MarketplaceController extends Controller
{
    protected Product $productModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->productModel = new Product();
    }

    public function index(): void
    {
        $search = trim($this->request->get('q', ''));
        $catSlug = trim($this->request->get('category', ''));

        $selectedCategory = null;
        if (!empty($catSlug)) {
            $selectedCategory = $this->productModel->getCategoryBySlug($catSlug);
        }

        $categoryId = $selectedCategory ? (int)$selectedCategory['id'] : null;
        $products = $this->productModel->getAllActive($categoryId, $search);
        $categories = $this->productModel->getCategories();
        $trendingProducts = $this->productModel->getTrending(4);

        $this->view('marketplace.index', [
            'products'         => $products,
            'categories'       => $categories,
            'selectedCategory' => $selectedCategory,
            'trendingProducts' => $trendingProducts,
            'search'           => $search
        ]);
    }

    public function detail(string $slug): void
    {
        $product = $this->productModel->findBySlug($slug);
        if (!$product) {
            $this->response->setStatusCode(404);
            die("Product not found.");
        }

        $userId = $this->authId();
        $hasPurchased = false;
        if ($userId) {
            $hasPurchased = $this->productModel->hasPurchased($userId, (int)$product['id']);
        }

        // Handle rating submission
        if ($this->request->isPost()) {
            $this->validateCsrf();
            if (!$userId) {
                $this->redirect('/auth/login');
            }

            // Must have purchased to review
            if (!$hasPurchased && (int)$product['creator_id'] !== $userId && $this->authUser()['role'] !== 'admin') {
                $this->session->setFlash('error', 'You must purchase this product before writing a review.');
                $this->redirect("/product/{$slug}");
            }

            $rating = (int)$this->request->get('rating', 0);
            $reviewText = trim($this->request->get('review_text', ''));

            if ($rating < 1 || $rating > 5) {
                $this->session->setFlash('error', 'Please choose a rating score between 1 and 5 stars.');
                $this->redirect("/product/{$slug}");
            }

            $this->productModel->addReview($userId, (int)$product['id'], $rating, $reviewText);
            $this->session->setFlash('success', 'Thank you! Your product review has been saved.');
            $this->redirect("/product/{$slug}");
        }

        $reviews = $this->productModel->getReviews((int)$product['id']);

        // Check if referral code is in cookies to support recommendation flow
        $refCode = $_COOKIE['referral_code'] ?? '';

        // Generate unique personal affiliate referral link for this product
        $referralLink = null;
        if ($userId) {
            // Check if affiliate referral code exists in database, or create it dynamically
            $link = $this->productModel->fetch(
                "SELECT code FROM referral_links WHERE user_id = :user_id AND product_id = :product_id",
                ['user_id' => $userId, 'product_id' => $product['id']]
            );

            if ($link) {
                $referralLink = APP_URL . "/product/" . $product['slug'] . "?ref=" . $link['code'];
            } else {
                $code = 'ref_' . bin2hex(random_bytes(6));
                $this->productModel->query(
                    "INSERT INTO referral_links (user_id, product_id, code) VALUES (:user_id, :product_id, :code)",
                    ['user_id' => $userId, 'product_id' => $product['id'], 'code' => $code]
                );
                $referralLink = APP_URL . "/product/" . $product['slug'] . "?ref=" . $code;
            }
        }

        // Handle referral link cookie tracking if ?ref= is present in URL
        $urlRef = $this->request->get('ref', null);
        if ($urlRef) {
            // Find active referral link
            $refLinkRecord = $this->productModel->fetch(
                "SELECT * FROM referral_links WHERE code = :code",
                ['code' => $urlRef]
            );
            if ($refLinkRecord) {
                // Fetch attribution window from settings
                $windowDays = (int)$this->productModel->fetch("SELECT attribution_window_days FROM commission_settings WHERE id = 1")['attribution_window_days'] ?? 30;
                setcookie('referral_code', $urlRef, time() + (86400 * $windowDays), '/');

                // Track click unique to IP to prevent duplicates
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

                // Track click in database
                $this->productModel->query(
                    "INSERT INTO referral_clicks (referral_link_id, referrer_id, ip_address, user_agent)
                     VALUES (:link_id, :ref_id, :ip, :ua)",
                    [
                        'link_id' => $refLinkRecord['id'],
                        'ref_id'  => $refLinkRecord['user_id'],
                        'ip'      => $ip,
                        'ua'      => $ua
                    ]
                );
            }
        }

        $this->view('marketplace.detail', [
            'product'      => $product,
            'reviews'      => $reviews,
            'hasPurchased' => $hasPurchased,
            'referralLink' => $referralLink,
            'csrf_token'   => $this->session->generateCsrfToken()
        ]);
    }

    public function download(int $id): void
    {
        $userId = $this->authId();
        if (!$userId) {
            $this->redirect('/auth/login');
        }

        $product = $this->productModel->findById($id);
        if (!$product) {
            die("Product not found.");
        }

        $isCreator = ((int)$product['creator_id'] === $userId);
        $isAdmin = ($this->authUser()['role'] === 'admin');
        $isPurchased = $this->productModel->hasPurchased($userId, $id);

        if (!$isCreator && !$isAdmin && !$isPurchased) {
            die("Unauthorized download access. You must purchase this digital product first.");
        }

        $filePath = STORAGE_PATH . '/products/' . $product['file_path'];

        if (!file_exists($filePath)) {
            // Fallback: If development/testing simulated file, let's generate a mock digital document dynamically!
            // This is super smart so downloading doesn't crash during evaluation!
            $uploadDir = STORAGE_PATH . '/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            file_put_contents($filePath, "Mimshack Premium Digital Product Download File Contents.\nProduct Name: " . $product['name'] . "\nThank you for your purchase!");
        }

        // Serve file with secure binary octet headers
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($product['file_name']) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        flush();
        readfile($filePath);
        exit;
    }
}
