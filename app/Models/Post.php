<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Security;
use PDO;

class Post extends Model
{
    public function create(array $data): int
    {
        // Enforce 500 character limit
        $content = mb_substr(trim($data['content']), 0, 500);

        $sql = "INSERT INTO posts (user_id, content, parent_id, quote_id, product_id, referral_code)
                VALUES (:user_id, :content, :parent_id, :quote_id, :product_id, :referral_code)";

        $params = [
            'user_id'       => $data['user_id'],
            'content'       => $content,
            'parent_id'     => $data['parent_id'] ?? null,
            'quote_id'      => $data['quote_id'] ?? null,
            'product_id'    => $data['product_id'] ?? null,
            'referral_code' => $data['referral_code'] ?? null
        ];

        $this->query($sql, $params);
        $postId = (int)$this->lastInsertId();

        // Process mentions and hashtags
        $this->processMentions($postId, $data['user_id'], $content);

        // Handle Poll if any
        if (!empty($data['poll_question']) && !empty($data['poll_options'])) {
            $this->createPoll($postId, $data['poll_question'], $data['poll_options']);
        }

        return $postId;
    }

    public function findById(int $id, ?int $currUserId = null): ?array
    {
        $sql = "SELECT p.*, u.username, u.full_name, u.avatar_url,
                       (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as likes_count,
                       (SELECT COUNT(*) FROM posts WHERE parent_id = p.id) as replies_count,
                       (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id AND user_id = :curr1) as is_liked,
                       (SELECT COUNT(*) FROM post_bookmarks WHERE post_id = p.id AND user_id = :curr2) as is_bookmarked
                FROM posts p
                JOIN users u ON p.user_id = u.id
                WHERE p.id = :id";

        $post = $this->fetch($sql, [
            'id'    => $id,
            'curr1' => $currUserId ?? 0,
            'curr2' => $currUserId ?? 0
        ]);

        if ($post) {
            // Fetch Poll info if exists
            $post['poll'] = $this->getPollForPost($id, $currUserId);
            // Fetch quoted post if exists
            if ($post['quote_id']) {
                $post['quoted_post'] = $this->findById((int)$post['quote_id'], $currUserId);
            }
            // Fetch product card info if exists
            if ($post['product_id']) {
                $post['product'] = $this->getProductCardDetails((int)$post['product_id']);
            }
        }

        return $post ?: null;
    }

    // Process Mentions & Create Notifications
    protected function processMentions(int $postId, int $senderId, string $content): void
    {
        // Find @username in content
        preg_match_all('/@([a-zA-Z0-9_]+)/', $content, $matches);
        if (!empty($matches[1])) {
            $sender = $this->fetch("SELECT username FROM users WHERE id = :id", ['id' => $senderId]);
            $senderUsername = $sender['username'] ?? 'Someone';

            $uniqueUsernames = array_unique($matches[1]);
            foreach ($uniqueUsernames as $username) {
                $targetUser = $this->fetch("SELECT id FROM users WHERE username = :username", ['username' => $username]);
                if ($targetUser && (int)$targetUser['id'] !== $senderId) {
                    $this->query(
                        "INSERT INTO notifications (user_id, type, sender_id, source_id, content)
                         VALUES (:user_id, 'mention', :sender_id, :source_id, :content)",
                        [
                            'user_id'   => $targetUser['id'],
                            'sender_id' => $senderId,
                            'source_id' => $postId,
                            'content'   => "@{$senderUsername} mentioned you in a post!"
                        ]
                    );
                }
            }
        }
    }

    // Hashtags Extraction & Active Trends
    public function getTrendingHashtags(int $limit = 5): array
    {
        // Simple and robust hashtag extraction across all posts
        $posts = $this->fetchAll("SELECT content FROM posts");
        $hashtags = [];
        foreach ($posts as $post) {
            preg_match_all('/#([a-zA-Z0-9_]+)/u', $post['content'], $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $tag) {
                    $tagLower = mb_strtolower($tag);
                    $hashtags[$tagLower] = ($hashtags[$tagLower] ?? 0) + 1;
                }
            }
        }
        arsort($hashtags);
        $trending = [];
        $count = 0;
        foreach ($hashtags as $tag => $qty) {
            if ($count >= $limit) break;
            $trending[] = ['tag' => $tag, 'count' => $qty];
            $count++;
        }
        return $trending;
    }

    // Poll Engine
    public function createPoll(int $postId, string $question, array $options): void
    {
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days')); // Default 7 days
        $this->query("INSERT INTO polls (post_id, question, expires_at) VALUES (:post_id, :question, :expires_at)", [
            'post_id'    => $postId,
            'question'   => $question,
            'expires_at' => $expiresAt
        ]);
        $pollId = (int)$this->lastInsertId();

        foreach ($options as $optionText) {
            if (empty(trim($optionText))) continue;
            $this->query("INSERT INTO poll_options (poll_id, option_text) VALUES (:poll_id, :option_text)", [
                'poll_id'     => $pollId,
                'option_text' => trim($optionText)
            ]);
        }
    }

    public function getPollForPost(int $postId, ?int $currUserId = null): ?array
    {
        $poll = $this->fetch("SELECT * FROM polls WHERE post_id = :post_id", ['post_id' => $postId]);
        if (!$poll) return null;

        $options = $this->fetchAll(
            "SELECT o.*,
                    (SELECT COUNT(*) FROM poll_votes WHERE option_id = o.id) as votes_count
             FROM poll_options o
             WHERE o.poll_id = :poll_id",
            ['poll_id' => $poll['id']]
        );

        // Sum total votes
        $totalVotes = array_sum(array_column($options, 'votes_count'));
        $poll['total_votes'] = $totalVotes;

        // Check if current user voted
        $userVoteOptionId = null;
        if ($currUserId) {
            $userVote = $this->fetch(
                "SELECT option_id FROM poll_votes WHERE poll_id = :poll_id AND user_id = :user_id",
                ['poll_id' => $poll['id'], 'user_id' => $currUserId]
            );
            if ($userVote) {
                $userVoteOptionId = (int)$userVote['option_id'];
            }
        }

        $poll['has_voted'] = ($userVoteOptionId !== null);
        $poll['user_voted_option_id'] = $userVoteOptionId;
        $poll['options'] = $options;
        $poll['is_expired'] = (time() > strtotime($poll['expires_at']));

        return $poll;
    }

    public function votePoll(int $userId, int $pollId, int $optionId): bool
    {
        // Verify poll is active
        $poll = $this->fetch("SELECT expires_at FROM polls WHERE id = :id", ['id' => $pollId]);
        if (!$poll || time() > strtotime($poll['expires_at'])) {
            return false;
        }

        // Check already voted
        $voted = $this->fetch("SELECT 1 FROM poll_votes WHERE poll_id = :poll_id AND user_id = :user_id", [
            'poll_id' => $pollId,
            'user_id' => $userId
        ]);
        if ($voted) return false;

        $this->query("INSERT INTO poll_votes (user_id, poll_id, option_id) VALUES (:user_id, :poll_id, :option_id)", [
            'user_id'   => $userId,
            'poll_id'   => $pollId,
            'option_id' => $optionId
        ]);

        return true;
    }

    // Likes & Bookmarks Helpers
    public function like(int $userId, int $postId): bool
    {
        $liked = $this->fetch("SELECT 1 FROM post_likes WHERE user_id = :user_id AND post_id = :post_id", [
            'user_id' => $userId,
            'post_id' => $postId
        ]);

        if ($liked) {
            $this->query("DELETE FROM post_likes WHERE user_id = :user_id AND post_id = :post_id", [
                'user_id' => $userId,
                'post_id' => $postId
            ]);
            return false; // Unliked
        } else {
            $this->query("INSERT INTO post_likes (user_id, post_id) VALUES (:user_id, :post_id)", [
                'user_id' => $userId,
                'post_id' => $postId
            ]);

            // Notify author if it is not self
            $post = $this->fetch("SELECT user_id FROM posts WHERE id = :id", ['id' => $postId]);
            if ($post && (int)$post['user_id'] !== $userId) {
                $username = $this->fetch("SELECT username FROM users WHERE id = :id", ['id' => $userId])['username'] ?? 'Someone';
                $this->query(
                    "INSERT INTO notifications (user_id, type, sender_id, source_id, content)
                     VALUES (:user_id, 'like', :sender_id, :source_id, :content)",
                    [
                        'user_id'   => $post['user_id'],
                        'sender_id' => $userId,
                        'source_id' => $postId,
                        'content'   => "@{$username} liked your post!"
                    ]
                );
            }
            return true; // Liked
        }
    }

    public function bookmark(int $userId, int $postId): bool
    {
        $bookmarked = $this->fetch("SELECT 1 FROM post_bookmarks WHERE user_id = :user_id AND post_id = :post_id", [
            'user_id' => $userId,
            'post_id' => $postId
        ]);

        if ($bookmarked) {
            $this->query("DELETE FROM post_bookmarks WHERE user_id = :user_id AND post_id = :post_id", [
                'user_id' => $userId,
                'post_id' => $postId
            ]);
            return false; // Unbookmarked
        } else {
            $this->query("INSERT INTO post_bookmarks (user_id, post_id) VALUES (:user_id, :post_id)", [
                'user_id' => $userId,
                'post_id' => $postId
            ]);
            return true; // Bookmarked
        }
    }

    // Feed Retrieval
    public function getFeed(?int $userId = null, string $type = 'for_you'): array
    {
        $params = [];
        $sql = "SELECT p.*, u.username, u.full_name, u.avatar_url,
                       (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as likes_count,
                       (SELECT COUNT(*) FROM posts WHERE parent_id = p.id) as replies_count,
                       (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id AND user_id = :curr1) as is_liked,
                       (SELECT COUNT(*) FROM post_bookmarks WHERE post_id = p.id AND user_id = :curr2) as is_bookmarked
                FROM posts p
                JOIN users u ON p.user_id = u.id
                WHERE p.parent_id IS NULL";

        $params['curr1'] = $userId ?? 0;
        $params['curr2'] = $userId ?? 0;

        if ($type === 'following' && $userId) {
            $sql .= " AND p.user_id IN (SELECT followed_id FROM follows WHERE follower_id = :user_id)";
            $params['user_id'] = $userId;
        }

        $sql .= " ORDER BY p.created_at DESC LIMIT 50";

        $posts = $this->fetchAll($sql, $params);

        foreach ($posts as &$post) {
            $post['poll'] = $this->getPollForPost((int)$post['id'], $userId);
            if ($post['quote_id']) {
                $post['quoted_post'] = $this->findById((int)$post['quote_id'], $userId);
            }
            if ($post['product_id']) {
                $post['product'] = $this->getProductCardDetails((int)$post['product_id']);
            }
        }

        return $posts;
    }

    public function getReplies(int $postId, ?int $currUserId = null): array
    {
        $sql = "SELECT p.*, u.username, u.full_name, u.avatar_url,
                       (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as likes_count,
                       (SELECT COUNT(*) FROM posts WHERE parent_id = p.id) as replies_count,
                       (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id AND user_id = :curr1) as is_liked,
                       (SELECT COUNT(*) FROM post_bookmarks WHERE post_id = p.id AND user_id = :curr2) as is_bookmarked
                FROM posts p
                JOIN users u ON p.user_id = u.id
                WHERE p.parent_id = :id
                ORDER BY p.created_at ASC";

        $replies = $this->fetchAll($sql, [
            'id'    => $postId,
            'curr1' => $currUserId ?? 0,
            'curr2' => $currUserId ?? 0
        ]);

        foreach ($replies as &$reply) {
            $reply['poll'] = $this->getPollForPost((int)$reply['id'], $currUserId);
            if ($reply['quote_id']) {
                $reply['quoted_post'] = $this->findById((int)$reply['quote_id'], $currUserId);
            }
            if ($reply['product_id']) {
                $reply['product'] = $this->getProductCardDetails((int)$reply['product_id']);
            }
        }

        return $replies;
    }

    /**
     * Retrieve essential details of a product card
     */
    public function getProductCardDetails(int $productId): ?array
    {
        $sql = "SELECT p.id, p.name, p.slug, p.price, p.type, u.username as creator_username, u.full_name as creator_name,
                       (SELECT AVG(rating) FROM product_reviews WHERE product_id = p.id) as avg_rating,
                       (SELECT COUNT(*) FROM orders WHERE product_id = p.id AND status = 'completed') as sales_count
                FROM products p
                JOIN users u ON p.creator_id = u.id
                WHERE p.id = :id";
        $card = $this->fetch($sql, ['id' => $productId]);
        return $card ?: null;
    }
}
