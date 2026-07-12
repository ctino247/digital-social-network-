<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Post;

class ExploreController extends Controller
{
    protected Post $postModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->postModel = new Post();
    }

    public function index(): void
    {
        $search = trim($this->request->get('q', ''));
        $categorySlug = trim($this->request->get('category', ''));

        $posts = [];
        $products = [];

        if (!empty($search)) {
            // Search Posts
            $posts = $this->postModel->fetchAll(
                "SELECT p.*, u.username, u.full_name, u.avatar_url,
                        (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as likes_count,
                        (SELECT COUNT(*) FROM posts WHERE parent_id = p.id) as replies_count,
                        (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id AND user_id = :curr1) as is_liked,
                        (SELECT COUNT(*) FROM post_bookmarks WHERE post_id = p.id AND user_id = :curr2) as is_bookmarked
                 FROM posts p
                 JOIN users u ON p.user_id = u.id
                 WHERE p.content LIKE :search AND p.parent_id IS NULL
                 ORDER BY p.created_at DESC LIMIT 20",
                [
                    'curr1'  => $this->authId() ?? 0,
                    'curr2'  => $this->authId() ?? 0,
                    'search' => '%' . $search . '%'
                ]
            );

            // Search active Products
            $products = $this->postModel->fetchAll(
                "SELECT p.*, c.name as category_name, u.username as creator_username
                 FROM products p
                 JOIN categories c ON p.category_id = c.id
                 JOIN users u ON p.creator_id = u.id
                 WHERE (p.name LIKE :s1 OR p.description LIKE :s2) AND p.status = 'active'
                 ORDER BY p.created_at DESC",
                [
                    's1' => '%' . $search . '%',
                    's2' => '%' . $search . '%'
                ]
            );
        } else {
            // Default feeds
            $posts = $this->postModel->fetchAll(
                "SELECT p.*, u.username, u.full_name, u.avatar_url,
                        (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as likes_count,
                        (SELECT COUNT(*) FROM posts WHERE parent_id = p.id) as replies_count,
                        (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id AND user_id = :curr1) as is_liked,
                        (SELECT COUNT(*) FROM post_bookmarks WHERE post_id = p.id AND user_id = :curr2) as is_bookmarked
                 FROM posts p
                 JOIN users u ON p.user_id = u.id
                 WHERE p.parent_id IS NULL
                 ORDER BY likes_count DESC, p.created_at DESC LIMIT 10",
                [
                    'curr1' => $this->authId() ?? 0,
                    'curr2' => $this->authId() ?? 0
                ]
            );

            $products = $this->postModel->fetchAll(
                "SELECT p.*, c.name as category_name, u.username as creator_username
                 FROM products p
                 JOIN categories c ON p.category_id = c.id
                 JOIN users u ON p.creator_id = u.id
                 WHERE p.status = 'active' AND p.is_featured = 1
                 ORDER BY p.created_at DESC LIMIT 6"
            );
        }

        // Fetch trending hashtags
        $trendingHashtags = $this->postModel->getTrendingHashtags(6);

        // Fetch recommended creators
        $creators = $this->postModel->fetchAll(
            "SELECT u.id, u.username, u.full_name, u.avatar_url, u.bio,
                    (SELECT COUNT(*) FROM follows WHERE followed_id = u.id) as followers_count
             FROM users u
             WHERE u.role IN ('creator', 'admin') AND u.id != :curr
             ORDER BY followers_count DESC LIMIT 5",
            ['curr' => $this->authId() ?? 0]
        );

        $this->view('explore.index', [
            'search'           => $search,
            'posts'            => $posts,
            'products'         => $products,
            'trendingHashtags' => $trendingHashtags,
            'creators'         => $creators,
            'csrf_token'       => $this->session->generateCsrfToken()
        ]);
    }
}
