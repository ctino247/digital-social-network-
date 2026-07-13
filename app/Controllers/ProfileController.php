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

        // Fetch products card info for each post if product_id exists
        foreach ($posts as &$post) {
            if ($post['product_id']) {
                $post['product'] = (new \App\Models\Post())->getProductCardDetails((int)$post['product_id']);
            }
        }

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

        // Fetch creator metrics (Average Rating, total reviews)
        $avgRating = 5.0;
        $totalReviews = 0;
        if (in_array($profileUser['role'], ['creator', 'admin'])) {
            $metrics = $this->userModel->fetch(
                "SELECT AVG(r.rating) as avg, COUNT(r.id) as qty
                 FROM product_reviews r
                 JOIN products p ON r.product_id = p.id
                 WHERE p.creator_id = :id",
                ['id' => $profileUser['id']]
            );
            $avgRating = $metrics['avg'] ? (float)$metrics['avg'] : 5.0;
            $totalReviews = (int)($metrics['qty'] ?? 0);
        }

        // Fetch Recommendation Feed posts (posts they shared recommending a product)
        $recommendationFeed = $this->userModel->fetchAll(
            "SELECT p.*, u.username, u.full_name, u.avatar_url,
                    (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as likes_count,
                    (SELECT COUNT(*) FROM posts WHERE parent_id = p.id) as replies_count,
                    (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id AND user_id = :curr1) as is_liked,
                    (SELECT COUNT(*) FROM post_bookmarks WHERE post_id = p.id AND user_id = :curr2) as is_bookmarked
             FROM posts p
             JOIN users u ON p.user_id = u.id
             WHERE p.user_id = :user_id AND p.product_id IS NOT NULL AND p.parent_id IS NULL
             ORDER BY p.created_at DESC",
            [
                'curr1'   => $currentUserId ?? 0,
                'curr2'   => $currentUserId ?? 0,
                'user_id' => $profileUser['id']
            ]
        );
        foreach ($recommendationFeed as &$recPost) {
            $recPost['product'] = (new \App\Models\Post())->getProductCardDetails((int)$recPost['product_id']);
        }

        // Fetch list of products they have recommended (referral_links)
        $recommendedProducts = $this->userModel->fetchAll(
            "SELECT p.*, c.name as category_name, u.username as creator_username
             FROM referral_links r
             JOIN products p ON r.product_id = p.id
             JOIN categories c ON p.category_id = c.id
             JOIN users u ON p.creator_id = u.id
             WHERE r.user_id = :user_id AND p.status = 'active'
             ORDER BY r.created_at DESC",
            ['user_id' => $profileUser['id']]
        );

        // Fetch list of followers / following for direct tabs display
        $followersList = $this->userModel->getFollowers((int)$profileUser['id']);
        $followingList = $this->userModel->getFollowing((int)$profileUser['id']);

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
            'profileUser'         => $profileUser,
            'isOwnProfile'        => $isOwnProfile,
            'isFollowing'         => $isFollowing,
            'followersCount'      => $followersCount,
            'followingCount'      => $followingCount,
            'posts'               => $posts,
            'products'            => $products,
            'avgRating'           => $avgRating,
            'totalReviews'        => $totalReviews,
            'recommendationFeed'  => $recommendationFeed,
            'recommendedProducts' => $recommendedProducts,
            'followersList'       => $followersList,
            'followingList'       => $followingList,
            'wallet'              => $wallet,
            'transactions'        => $transactions,
            'creatorApplication'  => $creatorApplication,
            'withdrawalHistory'   => $withdrawalHistory,
            'csrf_token'          => $this->session->generateCsrfToken()
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

        // Fetch settings
        $settings = $this->userModel->fetch("SELECT * FROM commission_settings WHERE id = 1");
        $minWithdrawal = (float)($settings['min_withdrawal_amount'] ?? 50.00);
        $maxWithdrawal = (float)($settings['max_withdrawal_amount'] ?? 5000.00);
        $withdrawalFee = (float)($settings['withdrawal_fee'] ?? 0.00);
        $withdrawalsEnabled = (int)($settings['withdrawals_enabled'] ?? 1);

        if (!$withdrawalsEnabled) {
            $this->session->setFlash('error', "Withdrawals are currently disabled by the system administrator.");
            $this->redirect('/wallet');
        }

        if ($amount < $minWithdrawal) {
            $this->session->setFlash('error', "The minimum withdrawal amount is $" . number_format($minWithdrawal, 2) . ".");
            $this->redirect('/wallet');
        }

        if ($amount > $maxWithdrawal) {
            $this->session->setFlash('error', "The maximum withdrawal amount per request is $" . number_format($maxWithdrawal, 2) . ".");
            $this->redirect('/wallet');
        }

        // Total deduction including fee
        $totalDeduction = $amount + $withdrawalFee;

        if ($totalDeduction > $balance) {
            $this->session->setFlash('error', "Insufficient funds. To withdraw $" . number_format($amount, 2) . ", you need a balance of $" . number_format($totalDeduction, 2) . " (including a $" . number_format($withdrawalFee, 2) . " transaction fee). Your available balance is $" . number_format($balance, 2) . ".");
            $this->redirect('/wallet');
        }

        if (empty($destination)) {
            $this->session->setFlash('error', "Please provide payout destination details.");
            $this->redirect('/wallet');
        }

        // DB Transaction for safety
        $db = \App\Core\Database::connect();
        try {
            $db->beginTransaction();

            // 1. Debit from wallet balance
            $stmtDebit = $db->prepare("UPDATE wallets SET balance = balance - :amount WHERE user_id = :user_id");
            $stmtDebit->execute(['amount' => $totalDeduction, 'user_id' => $userId]);

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
                'amount'    => -$totalDeduction,
                'desc'     => "Withdrawal request to " . substr($destination, 0, 40) . " (Fee: $" . number_format($withdrawalFee, 2) . ")"
            ]);

            $db->commit();
            $this->session->setFlash('success', "Your withdrawal request for $" . number_format($amount, 2) . " has been submitted for review.");

        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Withdrawal processing error: " . $e->getMessage());
            $this->session->setFlash('error', "Withdrawal submission failed. Please try again.");
        }

        $this->redirect('/wallet');
    }

    /**
     * Display Followers list with real-time searches
     */
    public function followers(string $username): void
    {
        $profileUser = $this->userModel->findByUsername($username);
        if (!$profileUser) {
            $this->response->setStatusCode(404);
            die("User not found.");
        }

        $search = trim($this->request->get('q', ''));
        $currentUserId = $this->authId();

        // Query followers with optional search filters
        $sql = "SELECT u.*
                FROM follows f
                JOIN users u ON f.follower_id = u.id
                WHERE f.followed_id = :user_id";

        $params = ['user_id' => $profileUser['id']];

        if (!empty($search)) {
            $sql .= " AND (u.username LIKE :q1 OR u.full_name LIKE :q2)";
            $params['q1'] = '%' . $search . '%';
            $params['q2'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY f.created_at DESC";
        $followers = $this->userModel->fetchAll($sql, $params);

        // Map follow states for button toggle
        foreach ($followers as &$f) {
            $f['is_following'] = $currentUserId ? $this->userModel->isFollowing($currentUserId, (int)$f['id']) : false;
        }

        $this->view('profile.followers', [
            'profileUser' => $profileUser,
            'followers'   => $followers,
            'search'      => $search,
            'csrf_token'  => $this->session->generateCsrfToken()
        ]);
    }

    /**
     * Display Following list with real-time searches
     */
    public function following(string $username): void
    {
        $profileUser = $this->userModel->findByUsername($username);
        if (!$profileUser) {
            $this->response->setStatusCode(404);
            die("User not found.");
        }

        $search = trim($this->request->get('q', ''));
        $currentUserId = $this->authId();

        // Query following list with search filters
        $sql = "SELECT u.*
                FROM follows f
                JOIN users u ON f.followed_id = u.id
                WHERE f.follower_id = :user_id";

        $params = ['user_id' => $profileUser['id']];

        if (!empty($search)) {
            $sql .= " AND (u.username LIKE :q1 OR u.full_name LIKE :q2)";
            $params['q1'] = '%' . $search . '%';
            $params['q2'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY f.created_at DESC";
        $following = $this->userModel->fetchAll($sql, $params);

        // Map follow states
        foreach ($following as &$f) {
            $f['is_following'] = $currentUserId ? $this->userModel->isFollowing($currentUserId, (int)$f['id']) : false;
        }

        $this->view('profile.following', [
            'profileUser' => $profileUser,
            'following'   => $following,
            'search'      => $search,
            'csrf_token'  => $this->session->generateCsrfToken()
        ]);
    }

    /**
     * Renders modern, feature-rich Wallet & Earnings page with full metrics breakdown
     */
    public function wallet(): void
    {
        $userId = $this->authId();
        if (!$userId) {
            $this->redirect('/auth/login');
        }

        // Fetch Wallet record
        $wallet = $this->userModel->getWallet($userId);
        if (!$wallet) {
            // Auto initialize wallet if missing
            $this->userModel->query("INSERT IGNORE INTO wallets (user_id, balance, pending_balance) VALUES (:id, 0.00, 0.00)", ['id' => $userId]);
            $wallet = $this->userModel->getWallet($userId);
        }

        // Calculate transaction metrics
        $transactions = $this->userModel->fetchAll(
            "SELECT * FROM transactions WHERE wallet_id = :id ORDER BY created_at DESC",
            ['id' => $userId]
        );

        $totalEarnings = 0.00;
        $creatorEarnings = 0.00;
        $referralEarnings = 0.00;
        $recommendationEarnings = 0.00;

        foreach ($transactions as $t) {
            $amt = (float)$t['amount'];
            if ($amt > 0) {
                $totalEarnings += $amt;
                if ($t['type'] === 'sale') {
                    $creatorEarnings += $amt;
                } elseif ($t['type'] === 'commission') {
                    $referralEarnings += $amt;
                    $recommendationEarnings += $amt; // Direct conversion commission
                }
            }
        }

        // Fetch withdrawal settings
        $settings = $this->userModel->fetch("SELECT * FROM commission_settings WHERE id = 1");

        $minWithdrawal = (float)($settings['min_withdrawal_amount'] ?? 50.00);
        $maxWithdrawal = (float)($settings['max_withdrawal_amount'] ?? 5000.00);
        $withdrawalFee = (float)($settings['withdrawal_fee'] ?? 0.00);
        $withdrawalsEnabled = (int)($settings['withdrawals_enabled'] ?? 1);

        // Fetch withdrawals history
        $withdrawalsHistory = $this->userModel->fetchAll(
            "SELECT * FROM withdrawals WHERE user_id = :id ORDER BY created_at DESC",
            ['id' => $userId]
        );

        $this->view('profile.wallet', [
            'wallet'                 => $wallet,
            'transactions'           => $transactions,
            'totalEarnings'          => $totalEarnings,
            'creatorEarnings'        => $creatorEarnings,
            'referralEarnings'       => $referralEarnings,
            'recommendationEarnings' => $recommendationEarnings,
            'withdrawalsHistory'     => $withdrawalsHistory,
            'minWithdrawal'          => $minWithdrawal,
            'maxWithdrawal'          => $maxWithdrawal,
            'withdrawalFee'          => $withdrawalFee,
            'withdrawalsEnabled'     => $withdrawalsEnabled,
            'csrf_token'             => $this->session->generateCsrfToken()
        ]);
    }
}
