<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Services\CommissionEngine;
use App\Services\Mailer;
use PDO;

class FlutterwaveController extends Controller
{
    /**
     * Renders a highly interactive Flutterwave Simulation checkout screen
     */
    public function simulateCheckout(): void
    {
        $txRef = $this->request->get('tx_ref', '');
        $redirectUrl = $this->request->get('redirect', '');

        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT p.*, prod.name as product_name, u.username as buyer_username
             FROM flutterwave_payments p
             JOIN products prod ON p.product_id = prod.id
             JOIN users u ON p.user_id = u.id
             WHERE p.tx_ref = :tx_ref"
        );
        $stmt->execute(['tx_ref' => $txRef]);
        $payment = $stmt->fetch();

        if (!$payment) {
            die("Payment transaction reference '{$txRef}' not found.");
        }

        $this->view('flutterwave.simulate_checkout', [
            'payment'     => $payment,
            'redirectUrl' => $redirectUrl,
            'csrf_token'  => $this->session->generateCsrfToken()
        ]);
    }

    /**
     * Handles payment success submit from Simulator
     */
    public function processSimulation(): void
    {
        $this->validateCsrf();
        $txRef = $this->request->get('tx_ref', '');
        $redirectUrl = $this->request->get('redirect', '');

        $id = 'flw_tx_' . bin2hex(random_bytes(6));
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE flutterwave_payments SET status = 'successful', transaction_id = :id WHERE tx_ref = :tx_ref");
        $stmt->execute([
            'id'     => $id,
            'tx_ref' => $txRef
        ]);

        $redirectUrlWithParams = $redirectUrl . (strpos($redirectUrl, '?') === false ? '?' : '&') . 'status=successful&tx_ref=' . urlencode($txRef) . '&transaction_id=' . urlencode($id);
        $this->response->redirect($redirectUrlWithParams);
    }

    /**
     * Handles standard redirect callback from Flutterwave
     */
    public function callback(): void
    {
        $status = $this->request->get('status', '');
        $txRef = $this->request->get('tx_ref', '');
        $transactionId = $this->request->get('transaction_id', $this->request->get('id', ''));

        if ($status === 'successful' || $status === 'success') {
            $flw = new \App\Services\Flutterwave();
            $verified = $flw->verifyPayment($transactionId, $txRef);

            if ($verified) {
                // Fulfill order
                $this->fulfillPayment($txRef);
                $this->session->setFlash('success', 'Payment successful! Your order has been processed and your downloads are now available.');
            } else {
                $this->session->setFlash('error', 'Payment verification failed.');
            }
        } else {
            $this->session->setFlash('error', 'Payment was cancelled or was not successful.');
        }

        $this->redirect('/wallet');
    }

    /**
     * Real-time Webhook Handler
     */
    public function webhook(): void
    {
        $rawPayload = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_VERIF_HASH'] ?? '';

        if (empty($rawPayload)) {
            $this->response->status(400);
            $this->json(['error' => 'Empty payload']);
            return;
        }

        $data = json_decode($rawPayload, true);
        if (!$data || !isset($data['data'])) {
            $this->response->status(400);
            $this->json(['error' => 'Invalid JSON']);
            return;
        }

        // 1. Retrieve keys
        $db = Database::connect();
        $stmt = $db->query("SELECT `key`, `value` FROM system_settings WHERE `key` LIKE 'flutterwave_%'");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $secretKey = $settings['flutterwave_secret_key'] ?? '';
        $encryptionKey = $settings['flutterwave_encryption_key'] ?? '';

        // 2. Validate webhook signature verif-hash (if configured)
        $isMock = (strpos($secretKey, 'mock-secret-key') !== false || empty($secretKey));
        $expectedHash = $encryptionKey ?: $secretKey;

        if (!$isMock && (empty($signature) || $signature !== $expectedHash)) {
            $this->response->status(401);
            $this->json(['error' => 'Unauthorized signature']);
            return;
        }

        $txRef = $data['data']['tx_ref'] ?? '';
        $status = $data['data']['status'] ?? '';
        $transactionId = (string)($data['data']['id'] ?? '');

        if ($status === 'successful' && !empty($txRef) && !empty($transactionId)) {
            // 3. Double-check with Flutterwave API to verify the actual amount, status, and currency
            $flw = new \App\Services\Flutterwave();
            if ($flw->verifyPayment($transactionId, $txRef)) {

                // Fetch the pending payment record
                $stmtPayment = $db->prepare("SELECT amount, currency FROM flutterwave_payments WHERE tx_ref = :tx_ref");
                $stmtPayment->execute(['tx_ref' => $txRef]);
                $payment = $stmtPayment->fetch();

                if ($payment) {
                    // Validate actual amount paid matches the expected amount
                    $payloadAmount = (float)($data['data']['amount'] ?? 0);
                    $payloadCurrency = $data['data']['currency'] ?? 'USD';
                    $expectedAmount = (float)$payment['amount'];

                    // Allow tiny rounding tolerance
                    if (abs($payloadAmount - $expectedAmount) <= 0.01 && $payloadCurrency === $payment['currency']) {
                        $stmtUpdate = $db->prepare("UPDATE flutterwave_payments SET status = 'successful', transaction_id = :id WHERE tx_ref = :tx_ref");
                        $stmtUpdate->execute([
                            'id'     => $transactionId,
                            'tx_ref' => $txRef
                        ]);

                        $this->fulfillPayment($txRef);
                        $this->json(['status' => 'success', 'message' => 'Payment fulfilled successfully']);
                        return;
                    } else {
                        error_log("Webhook verification mismatch. Paid: {$payloadAmount} {$payloadCurrency}, Expected: {$expectedAmount} {$payment['currency']}");
                    }
                }
            } else {
                error_log("Webhook validation: Flutterwave API verification failed for tx_ref: {$txRef}");
            }
        }

        $this->response->status(400);
        $this->json(['status' => 'error', 'message' => 'Invalid transaction status or verification failure']);
    }

    /**
     * Fulfills purchase: Unlocks downloads, credits creator/referrals, triggers automated notifications and emails.
     */
    private function fulfillPayment(string $txRef): void
    {
        $db = Database::connect();

        // Load payment record
        $stmt = $db->prepare("SELECT * FROM flutterwave_payments WHERE tx_ref = :tx_ref AND status = 'successful'");
        $stmt->execute(['tx_ref' => $txRef]);
        $payment = $stmt->fetch();

        if (!$payment) return;

        $userId = (int)$payment['user_id'];

        // Clear database shopping cart for this user
        $db->prepare("DELETE FROM shopping_cart WHERE user_id = :u")->execute(['u' => $userId]);
        $this->session->remove('cart');

        $userId = (int)$payment['user_id'];
        $productId = (int)$payment['product_id'];
        $finalAmount = (float)$payment['amount'];
        $referrerId = $payment['referrer_id'] ? (int)$payment['referrer_id'] : null;
        $couponId = $payment['coupon_id'] ? (int)$payment['coupon_id'] : null;

        // Verify if order is already completed to avoid duplicate delivery
        $stmtOrderCheck = $db->prepare("SELECT id FROM orders WHERE user_id = :u AND product_id = :p AND status = 'completed'");
        $stmtOrderCheck->execute(['u' => $userId, 'p' => $productId]);
        if ($stmtOrderCheck->fetch()) return; // Already processed!

        // Load commission and platform fee ratios
        $stmtComm = $db->query("SELECT platform_fee_percent FROM commission_settings WHERE id = 1");
        $commSettings = $stmtComm->fetch();
        $platformFeePercent = (float)($commSettings['platform_fee_percent'] ?? 5.00);

        // Platform fee
        $platformFee = $finalAmount * ($platformFeePercent / 100);

        // Load product specs
        $stmtProd = $db->prepare("SELECT name, creator_id FROM products WHERE id = :id");
        $stmtProd->execute(['id' => $productId]);
        $product = $stmtProd->fetch();
        $productName = $product['name'] ?? 'Digital Product';
        $creatorId = (int)($product['creator_id'] ?? 1);

        // Create completed order
        $stmtOrder = $db->prepare(
            "INSERT INTO orders (user_id, product_id, price, discount_applied, final_amount, platform_fee, status, referrer_id, coupon_id)
             VALUES (:user_id, :product_id, :price, 0.00, :final_amount, :platform_fee, 'completed', :referrer_id, :coupon_id)"
        );
        $stmtOrder->execute([
            'user_id'      => $userId,
            'product_id'   => $productId,
            'price'        => $finalAmount,
            'final_amount' => $finalAmount,
            'platform_fee' => $platformFee,
            'referrer_id'  => $referrerId,
            'coupon_id'    => $couponId
        ]);

        $orderId = (int)$db->lastInsertId();

        // Track Successful Purchase for Recommendation Analytics
        if ($referrerId) {
            $stmtRefLink = $db->prepare("SELECT id FROM referral_links WHERE user_id = :user_id AND product_id = :product_id");
            $stmtRefLink->execute(['user_id' => $referrerId, 'product_id' => $productId]);
            $refLink = $stmtRefLink->fetch();
            if ($refLink) {
                // Log event
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $stmtEvent = $db->prepare(
                    "INSERT INTO recommendation_events (referral_link_id, event_type, ip_address, user_agent)
                     VALUES (:link_id, 'purchase', :ip, :ua)"
                );
                $stmtEvent->execute([
                    'link_id' => $refLink['id'],
                    'ip'      => $ip,
                    'ua'      => $ua
                ]);
            }
        }

        // Load user profiles details
        $stmtBuyer = $db->prepare("SELECT full_name, email FROM users WHERE id = :id");
        $stmtBuyer->execute(['id' => $userId]);
        $buyer = $stmtBuyer->fetch();
        $buyerName = $buyer['full_name'] ?? 'Buyer';

        // 1. Notify Creator
        $creatorNotificationMsg = "Great news! Your digital product '{$productName}' was purchased by {$buyerName} for $" . number_format($finalAmount, 2) . ".";
        $stmtNotify = $db->prepare("INSERT INTO notifications (user_id, type, source_id, content) VALUES (:user_id, 'sale', :source_id, :content)");
        $stmtNotify->execute([
            'user_id'   => $creatorId,
            'source_id' => $orderId,
            'content'   => $creatorNotificationMsg
        ]);

        // 2. Notify Buyer
        $buyerNotificationMsg = "Thank you! Your purchase of '{$productName}' is confirmed. You can now download it instantly from your profile or the product page.";
        $stmtNotify->execute([
            'user_id'   => $userId,
            'source_id' => $orderId,
            'content'   => $buyerNotificationMsg
        ]);

        // Trigger multi-level affiliate marketing commissions
        CommissionEngine::processOrderCommissions($orderId);

        // Auto unlock Sales Partner status after purchasing any digital product
        $db->prepare("UPDATE users SET is_sales_partner = 1 WHERE id = :id")->execute(['id' => $userId]);

        // Trigger automated purchase confirmation email to buyer
        $cartMock = [['name' => $productName, 'price' => $finalAmount, 'type' => 'Digital Pack']];
        Mailer::sendPurchaseConfirmation($buyer['email'], $buyerName, $cartMock, $finalAmount);
    }
}
