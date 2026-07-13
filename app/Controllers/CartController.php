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

    public function index(): void
    {
        $cart = $this->session->get('cart', []);

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
            $cart = $this->session->get('cart', []);
            // Simple shopping cart - only digital products, duplicate prevention
            $cart[$productId] = [
                'id'          => $product['id'],
                'name'        => $product['name'],
                'price'       => $product['price'],
                'type'        => $product['type'],
                'creator_id'  => $product['creator_id']
            ];
            $this->session->set('cart', $cart);

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

    public function remove(): void
    {
        $this->validateCsrf();
        $productId = (int)$this->request->get('product_id', 0);
        $cart = $this->session->get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            $this->session->set('cart', $cart);
            $this->session->setFlash('success', 'Product removed from shopping cart.');
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

        $cart = $this->session->get('cart', []);
        if (empty($cart)) {
            $this->session->setFlash('error', 'Your shopping cart is empty.');
            $this->redirect('/marketplace');
        }

        // We will process the cart products sequentially.
        // For each product, calculate discount, calculate platform fee, check referral cookies,
        // create a completed order, and trigger the CommissionEngine.

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

            $totalAmountPaid = 0.00;
            foreach ($cart as $item) {
                $productId = (int)$item['id'];
                $price = (float)$item['price'];

                // Calculate final price with discounts
                $discountApplied = 0.00;
                if ($discountPercent > 0) {
                    $discountApplied = $price * ($discountPercent / 100);
                } elseif ($discountAmountPerItem > 0) {
                    $discountApplied = min($price, $discountAmountPerItem);
                }

                $finalAmount = max(0.00, $price - $discountApplied);
                $totalAmountPaid += ($finalAmount + ($finalAmount * ($platformFeePercent / 100)));

                // Platform fee
                $platformFee = $finalAmount * ($platformFeePercent / 100);

                // Insert into orders
                $stmt = $db->prepare(
                    "INSERT INTO orders (user_id, product_id, price, discount_applied, final_amount, platform_fee, status, referrer_id, coupon_id)
                     VALUES (:user_id, :product_id, :price, :discount_applied, :final_amount, :platform_fee, 'completed', :referrer_id, :coupon_id)"
                );
                $stmt->execute([
                    'user_id'          => $userId,
                    'product_id'       => $productId,
                    'price'            => $price,
                    'discount_applied' => $discountApplied,
                    'final_amount'     => $finalAmount,
                    'platform_fee'     => $platformFee,
                    'referrer_id'      => $referrerId,
                    'coupon_id'        => $couponId
                ]);

                $orderId = (int)$db->lastInsertId();

                // Track Successful Purchase for Recommendation Analytics
                if ($referrerId) {
                    $refLink = $this->productModel->fetch(
                        "SELECT id FROM referral_links WHERE user_id = :user_id AND product_id = :product_id",
                        ['user_id' => $referrerId, 'product_id' => $productId]
                    );
                    if ($refLink) {
                        $this->productModel->trackRecommendationEvent((int)$refLink['id'], 'purchase');
                    }
                }

                // Generate system notifications & log activity
                $buyerName = $this->authUser()['full_name'];
                $prodName = $item['name'];

                // 1. Notify Creator of the Sale
                $creatorNotificationMsg = "Great news! Your digital product '{$prodName}' was purchased by {$buyerName} for $" . number_format($finalAmount, 2) . ".";
                $stmtNotify = $db->prepare("INSERT INTO notifications (user_id, type, source_id, content) VALUES (:user_id, 'sale', :source_id, :content)");
                $stmtNotify->execute([
                    'user_id'   => $item['creator_id'],
                    'source_id' => $orderId,
                    'content'   => $creatorNotificationMsg
                ]);

                // 2. Notify Buyer
                $buyerNotificationMsg = "Thank you! Your purchase of '{$prodName}' is confirmed. You can now download it instantly from your profile or the product page.";
                $stmtNotify->execute([
                    'user_id'   => $userId,
                    'source_id' => $orderId,
                    'content'   => $buyerNotificationMsg
                ]);

                // Track and credit multi-level affiliate marketing commissions
                CommissionEngine::processOrderCommissions($orderId);
            }

            // Trigger automated purchase confirmation email
            \App\Services\Mailer::sendPurchaseConfirmation(
                $this->authUser()['email'],
                $this->authUser()['full_name'],
                $cart,
                $totalAmountPaid
            );

            // Clear Cart and Cookies
            $this->session->remove('cart');
            $this->session->remove('applied_coupon');
            setcookie('referral_code', '', time() - 3600, '/');

            $db->commit();

            // Auto unlock Sales Partner status after purchasing any digital product
            $this->productModel->query("UPDATE users SET is_sales_partner = 1 WHERE id = :id", ['id' => $userId]);
            $refreshedUser = $this->productModel->fetch("SELECT * FROM users WHERE id = :id", ['id' => $userId]);
            $this->session->set('user', $refreshedUser);

            $this->session->setFlash('success', 'Order completed successfully! Digital products are unlocked.');
            $this->redirect('/profile/' . $refreshedUser['username']);

        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Checkout error: " . $e->getMessage());
            $this->session->setFlash('error', 'Checkout failed. Please try again.');
            $this->redirect('/cart');
        }
    }
}
