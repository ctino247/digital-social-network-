<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Product extends Model
{
    public function getCategories(): array
    {
        return $this->fetchAll("SELECT * FROM categories ORDER BY name ASC");
    }

    public function getCategoryBySlug(string $slug): ?array
    {
        $cat = $this->fetch("SELECT * FROM categories WHERE slug = :slug", ['slug' => $slug]);
        return $cat ?: null;
    }

    public function getAllActive(?int $categoryId = null, ?string $search = null): array
    {
        $sql = "SELECT p.*, c.name as category_name, u.username as creator_username, u.full_name as creator_name,
                       (SELECT AVG(rating) FROM product_reviews WHERE product_id = p.id) as avg_rating,
                       (SELECT COUNT(*) FROM product_reviews WHERE product_id = p.id) as reviews_count
                FROM products p
                JOIN categories c ON p.category_id = c.id
                JOIN users u ON p.creator_id = u.id
                WHERE p.status = 'active'";

        $params = [];

        if ($categoryId) {
            $sql .= " AND p.category_id = :category_id";
            $params['category_id'] = $categoryId;
        }

        if (!empty($search)) {
            $sql .= " AND (p.name LIKE :search1 OR p.description LIKE :search2)";
            $params['search1'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY p.created_at DESC";

        return $this->fetchAll($sql, $params);
    }

    public function findBySlug(string $slug): ?array
    {
        $sql = "SELECT p.*, c.name as category_name, u.username as creator_username, u.full_name as creator_name, u.avatar_url as creator_avatar,
                       (SELECT AVG(rating) FROM product_reviews WHERE product_id = p.id) as avg_rating,
                       (SELECT COUNT(*) FROM product_reviews WHERE product_id = p.id) as reviews_count
                FROM products p
                JOIN categories c ON p.category_id = c.id
                JOIN users u ON p.creator_id = u.id
                WHERE p.slug = :slug";

        $product = $this->fetch($sql, ['slug' => $slug]);
        return $product ?: null;
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT p.*, c.name as category_name, u.username as creator_username, u.full_name as creator_name, u.avatar_url as creator_avatar
                FROM products p
                JOIN categories c ON p.category_id = c.id
                JOIN users u ON p.creator_id = u.id
                WHERE p.id = :id";

        $product = $this->fetch($sql, ['id' => $id]);
        return $product ?: null;
    }

    public function getFeatured(int $limit = 6): array
    {
        return $this->fetchAll(
            "SELECT p.*, c.name as category_name, u.username as creator_username,
                    (SELECT AVG(rating) FROM product_reviews WHERE product_id = p.id) as avg_rating
             FROM products p
             JOIN categories c ON p.category_id = c.id
             JOIN users u ON p.creator_id = u.id
             WHERE p.status = 'active' AND p.is_featured = 1
             ORDER BY p.created_at DESC LIMIT :limit",
            ['limit' => $limit]
        );
    }

    public function getTrending(int $limit = 6): array
    {
        // Simple trending algorithm: most orders in the system
        return $this->fetchAll(
            "SELECT p.*, c.name as category_name, u.username as creator_username,
                    (SELECT COUNT(*) FROM orders WHERE product_id = p.id) as sales_count,
                    (SELECT AVG(rating) FROM product_reviews WHERE product_id = p.id) as avg_rating
             FROM products p
             JOIN categories c ON p.category_id = c.id
             JOIN users u ON p.creator_id = u.id
             WHERE p.status = 'active'
             ORDER BY sales_count DESC, p.created_at DESC LIMIT :limit",
            ['limit' => $limit]
        );
    }

    public function addReview(int $userId, int $productId, int $rating, string $text): void
    {
        $this->query(
            "INSERT INTO product_reviews (user_id, product_id, rating, review_text)
             VALUES (:user_id, :product_id, :rating, :text)
             ON DUPLICATE KEY UPDATE rating = :rating2, review_text = :text2",
            [
                'user_id'    => $userId,
                'product_id' => $productId,
                'rating'     => $rating,
                'text'       => trim($text),
                'rating2'    => $rating,
                'text2'      => trim($text)
            ]
        );
    }

    public function getReviews(int $productId): array
    {
        return $this->fetchAll(
            "SELECT r.*, u.username, u.full_name, u.avatar_url
             FROM product_reviews r
             JOIN users u ON r.user_id = u.id
             WHERE r.product_id = :product_id
             ORDER BY r.created_at DESC",
            ['product_id' => $productId]
        );
    }

    // Check if user purchased product to allow downloads and reviews
    public function hasPurchased(int $userId, int $productId): bool
    {
        $order = $this->fetch(
            "SELECT 1 FROM orders WHERE user_id = :user_id AND product_id = :product_id AND status = 'completed'",
            ['user_id' => $userId, 'product_id' => $productId]
        );
        return !empty($order);
    }

    /**
     * Log a recommendation tracking event
     */
    public function trackRecommendationEvent(int $referralLinkId, string $eventType): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $this->query(
            "INSERT INTO recommendation_events (referral_link_id, event_type, ip_address, user_agent)
             VALUES (:link_id, :event_type, :ip, :ua)",
            [
                'link_id'    => $referralLinkId,
                'event_type' => $eventType,
                'ip'         => $ip,
                'ua'         => $ua
            ]
        );
    }
}
