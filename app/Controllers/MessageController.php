<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use PDO;

class MessageController extends Controller
{
    protected User $userModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->userModel = new User();
    }

    /**
     * Lists active conversation threads for the user
     */
    public function index(): void
    {
        $userId = $this->authId();

        // Get list of unique users the authenticated user has chatted with
        $conversations = $this->userModel->fetchAll(
            "SELECT DISTINCT u.id, u.username, u.full_name, u.avatar_url,
                    (SELECT content FROM messages
                     WHERE (sender_id = :u1 AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = :u2)
                     ORDER BY created_at DESC LIMIT 1) as last_message,
                    (SELECT created_at FROM messages
                     WHERE (sender_id = :u3 AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = :u4)
                     ORDER BY created_at DESC LIMIT 1) as last_message_time,
                    (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = :u5 AND is_read = 0) as unread_count
             FROM messages m
             JOIN users u ON (m.sender_id = u.id AND m.receiver_id = :u6) OR (m.receiver_id = u.id AND m.sender_id = :u7)
             WHERE u.id != :u8
             ORDER BY last_message_time DESC",
            [
                'u1' => $userId, 'u2' => $userId,
                'u3' => $userId, 'u4' => $userId,
                'u5' => $userId, 'u6' => $userId,
                'u7' => $userId, 'u8' => $userId
            ]
        );

        $this->view('messages.index', [
            'conversations' => $conversations,
            'csrf_token'    => $this->session->generateCsrfToken()
        ]);
    }

    /**
     * Renders search page to start a new chat thread
     */
    public function new(): void
    {
        $search = trim($this->request->get('q', ''));
        $users = [];

        if (!empty($search)) {
            $users = $this->userModel->fetchAll(
                "SELECT * FROM users WHERE (username LIKE :q1 OR full_name LIKE :q2) AND id != :id ORDER BY username ASC",
                [
                    'q1' => '%' . $search . '%',
                    'q2' => '%' . $search . '%',
                    'id' => $this->authId()
                ]
            );
        }

        $this->view('messages.new', [
            'users'      => $users,
            'search'     => $search,
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }

    /**
     * Renders dedicated chat conversation thread
     */
    public function chat(string $username): void
    {
        $targetUser = $this->userModel->findByUsername($username);
        if (!$targetUser) {
            $this->response->setStatusCode(404);
            die("User not found.");
        }

        $userId = $this->authId();

        // Mark incoming messages as read
        $this->userModel->query(
            "UPDATE messages SET is_read = 1 WHERE sender_id = :sender AND receiver_id = :receiver",
            ['sender' => $targetUser['id'], 'receiver' => $userId]
        );

        // Fetch thread messages
        $chatMessages = $this->userModel->fetchAll(
            "SELECT * FROM messages
             WHERE (sender_id = :s1 AND receiver_id = :r1) OR (sender_id = :s2 AND receiver_id = :r2)
             ORDER BY created_at ASC",
            [
                's1' => $userId, 'r1' => $targetUser['id'],
                's2' => $targetUser['id'], 'r2' => $userId
            ]
        );

        $this->view('messages.chat', [
            'targetUser'   => $targetUser,
            'chatMessages' => $chatMessages,
            'csrf_token'   => $this->session->generateCsrfToken()
        ]);
    }

    /**
     * Sends a direct private message via POST
     */
    public function send(): void
    {
        $this->validateCsrf();
        $userId = $this->authId();

        $receiverId = (int)$this->request->get('receiver_id', 0);
        $content = trim($this->request->get('content', ''));

        $targetUser = $this->userModel->findById($receiverId);
        if (!$targetUser) {
            die("Receiver not found.");
        }

        if (!empty($content)) {
            $this->userModel->query(
                "INSERT INTO messages (sender_id, receiver_id, content) VALUES (:sender, :receiver, :content)",
                [
                    'sender'   => $userId,
                    'receiver' => $receiverId,
                    'content'  => $content
                ]
            );

            // Notify user of new DM
            $senderUsername = $this->authUser()['username'];
            $this->userModel->query(
                "INSERT INTO notifications (user_id, type, sender_id, content)
                 VALUES (:user_id, 'message', :sender_id, :content)",
                [
                    'user_id'   => $receiverId,
                    'sender_id' => $userId,
                    'content'   => "@{$senderUsername} sent you a private message!"
                ]
            );
        }

        $this->redirect('/messages/' . $targetUser['username']);
    }

    /**
     * API to query new messages dynamically
     */
    public function getUpdates(string $username): void
    {
        $targetUser = $this->userModel->findByUsername($username);
        if (!$targetUser) {
            $this->json(['success' => false, 'error' => 'User not found'], 404);
        }

        $userId = $this->authId();

        // Fetch unread messages from target user
        $unread = $this->userModel->fetchAll(
            "SELECT * FROM messages WHERE sender_id = :sender AND receiver_id = :receiver AND is_read = 0 ORDER BY created_at ASC",
            ['sender' => $targetUser['id'], 'receiver' => $userId]
        );

        // Mark as read
        if (!empty($unread)) {
            $this->userModel->query(
                "UPDATE messages SET is_read = 1 WHERE sender_id = :sender AND receiver_id = :receiver AND is_read = 0",
                ['sender' => $targetUser['id'], 'receiver' => $userId]
            );
        }

        $this->json([
            'success'  => true,
            'messages' => $unread
        ]);
    }
}
