<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Post;

class PostController extends Controller
{
    protected Post $postModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->postModel = new Post();
    }

    public function create(): void
    {
        $quoteId = $this->request->get('quote_id', null);
        $quotedPost = null;
        if ($quoteId) {
            $quotedPost = $this->postModel->findById((int)$quoteId, $this->authId());
        }

        $this->view('post.create', [
            'quotedPost' => $quotedPost,
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }

    public function store(): void
    {
        $this->validateCsrf();
        $userId = $this->authId();
        if (!$userId) {
            $this->redirect('/auth/login');
        }

        $content = trim($this->request->get('content', ''));
        $quoteId = $this->request->get('quote_id', null);

        // Poll variables
        $pollQuestion = trim($this->request->get('poll_question', ''));
        $pollOptions = $this->request->get('poll_options', []);

        if (empty($content) && empty($pollQuestion)) {
            $this->session->setFlash('error', 'Post content or poll question cannot be empty.');
            $this->redirect('/post/create' . ($quoteId ? '?quote_id=' . $quoteId : ''));
        }

        if (mb_strlen($content) > 500) {
            $this->session->setFlash('error', 'Post content cannot exceed 500 characters.');
            $this->redirect('/post/create' . ($quoteId ? '?quote_id=' . $quoteId : ''));
        }

        // Setup payload
        $data = [
            'user_id'   => $userId,
            'content'   => $content,
            'parent_id' => null,
            'quote_id'  => $quoteId ?: null
        ];

        // Include poll parameters if complete
        if (!empty($pollQuestion) && count(array_filter($pollOptions)) >= 2) {
            $data['poll_question'] = $pollQuestion;
            $data['poll_options'] = array_filter($pollOptions);
        }

        $postId = $this->postModel->create($data);

        $this->session->setFlash('success', 'Your post was successfully published!');
        $this->redirect('/');
    }

    public function show(int $id): void
    {
        $userId = $this->authId();
        $post = $this->postModel->findById($id, $userId);
        if (!$post) {
            $this->response->setStatusCode(404);
            die("Post not found.");
        }

        $replies = $this->postModel->getReplies($id, $userId);

        $this->view('post.show', [
            'post'       => $post,
            'replies'    => $replies,
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }

    public function reply(int $id): void
    {
        $this->validateCsrf();
        $userId = $this->authId();
        if (!$userId) {
            $this->redirect('/auth/login');
        }

        $content = trim($this->request->get('content', ''));
        if (empty($content)) {
            $this->session->setFlash('error', 'Reply content cannot be empty.');
            $this->redirect("/post/{$id}");
        }

        if (mb_strlen($content) > 500) {
            $this->session->setFlash('error', 'Reply content cannot exceed 500 characters.');
            $this->redirect("/post/{$id}");
        }

        // Store reply
        $this->postModel->create([
            'user_id'   => $userId,
            'content'   => $content,
            'parent_id' => $id,
            'quote_id'  => null
        ]);

        // Send Notification to parent post owner
        $parentPost = $this->postModel->findById($id);
        if ($parentPost && (int)$parentPost['user_id'] !== $userId) {
            $username = $this->authUser()['username'];
            $this->postModel->query(
                "INSERT INTO notifications (user_id, type, sender_id, source_id, content)
                 VALUES (:user_id, 'reply', :sender_id, :source_id, :content)",
                [
                    'user_id'   => $parentPost['user_id'],
                    'sender_id' => $userId,
                    'source_id' => $id,
                    'content'   => "@{$username} replied to your thread!"
                ]
            );
        }

        $this->session->setFlash('success', 'Your reply has been posted.');
        $this->redirect("/post/{$id}");
    }

    public function like(int $id): void
    {
        $this->validateCsrf();
        $userId = $this->authId();
        if (!$userId) {
            $this->json(['success' => false, 'error' => 'Login required.'], 401);
        }

        $isLiked = $this->postModel->like($userId, $id);
        $likesCount = $this->postModel->fetch("SELECT COUNT(*) as count FROM post_likes WHERE post_id = :id", ['id' => $id])['count'] ?? 0;

        $this->json([
            'success'    => true,
            'action'     => $isLiked ? 'liked' : 'unliked',
            'likesCount' => $likesCount
        ]);
    }

    public function bookmark(int $id): void
    {
        $this->validateCsrf();
        $userId = $this->authId();
        if (!$userId) {
            $this->json(['success' => false, 'error' => 'Login required.'], 401);
        }

        $isBookmarked = $this->postModel->bookmark($userId, $id);

        $this->json([
            'success' => true,
            'action'  => $isBookmarked ? 'bookmarked' : 'unbookmarked'
        ]);
    }

    public function votePoll(): void
    {
        $this->validateCsrf();
        $userId = $this->authId();
        if (!$userId) {
            $this->json(['success' => false, 'error' => 'Login required.'], 401);
        }

        $pollId = (int)$this->request->get('poll_id', 0);
        $optionId = (int)$this->request->get('option_id', 0);

        if (!$pollId || !$optionId) {
            $this->json(['success' => false, 'error' => 'Invalid choice.'], 400);
        }

        $success = $this->postModel->votePoll($userId, $pollId, $optionId);

        if ($success) {
            // Recalculate options percentages
            $options = $this->postModel->fetchAll(
                "SELECT id, option_text, (SELECT COUNT(*) FROM poll_votes WHERE option_id = o.id) as votes
                 FROM poll_options o WHERE poll_id = :poll_id",
                ['poll_id' => $pollId]
            );
            $total = array_sum(array_column($options, 'votes'));

            $this->json([
                'success' => true,
                'total'   => $total,
                'options' => $options
            ]);
        } else {
            $this->json(['success' => false, 'error' => 'Could not submit vote. Make sure the poll is active and you have not voted.'], 400);
        }
    }
}
