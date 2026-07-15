<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Services\CommissionEngine;

class CartController extends Controller
{
    protected Product $productModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->productModel = new Product();
    }

    private function getCart(): array
    {
        $userId = $this->authId();
        if ($userId) {
            // Merge session cart to DB if any exists
            $sessionCart = $this->session->get('cart', []);
            $db = \App\Core\Database::connect();
            if (!empty($sessionCart)) {
                foreach ($sessionCart as $productId => $item) {
                    $stmt = $db->prepare("SELECT id FROM shopping_cart WHERE user_id = :u AND product_id = :p");
                    $stmt->execute(['u' => $userId, 'p' => $productId]);
                    if (!$stmt->fetch()) {
                        $stmtInsert = $db->prepare("INSERT INTO shopping_cart (user_id, product_id, quantity) VALUES (:u, :p, 1)");
                        $stmtInsert->execute(['u' => $userId, 'p' => $productId]);
                    }
                }
                $this->session->remove('cart');
            }

            // Fetch from database
            $stmt = $db->prepare(
                "SELECT p.id, p.name, p.price, p.type, p.creator_id
                 FROM shopping_cart sc
                 JOIN products p ON sc.product_id = p.id
                 WHERE sc.user_id = :user_id"
            );
            $stmt->execute(['user_id' => $userId]);
            $dbCart = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $cart = [];
            foreach ($dbCart as $item) {
                $cart[$item['id']] = $item;
            }
            return $cart;
        }

        return $this->session->get('cart', []);
    }

    public function index(): void
    {
        $cart = $this->getCart();

        // Calculate totals
        $subtotal = 0.00;
        foreach ($cart as $item) {
            $subtotal += (float)$item['price'];
        }

        // Apply coupon if any
        $couponCode = $this->session->get('applied_coupon', null);
        $discountAmount = 0.00;
        $couponDetails = null;

        if ($couponCode) {
            // Find coupon
            $couponDetails = $this->productModel->fetch(
                "SELECT * FROM coupons WHERE code = :code",
                ['code' => $couponCode]
            );

            if ($couponDetails) {
                if ($couponDetails['discount_percent']) {
                    $discountAmount = $subtotal * ($couponDetails['discount_percent'] / 100);
                } elseif ($couponDetails['discount_amount']) {
                    $discountAmount = min($subtotal, (float)$couponDetails['discount_amount']);
                }
            } else {
                $this->session->remove('applied_coupon');
            }
        }

        $finalAmount = max(0.00, $subtotal - $discountAmount);

        // Fetch platform settings
        $settings = $this->productModel->fetch("SELECT platform_fee_percent FROM commission_settings WHERE id = 1");
        $feePercent = (float)($settings['platform_fee_percent'] ?? 5.00);

        // Platform fee
        $platformFee = $finalAmount * ($feePercent / 100);

        // Render Cart
        $this->view('cart.index', [
            'cart'           => $cart,
            'subtotal'       => $subtotal,
            'discount'       => $discountAmount,
            'coupon'         => $couponDetails,
            'platformFee'    => $platformFee,
            'finalAmount'    => $finalAmount,
            'csrf_token'     => $this->session->generateCsrfToken()
        ]);
    }

    public function add(): void
    {
        $this->validateCsrf();
        $productId = (int)$this->request->get('product_id', 0);
        $product = $this->productModel->findById($productId);

        if ($product) {
            $userId = $this->authId();
            if ($userId) {
                $db = \App\Core\Database::connect();
                $stmt = $db->prepare("SELECT id FROM shopping_cart WHERE user_id = :u AND product_id = :p");
                $stmt->execute(['u' => $userId, 'p' => $productId]);
                if (!$stmt->fetch()) {
                    $stmtInsert = $db->prepare("INSERT INTO shopping_cart (user_id, product_id, quantity) VALUES (:u, :p, 1)");
                    $stmtInsert->execute(['u' => $userId, 'p' => $productId]);
                }
            } else {
                $cart = $this->session->get('cart', []);
                $cart[$productId] = [
                    'id'          => $product['id'],
                    'name'        => $product['name'],
                    'price'       => $product['price'],
                    'type'        => $product['type'],
                    'creator_id'  => $product['creator_id']
                ];
                $this->session->set('cart', $cart);
            }

            // Log Checkout Start for Recommendation Analytics
            $refCode = $_COOKIE['referral_code'] ?? null;
            if ($refCode) {
                $refLink = $this->productModel->fetch(
                    "SELECT id FROM referral_links WHERE code = :code AND product_id = :p_id",
                    ['code' => $refCode, 'p_id' => $productId]
                );
                if ($refLink) {
                    $this->productModel->trackRecommendationEvent((int)$refLink['id'], 'checkout_start');
                }
            }

            $this->session->setFlash('success', 'Product added to shopping cart.');
        } else {
            $this->session->setFlash('error', 'Product not found.');
        }

        $this->redirect('/cart');
    }

    public function buyNow(): void
    {
        $this->validateCsrf();
        $productId = (int)$this->request->get('product_id', 0);
        $product = $this->productModel->findById($productId);

        if ($product) {
            $userId = $this->authId();
            if ($userId) {
                $db = \App\Core\Database::connect();
                $stmt = $db->prepare("SELECT id FROM shopping_cart WHERE user_id = :u AND product_id = :p");
                $stmt->execute(['u' => $userId, 'p' => $productId]);
                if (!$stmt->fetch()) {
                    $stmtInsert = $db->prepare("INSERT INTO shopping_cart (user_id, product_id, quantity) VALUES (:u, :p, 1)");
                    $stmtInsert->execute(['u' => $userId, 'p' => $productId]);
                }
            } else {
                $cart = $this->session->get('cart', []);
                $cart[$productId] = [
                    'id'          => $product['id'],
                    'name'        => $product['name'],
                    'price'       => $product['price'],
                    'type'        => $product['type'],
                    'creator_id'  => $product['creator_id']
                ];
                $this->session->set('cart', $cart);
            }

            // Log Checkout Start for Recommendation Analytics
            $refCode = $_COOKIE['referral_code'] ?? null;
            if ($refCode) {
                $refLink = $this->productModel->fetch(
                    "SELECT id FROM referral_links WHERE code = :code AND product_id = :p_id",
                    ['code' => $refCode, 'p_id' => $productId]
                );
                if ($refLink) {
                    $this->productModel->trackRecommendationEvent((int)$refLink['id'], 'checkout_start');
                }
            }

            // Redirect directly to cart for instant checkout execution
            $this->redirect('/cart');
        } else {
            $this->session->setFlash('error', 'Product not found.');
            $this->redirect('/marketplace');
        }
    }

    public function remove(): void
    {
        $this->validateCsrf();
        $productId = (int)$this->request->get('product_id', 0);
        $userId = $this->authId();

        if ($userId) {
            $db = \App\Core\Database::connect();
            $stmt = $db->prepare("DELETE FROM shopping_cart WHERE user_id = :u AND product_id = :p");
            $stmt->execute(['u' => $userId, 'p' => $productId]);
            $this->session->setFlash('success', 'Product removed from shopping cart.');
        } else {
            $cart = $this->session->get('cart', []);
            if (isset($cart[$productId])) {
                unset($cart[$productId]);
                $this->session->set('cart', $cart);
                $this->session->setFlash('success', 'Product removed from shopping cart.');
            }
        }

        $this->redirect('/cart');
    }

    public function applyCoupon(): void
    {
        $this->validateCsrf();
        $code = strtoupper(trim($this->request->get('coupon_code', '')));

        if (empty($code)) {
            $this->session->remove('applied_coupon');
            $this->session->setFlash('success', 'Coupon removed.');
            $this->redirect('/cart');
        }

        // Validate coupon code in database
        $coupon = $this->productModel->fetch(
            "SELECT * FROM coupons WHERE code = :code AND (expires_at IS NULL OR expires_at > NOW())",
            ['code' => $code]
        );

        if ($coupon) {
            $this->session->set('applied_coupon', $code);
            $this->session->setFlash('success', "Coupon '{$code}' applied successfully!");
        } else {
            $this->session->remove('applied_coupon');
            $this->session->setFlash('error', 'Invalid or expired coupon code.');
        }

        $this->redirect('/cart');
    }

    public function checkout(): void
    {
        $this->validateCsrf();
        $userId = $this->authId();
        if (!$userId) {
            $this->session->setFlash('error', 'Please login to checkout.');
            $this->redirect('/auth/login');
        }

        $cart = $this->getCart();
        if (empty($cart)) {
            $this->session->setFlash('error', 'Your shopping cart is empty.');
            $this->redirect('/marketplace');
        }

        $db = \App\Core\Database::connect();

        try {
            $db->beginTransaction();

            $couponCode = $this->session->get('applied_coupon', null);
            $couponId = null;
            $discountPercent = 0;
            $discountAmountPerItem = 0.00;

            if ($couponCode) {
                $coupon = $this->productModel->fetch("SELECT * FROM coupons WHERE code = :code", ['code' => $couponCode]);
                if ($coupon) {
                    $couponId = (int)$coupon['id'];
                    $discountPercent = (int)($coupon['discount_percent'] ?? 0);
                    $discountAmountPerItem = (float)($coupon['discount_amount'] ?? 0.00);
                }
            }

            // Configurable settings
            $commissionSettings = $this->productModel->fetch("SELECT * FROM commission_settings WHERE id = 1");
            $platformFeePercent = (float)($commissionSettings['platform_fee_percent'] ?? 5.00);

            // Fetch active referral cookie code if present
            $referralCode = $_COOKIE['referral_code'] ?? null;
            $referrerId = null;

            if ($referralCode) {
                $refLink = $this->productModel->fetch("SELECT user_id FROM referral_links WHERE code = :code", ['code' => $referralCode]);
                if ($refLink) {
                    $referrerId = (int)$refLink['user_id'];
                    // Prevent self-referral commission
                    if ($referrerId === $userId) {
                        $referrerId = null;
                    }
                }
            }

            // Create a unique tx_ref for Flutterwave transaction
            $txRef = 'tx_' . bin2hex(random_bytes(10));

            $totalAmountPaid = 0.00;
            $firstProductId = null;

            foreach ($cart as $item) {
                $productId = (int)$item['id'];
                if ($firstProductId === null) {
                    $firstProductId = $productId;
                }
                $price = (float)$item['price'];

                // Calculate final price with discounts
                $discountApplied = 0.00;
                if ($discountPercent > 0) {
                    $discountApplied = $price * ($discountPercent / 100);
                } elseif ($discountAmountPerItem > 0) {
                    $discountApplied = min($price, $discountAmountPerItem);
                }

                $finalAmount = max(0.00, $price - $discountApplied);
                $platformFee = $finalAmount * ($platformFeePercent / 100);

                // We add price + platform fee to total amount paid
                $totalAmountPaid += ($finalAmount + $platformFee);

                // Insert into orders with status 'pending' and associate with tx_ref
                $stmt = $db->prepare(
                    "INSERT INTO orders (user_id, product_id, price, discount_applied, final_amount, platform_fee, status, referrer_id, coupon_id, tx_ref)
                     VALUES (:user_id, :product_id, :price, :discount_applied, :final_amount, :platform_fee, 'pending', :referrer_id, :coupon_id, :tx_ref)"
                );
                $stmt->execute([
                    'user_id'          => $userId,
                    'product_id'       => $productId,
                    'price'            => $price,
                    'discount_applied' => $discountApplied,
                    'final_amount'     => $finalAmount,
                    'platform_fee'     => $platformFee,
                    'referrer_id'      => $referrerId,
                    'coupon_id'        => $couponId,
                    'tx_ref'           => $txRef
                ]);
            }

            $db->commit();

            // Initialize Flutterwave Payment via API
            $flw = new \App\Services\Flutterwave();
            $redirectUrl = APP_URL . '/flutterwave/callback';

            $meta = [
                'user_id'     => $userId,
                'product_id'  => $firstProductId,
                'coupon_id'   => $couponId,
                'referrer_id' => $referrerId,
                'buyer_name'  => $this->authUser()['full_name']
            ];

            // Initialize standard Flutterwave payment session
            $paymentLink = $flw->initializePayment(
                $this->authUser()['email'],
                $totalAmountPaid,
                'USD',
                $redirectUrl,
                $meta
            );

            if ($paymentLink) {
                $this->redirect($paymentLink);
            } else {
                throw new \Exception("Could not initialize Flutterwave payment link.");
            }

        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Checkout error: " . $e->getMessage());
            $this->session->setFlash('error', 'Checkout failed to initialize. Please try again.');
            $this->redirect('/cart');
        }
    }
}
