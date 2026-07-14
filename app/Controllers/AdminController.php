<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Services\Mailer;

class AdminController extends Controller
{
    protected User $userModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->userModel = new User();
    }

    public function dashboard(): void
    {
        // 1. Fetch system metrics
        $usersCount = $this->userModel->fetch("SELECT COUNT(*) as qty FROM users")['qty'] ?? 0;
        $productsCount = $this->userModel->fetch("SELECT COUNT(*) as qty FROM products")['qty'] ?? 0;
        $ordersCount = $this->userModel->fetch("SELECT COUNT(*) as qty FROM orders WHERE status = 'completed'")['qty'] ?? 0;
        $totalRevenue = $this->userModel->fetch("SELECT SUM(final_amount) as rev FROM orders WHERE status = 'completed'")['rev'] ?? 0.00;
        $platformFeesEarned = $this->userModel->fetch("SELECT SUM(platform_fee) as fee FROM orders WHERE status = 'completed'")['fee'] ?? 0.00;

        $pendingCreators = $this->userModel->fetch("SELECT COUNT(*) as qty FROM creator_applications WHERE status = 'pending'")['qty'] ?? 0;
        $pendingWithdrawals = $this->userModel->fetch("SELECT COUNT(*) as qty FROM withdrawals WHERE status = 'pending'")['qty'] ?? 0;

        // 2. Fetch recent orders
        $recentOrders = $this->userModel->fetchAll(
            "SELECT o.*, u.username as buyer_username, p.name as product_name
             FROM orders o
             JOIN users u ON o.user_id = u.id
             JOIN products p ON o.product_id = p.id
             ORDER BY o.created_at DESC LIMIT 10"
        );

        $this->view('admin.dashboard', [
            'usersCount'         => $usersCount,
            'productsCount'      => $productsCount,
            'ordersCount'        => $ordersCount,
            'totalRevenue'       => $totalRevenue,
            'platformFeesEarned' => $platformFeesEarned,
            'pendingCreators'    => $pendingCreators,
            'pendingWithdrawals' => $pendingWithdrawals,
            'recentOrders'       => $recentOrders,
            'csrf_token'         => $this->session->generateCsrfToken()
        ]);
    }

    public function users(): void
    {
        $users = $this->userModel->fetchAll("SELECT * FROM users ORDER BY created_at DESC");
        $this->view('admin.users', [
            'users'      => $users,
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }

    public function verifyUser(int $id): void
    {
        $this->validateCsrf();
        $this->userModel->query("UPDATE users SET is_verified = 1 WHERE id = :id", ['id' => $id]);
        $this->session->setFlash('success', "User verified successfully.");
        $this->redirect('/admin/users');
    }

    public function creatorApplications(): void
    {
        $apps = $this->userModel->fetchAll(
            "SELECT a.*, u.username, u.full_name, u.email
             FROM creator_applications a
             JOIN users u ON a.user_id = u.id
             ORDER BY a.created_at DESC"
        );

        $this->view('admin.creator_applications', [
            'applications' => $apps,
            'csrf_token'   => $this->session->generateCsrfToken()
        ]);
    }

    public function approveCreator(int $id): void
    {
        $this->validateCsrf();

        $app = $this->userModel->fetch("SELECT * FROM creator_applications WHERE id = :id", ['id' => $id]);
        if ($app) {
            // Update Application status
            $this->userModel->query(
                "UPDATE creator_applications SET status = 'approved', admin_note = 'Approved by Admin' WHERE id = :id",
                ['id' => $id]
            );

            // Update user role to Creator
            $this->userModel->query(
                "UPDATE users SET role = 'creator' WHERE id = :user_id",
                ['user_id' => $app['user_id']]
            );

            // Send notification
            $this->userModel->query(
                "INSERT INTO notifications (user_id, type, content) VALUES (:user_id, 'system', 'Congratulations! Your Creator Application has been approved. Welcome to the Creator Program.')",
                ['user_id' => $app['user_id']]
            );

            $this->session->setFlash('success', "Creator application approved successfully.");
        }

        $this->redirect('/admin/creator-applications');
    }

    public function rejectCreator(int $id): void
    {
        $this->validateCsrf();
        $note = trim($this->request->get('admin_note', 'Rejected by Admin.'));

        $app = $this->userModel->fetch("SELECT * FROM creator_applications WHERE id = :id", ['id' => $id]);
        if ($app) {
            $this->userModel->query(
                "UPDATE creator_applications SET status = 'rejected', admin_note = :note WHERE id = :id",
                ['id' => $id, 'note' => $note]
            );

            $this->userModel->query(
                "INSERT INTO notifications (user_id, type, content) VALUES (:user_id, 'system', 'Your Creator Application has been rejected. Note: " . htmlspecialchars($note) . "')",
                ['user_id' => $app['user_id']]
            );

            $this->session->setFlash('success', "Creator application rejected.");
        }

        $this->redirect('/admin/creator-applications');
    }

    public function products(): void
    {
        $products = $this->userModel->fetchAll(
            "SELECT p.*, u.username as creator_username, c.name as category_name
             FROM products p
             JOIN users u ON p.creator_id = u.id
             JOIN categories c ON p.category_id = c.id
             ORDER BY p.created_at DESC"
        );

        $this->view('admin.products', [
            'products'   => $products,
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }

    public function approveProduct(int $id): void
    {
        $this->validateCsrf();
        $this->userModel->query("UPDATE products SET status = 'active' WHERE id = :id", ['id' => $id]);

        $prod = $this->userModel->fetch("SELECT creator_id, name FROM products WHERE id = :id", ['id' => $id]);
        if ($prod) {
            $this->userModel->query(
                "INSERT INTO notifications (user_id, type, content) VALUES (:user_id, 'system', 'Your product \"" . htmlspecialchars($prod['name']) . "\" has been approved and is now active in the Marketplace!')",
                ['user_id' => $prod['creator_id']]
            );

            // Trigger retargeting launch emails to previous buyers
            try {
                \App\Services\Mailer::sendNewProductAlertToPreviousBuyers($id);
            } catch (\Exception $e) {
                error_log("Failed to send launch alert from admin: " . $e->getMessage());
            }
        }

        $this->session->setFlash('success', "Product approved and listed successfully.");
        $this->redirect('/admin/products');
    }

    public function rejectProduct(int $id): void
    {
        $this->validateCsrf();
        $this->userModel->query("UPDATE products SET status = 'rejected' WHERE id = :id", ['id' => $id]);

        $prod = $this->userModel->fetch("SELECT creator_id, name FROM products WHERE id = :id", ['id' => $id]);
        if ($prod) {
            $this->userModel->query(
                "INSERT INTO notifications (user_id, type, content) VALUES (:user_id, 'system', 'Your product \"" . htmlspecialchars($prod['name']) . "\" was rejected by the moderation team.')",
                ['user_id' => $prod['creator_id']]
            );
        }

        $this->session->setFlash('success', "Product rejected successfully.");
        $this->redirect('/admin/products');
    }

    public function orders(): void
    {
        $orders = $this->userModel->fetchAll(
            "SELECT o.*, u.username as buyer_username, p.name as product_name, c.username as creator_username
             FROM orders o
             JOIN users u ON o.user_id = u.id
             JOIN products p ON o.product_id = p.id
             JOIN users c ON p.creator_id = c.id
             ORDER BY o.created_at DESC"
        );

        $this->view('admin.orders', [
            'orders' => $orders
        ]);
    }

    public function withdrawals(): void
    {
        $withdrawals = $this->userModel->fetchAll(
            "SELECT w.*, u.username, u.full_name, u.email
             FROM withdrawals w
             JOIN users u ON w.user_id = u.id
             ORDER BY w.created_at DESC"
        );

        $this->view('admin.withdrawals', [
            'withdrawals' => $withdrawals,
            'csrf_token'  => $this->session->generateCsrfToken()
        ]);
    }

    public function approveWithdrawal(int $id): void
    {
        $this->validateCsrf();

        $w = $this->userModel->fetch("SELECT * FROM withdrawals WHERE id = :id", ['id' => $id]);
        if ($w && $w['status'] === 'pending') {
            // Update withdrawal status to approved
            $this->userModel->query(
                "UPDATE withdrawals SET status = 'approved', processed_at = NOW() WHERE id = :id",
                ['id' => $id]
            );

            // Update transaction logs
            $this->userModel->query(
                "UPDATE transactions SET status = 'completed'
                 WHERE wallet_id = :user_id AND type = 'withdrawal' AND status = 'pending'
                 ORDER BY created_at DESC LIMIT 1",
                ['user_id' => $w['user_id']]
            );

            // Send system notifications
            $this->userModel->query(
                "INSERT INTO notifications (user_id, type, content) VALUES (:user_id, 'system', 'Your withdrawal request of $" . number_format($w['amount'], 2) . " was approved and processed.')",
                ['user_id' => $w['user_id']]
            );

            // Fetch user info for mailer
            $user = $this->userModel->findById((int)$w['user_id']);
            if ($user) {
                Mailer::sendWithdrawalStatusUpdate($user['email'], $user['full_name'], (float)$w['amount'], 'approved');
            }

            $this->session->setFlash('success', "Withdrawal request approved.");
        }

        $this->redirect('/admin/withdrawals');
    }

    public function rejectWithdrawal(int $id): void
    {
        $this->validateCsrf();

        $w = $this->userModel->fetch("SELECT * FROM withdrawals WHERE id = :id", ['id' => $id]);
        if ($w && $w['status'] === 'pending') {
            // Update status to rejected
            $this->userModel->query(
                "UPDATE withdrawals SET status = 'rejected', processed_at = NOW() WHERE id = :id",
                ['id' => $id]
            );

            // Return balance back to user's wallet!
            $this->userModel->query(
                "UPDATE wallets SET balance = balance + :amount WHERE user_id = :user_id",
                ['amount' => $w['amount'], 'user_id' => $w['user_id']]
            );

            // Cancel the transaction record
            $this->userModel->query(
                "UPDATE transactions SET status = 'failed'
                 WHERE wallet_id = :user_id AND type = 'withdrawal' AND status = 'pending'
                 ORDER BY created_at DESC LIMIT 1",
                ['user_id' => $w['user_id']]
            );

            // Send notifications
            $this->userModel->query(
                "INSERT INTO notifications (user_id, type, content) VALUES (:user_id, 'system', 'Your withdrawal request of $" . number_format($w['amount'], 2) . " was rejected. Balance refunded to wallet.')",
                ['user_id' => $w['user_id']]
            );

            // Fetch user info for mailer
            $user = $this->userModel->findById((int)$w['user_id']);
            if ($user) {
                Mailer::sendWithdrawalStatusUpdate($user['email'], $user['full_name'], (float)$w['amount'], 'rejected');
            }

            $this->session->setFlash('success', "Withdrawal request rejected and balance refunded.");
        }

        $this->redirect('/admin/withdrawals');
    }

    public function settings(): void
    {
        // Handle Post request to save settings
        if ($this->request->isPost()) {
            $this->validateCsrf();

            // Save general system settings
            $keys = [
                'platform_name', 'platform_brand_color', 'maintenance_mode',
                'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_secure',
                'paypal_client_id', 'paypal_secret', 'stripe_key', 'stripe_secret',
                'feature_toggle_chat', 'feature_toggle_marketplace', 'feature_toggle_social',
                'seo_title', 'seo_description'
            ];

            foreach ($keys as $k) {
                $val = $this->request->get($k, '');
                $this->userModel->query(
                    "INSERT INTO system_settings (`key`, `value`) VALUES (:k, :v) ON DUPLICATE KEY UPDATE `value` = :v2",
                    ['k' => $k, 'v' => $val, 'v2' => $val]
                );
            }

            // Update commission ratios
            $this->userModel->query(
                "UPDATE commission_settings
                 SET level_1_percent = :l1, level_2_percent = :l2, level_3_percent = :l3, level_4_percent = :l4, level_5_percent = :l5,
                     platform_fee_percent = :p_fee, min_withdrawal_amount = :min_w, attribution_window_days = :attr
                 WHERE id = 1",
                [
                    'l1'    => (float)$this->request->get('level_1_percent', 10.00),
                    'l2'    => (float)$this->request->get('level_2_percent', 5.00),
                    'l3'    => (float)$this->request->get('level_3_percent', 3.00),
                    'l4'    => (float)$this->request->get('level_4_percent', 2.00),
                    'l5'    => (float)$this->request->get('level_5_percent', 1.00),
                    'p_fee' => (float)$this->request->get('platform_fee_percent', 5.00),
                    'min_w' => (float)$this->request->get('min_withdrawal_amount', 50.00),
                    'attr'  => (int)$this->request->get('attribution_window_days', 30)
                ]
            );

            $this->session->setFlash('success', "System and commission settings updated successfully.");
            $this->redirect('/admin/settings');
        }

        // Fetch current settings
        $settings = $this->userModel->fetchAll("SELECT * FROM system_settings");
        $settingsMap = [];
        foreach ($settings as $s) {
            $settingsMap[$s['key']] = $s['value'];
        }

        $commissions = $this->userModel->fetch("SELECT * FROM commission_settings WHERE id = 1");

        $this->view('admin.settings', [
            'settings'    => $settingsMap,
            'commissions' => $commissions,
            'csrf_token'  => $this->session->generateCsrfToken()
        ]);
    }
}
