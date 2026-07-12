<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class ProfileController extends Controller
{
    protected User $userModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->userModel = new User();
    }

    public function index(string $username): void
    {
        $profileUser = $this->userModel->findByUsername($username);
        if (!$profileUser) {
            $this->response->setStatusCode(404);
            die("User @{$username} not found.");
        }

        $currentUserId = $this->authId();
        $isOwnProfile = ($currentUserId === (int)$profileUser['id']);

        // Follow state
        $isFollowing = false;
        if ($currentUserId && !$isOwnProfile) {
            $isFollowing = $this->userModel->isFollowing($currentUserId, (int)$profileUser['id']);
        }

        // Stats
        $followersCount = $this->userModel->getFollowersCount((int)$profileUser['id']);
        $followingCount = $this->userModel->getFollowingCount((int)$profileUser['id']);

        // Retrieve posts by this user
        $posts = $this->userModel->fetchAll(
            "SELECT p.*, u.username, u.full_name, u.avatar_url,
                    (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as likes_count,
                    (SELECT COUNT(*) FROM posts WHERE parent_id = p.id) as replies_count,
                    (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id AND user_id = :curr1) as is_liked,
                    (SELECT COUNT(*) FROM post_bookmarks WHERE post_id = p.id AND user_id = :curr2) as is_bookmarked
             FROM posts p
             JOIN users u ON p.user_id = u.id
             WHERE p.user_id = :user_id AND p.parent_id IS NULL
             ORDER BY p.created_at DESC",
            [
                'curr1'   => $currentUserId ?? 0,
                'curr2'   => $currentUserId ?? 0,
                'user_id' => $profileUser['id']
            ]
        );

        // Retrieve products if they are creator or admin
        $products = [];
        if (in_array($profileUser['role'], ['creator', 'admin'])) {
            $products = $this->userModel->fetchAll(
                "SELECT p.*, c.name as category_name
                 FROM products p
                 JOIN categories c ON p.category_id = c.id
                 WHERE p.creator_id = :creator_id AND p.status = 'active'
                 ORDER BY p.created_at DESC",
                ['creator_id' => $profileUser['id']]
            );
        }

        // Wallet details (only if own profile)
        $wallet = null;
        $transactions = [];
        $creatorApplication = null;
        $withdrawalHistory = [];

        if ($isOwnProfile) {
            $wallet = $this->userModel->getWallet($currentUserId);
            $transactions = $this->userModel->fetchAll(
                "SELECT * FROM transactions WHERE wallet_id = :wallet_id ORDER BY created_at DESC",
                ['wallet_id' => $currentUserId]
            );
            $creatorApplication = $this->userModel->fetch(
                "SELECT * FROM creator_applications WHERE user_id = :user_id",
                ['user_id' => $currentUserId]
            );
            $withdrawalHistory = $this->userModel->fetchAll(
                "SELECT * FROM withdrawals WHERE user_id = :user_id ORDER BY created_at DESC",
                ['user_id' => $currentUserId]
            );
        }

        $this->view('profile.index', [
            'profileUser'        => $profileUser,
            'isOwnProfile'       => $isOwnProfile,
            'isFollowing'        => $isFollowing,
            'followersCount'     => $followersCount,
            'followingCount'     => $followingCount,
            'posts'              => $posts,
            'products'           => $products,
            'wallet'             => $wallet,
            'transactions'       => $transactions,
            'creatorApplication' => $creatorApplication,
            'withdrawalHistory'  => $withdrawalHistory,
            'csrf_token'         => $this->session->generateCsrfToken()
        ]);
    }

    public function update(): void
    {
        $this->validateCsrf();
        $userId = $this->authId();
        if (!$userId) {
            $this->redirect('/auth/login');
        }

        $fullName = trim($this->request->get('full_name', ''));
        $bio = trim($this->request->get('bio', ''));

        if (empty($fullName)) {
            $this->session->setFlash('error', 'Full Name cannot be empty.');
            $this->redirect('/profile/' . $this->authUser()['username']);
        }

        $updateData = [
            'full_name' => $fullName,
            'bio'       => $bio
        ];

        // Handle Avatar Upload securely
        $files = $this->request->getFiles();
        if (isset($files['avatar']) && $files['avatar']['error'] === UPLOAD_ERR_OK) {
            $file = $files['avatar'];
            $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowedTypes)) {
                $this->session->setFlash('error', 'Invalid avatar format. Only JPG, PNG, WEBP are allowed.');
                $this->redirect('/profile/' . $this->authUser()['username']);
            }

            // Create uploads directory if not exists
            $uploadDir = PUBLIC_PATH . '/uploads/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFileName = 'avatar_' . $userId . '_' . time() . '.' . $extension;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                $updateData['avatar_url'] = '/uploads/avatars/' . $newFileName;
            } else {
                $this->session->setFlash('error', 'Failed to save uploaded avatar.');
            }
        }

        $this->userModel->updateProfile($userId, $updateData);

        // Refresh user session info
        $refreshedUser = $this->userModel->findById($userId);
        $this->session->set('user', $refreshedUser);

        $this->session->setFlash('success', 'Profile updated successfully.');
        $this->redirect('/profile/' . $refreshedUser['username']);
    }

    public function follow(int $id): void
    {
        $this->validateCsrf();
        $followerId = $this->authId();
        if (!$followerId) {
            $this->json(['success' => false, 'error' => 'Please login.'], 401);
        }

        if ($followerId === $id) {
            $this->json(['success' => false, 'error' => 'You cannot follow yourself.'], 400);
        }

        $targetUser = $this->userModel->findById($id);
        if (!$targetUser) {
            $this->json(['success' => false, 'error' => 'User not found.'], 404);
        }

        $isFollowing = $this->userModel->isFollowing($followerId, $id);

        if ($isFollowing) {
            $this->userModel->unfollow($followerId, $id);
            $action = 'unfollowed';
        } else {
            $this->userModel->follow($followerId, $id);
            $action = 'followed';

            // Send notification to followed user
            $followerUsername = $this->authUser()['username'];
            $this->userModel->query(
                "INSERT INTO notifications (user_id, type, sender_id, source_id, content)
                 VALUES (:user_id, 'follow', :sender_id, :source_id, :content)",
                [
                    'user_id'   => $id,
                    'sender_id' => $followerId,
                    'source_id' => $followerId,
                    'content'   => "@{$followerUsername} started following you!"
                ]
            );
        }

        $followersCount = $this->userModel->getFollowersCount($id);

        $this->json([
            'success'        => true,
            'action'         => $action,
            'followersCount' => $followersCount
        ]);
    }

    public function applyCreator(): void
    {
        $this->validateCsrf();
        $userId = $this->authId();
        if (!$userId) {
            $this->redirect('/auth/login');
        }

        $bio = trim($this->request->get('creator_bio', ''));
        $portfolio = trim($this->request->get('portfolio_url', ''));

        if (empty($bio)) {
            $this->session->setFlash('error', 'Please provide a short description of what digital products you plan to sell.');
            $this->redirect('/profile/' . $this->authUser()['username']);
        }

        $this->userModel->query(
            "INSERT INTO creator_applications (user_id, bio, portfolio_url, status)
             VALUES (:user_id, :bio, :portfolio_url, 'pending')
             ON DUPLICATE KEY UPDATE bio = :bio2, portfolio_url = :portfolio_url2, status = 'pending'",
            [
                'user_id'            => $userId,
                'bio'                => $bio,
                'portfolio_url'      => $portfolio,
                'bio2'               => $bio,
                'portfolio_url2'     => $portfolio
            ]
        );

        $this->session->setFlash('success', 'Your Creator Application has been submitted and is pending administrator review.');
        $this->redirect('/profile/' . $this->authUser()['username']);
    }

    public function requestWithdrawal(): void
    {
        $this->validateCsrf();
        $userId = $this->authId();
        if (!$userId) {
            $this->redirect('/auth/login');
        }

        $amount = (float)$this->request->get('amount', 0);
        $destination = trim($this->request->get('destination', ''));

        // Load wallet balance
        $wallet = $this->userModel->getWallet($userId);
        $balance = $wallet ? (float)$wallet['balance'] : 0.00;

        // Fetch min withdrawal setting
        $settings = $this->userModel->fetch("SELECT min_withdrawal_amount FROM commission_settings WHERE id = 1");
        $minWithdrawal = (float)($settings['min_withdrawal_amount'] ?? 50.00);

        if ($amount < $minWithdrawal) {
            $this->session->setFlash('error', "The minimum withdrawal amount is $" . number_format($minWithdrawal, 2) . ".");
            $this->redirect('/profile/' . $this->authUser()['username']);
        }

        if ($amount > $balance) {
            $this->session->setFlash('error', "Insufficient funds. Your available balance is $" . number_format($balance, 2) . ".");
            $this->redirect('/profile/' . $this->authUser()['username']);
        }

        if (empty($destination)) {
            $this->session->setFlash('error', "Please provide payout destination details.");
            $this->redirect('/profile/' . $this->authUser()['username']);
        }

        // DB Transaction for safety
        $db = \App\Core\Database::connect();
        try {
            $db->beginTransaction();

            // 1. Debit from wallet balance
            $stmtDebit = $db->prepare("UPDATE wallets SET balance = balance - :amount WHERE user_id = :user_id");
            $stmtDebit->execute(['amount' => $amount, 'user_id' => $userId]);

            // 2. Create withdrawal request
            $stmtReq = $db->prepare(
                "INSERT INTO withdrawals (user_id, amount, destination, status)
                 VALUES (:user_id, :amount, :destination, 'pending')"
            );
            $stmtReq->execute([
                'user_id'     => $userId,
                'amount'      => $amount,
                'destination' => $destination
            ]);

            // 3. Create transaction log
            $stmtTx = $db->prepare(
                "INSERT INTO transactions (wallet_id, amount, type, description, status)
                 VALUES (:wallet_id, :amount, 'withdrawal', :desc, 'pending')"
            );
            $stmtTx->execute([
                'wallet_id' => $userId,
                'amount'    => -$amount,
                'desc'      => "Requested withdrawal to " . substr($destination, 0, 40)
            ]);

            $db->commit();
            $this->session->setFlash('success', "Your withdrawal request for $" . number_format($amount, 2) . " has been submitted for review.");

        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Withdrawal processing error: " . $e->getMessage());
            $this->session->setFlash('error', "Withdrawal submission failed. Please try again.");
        }

        $this->redirect('/profile/' . $this->authUser()['username']);
    }
}
