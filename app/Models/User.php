<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Security;
use PDO;

class User extends Model
{
    public function create(array $data): int
    {
        $sql = "INSERT INTO users (username, email, password_hash, full_name, bio, avatar_url, role, is_verified, referred_by)
                VALUES (:username, :email, :password_hash, :full_name, :bio, :avatar_url, :role, :is_verified, :referred_by)";

        $params = [
            'username'      => $data['username'],
            'email'         => $data['email'],
            'password_hash' => Security::hashPassword($data['password']),
            'full_name'     => $data['full_name'],
            'bio'           => $data['bio'] ?? null,
            'avatar_url'    => $data['avatar_url'] ?? null,
            'role'          => $data['role'] ?? 'member',
            'is_verified'   => $data['is_verified'] ?? 0,
            'referred_by'   => $data['referred_by'] ?? null
        ];

        $this->query($sql, $params);
        $userId = (int)$this->lastInsertId();

        // Initialize wallet for new user
        $this->query("INSERT INTO wallets (user_id, balance, pending_balance) VALUES (:user_id, 0.00, 0.00)", [
            'user_id' => $userId
        ]);

        return $userId;
    }

    public function findById(int $id): ?array
    {
        $user = $this->fetch("SELECT * FROM users WHERE id = :id", ['id' => $id]);
        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $user = $this->fetch("SELECT * FROM users WHERE email = :email", ['email' => $email]);
        return $user ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $user = $this->fetch("SELECT * FROM users WHERE username = :username", ['username' => $username]);
        return $user ?: null;
    }

    public function updateRememberToken(int $id, ?string $token): void
    {
        $this->query("UPDATE users SET remember_token = :token WHERE id = :id", [
            'id'    => $id,
            'token' => $token
        ]);
    }

    public function verifyEmail(int $id): void
    {
        $this->query("UPDATE users SET is_verified = 1 WHERE id = :id", ['id' => $id]);
    }

    public function updateProfile(int $id, array $data): void
    {
        $allowedKeys = [
            'full_name', 'bio', 'avatar_url', 'cover_url', 'role', 'is_sales_partner',
            'website', 'occupation', 'country', 'social_links', 'email_preferences',
            'notification_settings', 'privacy_settings'
        ];

        $sets = [];
        $params = ['id' => $id];

        foreach ($data as $key => $val) {
            if (in_array($key, $allowedKeys)) {
                $sets[] = "{$key} = :{$key}";
                $params[$key] = $val;
            }
        }

        if (empty($sets)) return;

        $sql = "UPDATE users SET " . implode(", ", $sets) . " WHERE id = :id";
        $this->query($sql, $params);
    }

    // Follower / Following Graph Mechanics
    public function isFollowing(int $followerId, int $followedId): bool
    {
        $res = $this->fetch("SELECT 1 FROM follows WHERE follower_id = :follower_id AND followed_id = :followed_id", [
            'follower_id' => $followerId,
            'followed_id' => $followedId
        ]);
        return !empty($res);
    }

    public function follow(int $followerId, int $followedId): void
    {
        if ($followerId === $followedId) return;
        $this->query("INSERT IGNORE INTO follows (follower_id, followed_id) VALUES (:follower_id, :followed_id)", [
            'follower_id' => $followerId,
            'followed_id' => $followedId
        ]);
    }

    public function unfollow(int $followerId, int $followedId): void
    {
        $this->query("DELETE FROM follows WHERE follower_id = :follower_id AND followed_id = :followed_id", [
            'follower_id' => $followerId,
            'followed_id' => $followedId
        ]);
    }

    public function getFollowersCount(int $userId): int
    {
        $res = $this->fetch("SELECT COUNT(*) as count FROM follows WHERE followed_id = :user_id", ['user_id' => $userId]);
        return (int)($res['count'] ?? 0);
    }

    public function getFollowingCount(int $userId): int
    {
        $res = $this->fetch("SELECT COUNT(*) as count FROM follows WHERE follower_id = :user_id", ['user_id' => $userId]);
        return (int)($res['count'] ?? 0);
    }

    public function getFollowers(int $userId): array
    {
        return $this->fetchAll(
            "SELECT u.id, u.username, u.full_name, u.avatar_url, u.bio
             FROM follows f
             JOIN users u ON f.follower_id = u.id
             WHERE f.followed_id = :user_id",
            ['user_id' => $userId]
        );
    }

    public function getFollowing(int $userId): array
    {
        return $this->fetchAll(
            "SELECT u.id, u.username, u.full_name, u.avatar_url, u.bio
             FROM follows f
             JOIN users u ON f.followed_id = u.id
             WHERE f.follower_id = :user_id",
            ['user_id' => $userId]
        );
    }

    // Wallet Integration Helpers
    public function getWallet(int $userId): ?array
    {
        $wallet = $this->fetch("SELECT * FROM wallets WHERE user_id = :user_id", ['user_id' => $userId]);
        return $wallet ?: null;
    }
}
