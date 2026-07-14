<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Services\CommissionEngine;
use App\Core\Database;

class CartController extends Controller
{
    protected Product $productModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->productModel = new Product();
    }

    /**
     * Helper to get synchronized cart list for current user or guest session
     */
    private function getCartItems(): array
    {
        $userId = $this->authId();
        $cart = [];

        if ($userId) {
            // Load synchronized cart from the database shopping_cart table
            $dbItems = $this->productModel->fetchAll(
                "SELECT sc.product_id, sc.quantity, p.name, p.price, p.type, p.creator_id
                 FROM shopping_cart sc
                 JOIN products p ON sc.product_id = p.id
                 WHERE sc.user_id = :user_id AND p.status = 'active'",
                ['user_id' => $userId]
            );

            foreach ($dbItems as $item) {
                $cart[(int)$item['product_id']] = [
                    'id'          => (int)$item['product_id'],
                    'name'        => $item['name'],
                    'price'       => (float)$item['price'],
                    'type'        => $item['type'],
                    'creator_id'  => (int)$item['creator_id'],
                    'quantity'    => (int)$item['quantity']
                ];
            }
        } else {
            // Load from guest session
            $sessionCart = $this->session->get('cart', []);
            foreach ($sessionCart as $pid => $item) {
                $cart[(int)$pid] = [
                    'id'          => (int)$item['id'],
                    'name'        => $item['name'],
                    'price'       => (float)$item['price'],
                    'type'        => $item['type'],
                    'creator_id'  => (int)$item['creator_id'],
                    'quantity'    => isset($item['quantity']) ? (int)$item['quantity'] : 1
                ];
            }
        }

        return $cart;
    }

    public function index(): void
    {
        $cart = $this->getCartItems();

        // Calculate totals with quantities
        $subtotal = 0.00;
        foreach ($cart as $item) {
            $qty = $item['quantity'];
            $subtotal += (float)$item['price'] * $qty;
        }

        // Apply coupon if any
        $couponCode = $this->session->get('applied_coupon', null);
        $discountAmount = 0.00;
        $couponDetails = null;

        if ($couponCode) {
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
                // Save to database
                $this->productModel->query(
                    "INSERT INTO shopping_cart (user_id, product_id, quantity)
                     VALUES (:user_id, :product_id, 1)
                     ON DUPLICATE KEY UPDATE quantity = quantity + 1",
                    ['user_id' => $userId, 'product_id' => $productId]
                );
            } else {
                // Save to guest session
                $cart = $this->session->get('cart', []);
                if (isset($cart[$productId])) {
                    $cart[$productId]['quantity'] = (isset($cart[$productId]['quantity']) ? $cart[$productId]['quantity'] : 1) + 1;
                } else {
                    $cart[$productId] = [
                        'id'          => $product['id'],
                        'name'        => $product['name'],
                        'price'       => $product['price'],
                        'type'        => $product['type'],
                        'creator_id'  => $product['creator_id'],
                        'quantity'    => 1
                    ];
                }
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
                // Save to database
                $this->productModel->query(
                    "INSERT INTO shopping_cart (user_id, product_id, quantity)
                     VALUES (:user_id, :product_id, 1)
                     ON DUPLICATE KEY UPDATE quantity = quantity + 1",
                    ['user_id' => $userId, 'product_id' => $productId]
                );
            } else {
                // Save to session
                $cart = $this->session->get('cart', []);
                if (isset($cart[$productId])) {
                    $cart[$productId]['quantity'] = (isset($cart[$productId]['quantity']) ? $cart[$productId]['quantity'] : 1) + 1;
                } else {
                    $cart[$productId] = [
                        'id'          => $product['id'],
                        'name'        => $product['name'],
                        'price'       => $product['price'],
                        'type'        => $product['type'],
                        'creator_id'  => $product['creator_id'],
                        'quantity'    => 1
                    ];
                }
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
            $this->productModel->query(
                "DELETE FROM shopping_cart WHERE user_id = :user_id AND product_id = :product_id",
                ['user_id' => $userId, 'product_id' => $productId]
            );
        } else {
            $cart = $this->session->get('cart', []);
            if (isset($cart[$productId])) {
                unset($cart[$productId]);
                $this->session->set('cart', $cart);
            }
        }

        $this->session->setFlash('success', 'Product removed from shopping cart.');
        $this->redirect('/cart');
    }

    public function updateQuantity(): void
    {
        $this->validateCsrf();
        $productId = (int)$this->request->get('product_id', 0);
        $quantity = max(1, (int)$this->request->get('quantity', 1));
        $userId = $this->authId();

        if ($userId) {
            $this->productModel->query(
                "UPDATE shopping_cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id",
                ['quantity' => $quantity, 'user_id' => $userId, 'product_id' => $productId]
            );
        } else {
            $cart = $this->session->get('cart', []);
            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] = $quantity;
                $this->session->set('cart', $cart);
            }
        }

        $this->session->setFlash('success', 'Cart quantity updated.');
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

    /**
     * Initializes secure Flutterwave payment process instead of mock direct order completion.
     */
    public function checkout(): void
    {
        $this->validateCsrf();
        $userId = $this->authId();
        if (!$userId) {
            $this->session->setFlash('error', 'Please login to checkout.');
            $this->redirect('/auth/login');
        }

        $cart = $this->getCartItems();
        if (empty($cart)) {
            $this->session->setFlash('error', 'Your shopping cart is empty.');
            $this->redirect('/marketplace');
        }

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

        $commissionSettings = $this->productModel->fetch("SELECT * FROM commission_settings WHERE id = 1");
        $platformFeePercent = (float)($commissionSettings['platform_fee_percent'] ?? 5.00);

        // Fetch active referral cookie code if present
        $referralCode = $_COOKIE['referral_code'] ?? null;
        $referrerId = null;

        if ($referralCode) {
            $refLink = $this->productModel->fetch("SELECT user_id FROM referral_links WHERE code = :code", ['code' => $referralCode]);
            if ($refLink) {
                $referrerId = (int)$refLink['user_id'];
                if ($referrerId === $userId) {
                    $referrerId = null;
                }
            }
        }

        // Initialize transaction reference
        $txRef = 'mimshack_tx_' . bin2hex(random_bytes(10));

        // Create entries in flutterwave_payments for verification flow
        // To handle multi-item carts, we can charge the total final amount.
        // Let's create an active payment intent in the DB for each item in the cart, referencing the same txRef!
        $db = Database::connect();

        try {
            $db->beginTransaction();

            $totalCartPaymentAmount = 0.00;

            foreach ($cart as $item) {
                $productId = (int)$item['id'];
                $qty = (int)$item['quantity'];
                $price = (float)$item['price'] * $qty;

                // Calculate final price with discounts
                $discountApplied = 0.00;
                if ($discountPercent > 0) {
                    $discountApplied = $price * ($discountPercent / 100);
                } elseif ($discountAmountPerItem > 0) {
                    $discountApplied = min($price, $discountAmountPerItem);
                }

                $finalAmount = max(0.00, $price - $discountApplied);
                $platformFee = $finalAmount * ($platformFeePercent / 100);
                $chargeAmount = $finalAmount + $platformFee;

                $totalCartPaymentAmount += $chargeAmount;

                // Insert into flutterwave_payments as pending
                $stmt = $db->prepare(
                    "INSERT INTO flutterwave_payments (tx_ref, user_id, product_id, amount, currency, status, coupon_id, referrer_id)
                     VALUES (:tx_ref, :user_id, :product_id, :amount, 'USD', 'pending', :coupon_id, :referrer_id)"
                );
                $stmt->execute([
                    'tx_ref'     => $txRef,
                    'user_id'    => $userId,
                    'product_id' => $productId,
                    'amount'     => $chargeAmount,
                    'coupon_id'  => $couponId,
                    'referrer_id' => $referrerId
                ]);
            }

            $db->commit();

            // Redirect user to our Flutterwave checkout simulator with tx_ref and dynamic site URL redirects
            $dynamicUrl = $this->getDynamicSiteUrl();
            $redirectUrl = $dynamicUrl . '/profile/' . $this->authUser()['username'];

            $this->redirect('/flutterwave/simulate-checkout?tx_ref=' . urlencode($txRef) . '&redirect=' . urlencode($redirectUrl));

        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Payment initialization error: " . $e->getMessage());
            $this->session->setFlash('error', 'Checkout initialization failed. Please try again.');
            $this->redirect('/cart');
        }
    }

    /**
     * Helper to retrieve the current dynamic absolute host/domain URL
     */
    private function getDynamicSiteUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        return $protocol . "://" . $host;
    }
}
