<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class CommissionEngine
{
    public static function processOrderCommissions(int $orderId): void
    {
        $db = Database::connect();

        // 1. Fetch Order details
        $stmt = $db->prepare(
            "SELECT o.*, p.creator_id, p.name as product_name, p.price as original_price
             FROM orders o
             JOIN products p ON o.product_id = p.id
             WHERE o.id = :orderId"
        );
        $stmt->execute(['orderId' => $orderId]);
        $order = $stmt->fetch();

        if (!$order) return;

        $finalAmount = (float)$order['final_amount'];
        $platformFee = (float)$order['platform_fee'];
        $creatorId = (int)$order['creator_id'];
        $directReferrerId = $order['referrer_id'] ? (int)$order['referrer_id'] : null;

        // Fetch commission settings
        $stmtSettings = $db->query("SELECT * FROM commission_settings WHERE id = 1");
        $settings = $stmtSettings->fetch();

        $levels = [
            1 => (float)($settings['level_1_percent'] ?? 10.00),
            2 => (float)($settings['level_2_percent'] ?? 5.00),
            3 => (float)($settings['level_3_percent'] ?? 3.00),
            4 => (float)($settings['level_4_percent'] ?? 2.00),
            5 => (float)($settings['level_5_percent'] ?? 1.00),
        ];

        $totalReferralCommission = 0.00;
        $referrerChain = [];

        // Build the referral chain starting from direct referrer
        if ($directReferrerId) {
            $referrerChain[1] = $directReferrerId;

            // Ascend the referred_by chain up to 5 levels
            $currentReferrerId = $directReferrerId;
            for ($level = 2; $level <= 5; $level++) {
                $stmtRef = $db->prepare("SELECT referred_by FROM users WHERE id = :id");
                $stmtRef->execute(['id' => $currentReferrerId]);
                $userRef = $stmtRef->fetch();

                if ($userRef && $userRef['referred_by']) {
                    $nextReferrerId = (int)$userRef['referred_by'];
                    // Prevent circular or self-referral loop
                    if (in_array($nextReferrerId, $referrerChain) || $nextReferrerId === (int)$order['user_id']) {
                        break;
                    }
                    $referrerChain[$level] = $nextReferrerId;
                    $currentReferrerId = $nextReferrerId;
                } else {
                    break;
                }
            }
        }

        // Process referral commissions for each level found
        foreach ($referrerChain as $level => $refId) {
            $percent = $levels[$level];
            $commissionAmount = $finalAmount * ($percent / 100);

            if ($commissionAmount > 0) {
                // Credit the referrer's wallet
                $stmtWallet = $db->prepare(
                    "UPDATE wallets SET balance = balance + :amount WHERE user_id = :user_id"
                );
                $stmtWallet->execute(['amount' => $commissionAmount, 'user_id' => $refId]);

                // Create transaction record
                $stmtTx = $db->prepare(
                    "INSERT INTO transactions (wallet_id, amount, type, order_id, description, status)
                     VALUES (:wallet_id, :amount, 'commission', :order_id, :description, 'completed')"
                );
                $stmtTx->execute([
                    'wallet_id' => $refId,
                    'amount'    => $commissionAmount,
                    'order_id'  => $orderId,
                    'description'=> "Level {$level} affiliate commission for product '{$order['product_name']}'"
                ]);

                // Send notification to referrer
                $stmtNotify = $db->prepare(
                    "INSERT INTO notifications (user_id, type, source_id, content)
                     VALUES (:user_id, 'commission', :source_id, :content)"
                );
                $stmtNotify->execute([
                    'user_id'   => $refId,
                    'source_id' => $orderId,
                    'content'   => "Earned Level {$level} affiliate commission of $" . number_format($commissionAmount, 2) . " for product '{$order['product_name']}'!"
                ]);

                // Send automated commission email
                $stmtRefDetails = $db->prepare("SELECT email, full_name FROM users WHERE id = :id");
                $stmtRefDetails->execute(['id' => $refId]);
                $refDetails = $stmtRefDetails->fetch();
                if ($refDetails) {
                    \App\Services\Mailer::sendCommissionEarned(
                        $refDetails['email'],
                        $refDetails['full_name'],
                        $order['product_name'],
                        $level,
                        $commissionAmount
                    );
                }

                $totalReferralCommission += $commissionAmount;
            }
        }

        // Calculate Creator Royalty
        // Standard: Price - Platform Fee - Total Referral Commission splits paid out
        $creatorRoyalty = max(0.00, $finalAmount - $platformFee - $totalReferralCommission);

        // Credit Creator's Wallet
        $stmtCreditCreator = $db->prepare(
            "UPDATE wallets SET balance = balance + :amount WHERE user_id = :user_id"
        );
        $stmtCreditCreator->execute(['amount' => $creatorRoyalty, 'user_id' => $creatorId]);

        // Create transaction record for Creator
        $stmtTxCreator = $db->prepare(
            "INSERT INTO transactions (wallet_id, amount, type, order_id, description, status)
             VALUES (:wallet_id, :amount, 'sale', :order_id, :description, 'completed')"
        );
        $stmtTxCreator->execute([
            'wallet_id' => $creatorId,
            'amount'    => $creatorRoyalty,
            'order_id'  => $orderId,
            'description'=> "Royalty earnings for sale of digital product '{$order['product_name']}'"
        ]);

        // Send automated royalty earned email to Creator
        $stmtCreatorDetails = $db->prepare("SELECT email, full_name FROM users WHERE id = :id");
        $stmtCreatorDetails->execute(['id' => $creatorId]);
        $creatorDetails = $stmtCreatorDetails->fetch();
        if ($creatorDetails) {
            \App\Services\Mailer::sendRoyaltyEarned(
                $creatorDetails['email'],
                $creatorDetails['full_name'],
                $order['product_name'],
                $creatorRoyalty
            );
        }

        // Update the order with computed splits
        $stmtUpdateOrder = $db->prepare(
            "UPDATE orders
             SET creator_royalty = :creator_royalty, referral_commission = :referral_commission
             WHERE id = :id"
        );
        $stmtUpdateOrder->execute([
            'creator_royalty'     => $creatorRoyalty,
            'referral_commission' => $totalReferralCommission,
            'id'                  => $orderId
        ]);
    }
}
