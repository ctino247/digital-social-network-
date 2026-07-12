<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Post;

class HomeController extends Controller
{
    protected Post $postModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->postModel = new Post();
    }

    public function index(): void
    {
        $userId = $this->authId();
        $feedType = $this->request->get('feed', 'for_you');

        if ($feedType === 'following' && !$userId) {
            $this->redirect('/auth/login');
        }

        $posts = $this->postModel->getFeed($userId, $feedType);
        $trendingHashtags = $this->postModel->getTrendingHashtags(5);

        // Fetch announcements
        $announcements = $this->postModel->fetchAll("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3");

        $this->view('home.index', [
            'posts'            => $posts,
            'trendingHashtags' => $trendingHashtags,
            'feedType'         => $feedType,
            'announcements'    => $announcements,
            'csrf_token'       => $this->session->generateCsrfToken()
        ]);
    }
}
