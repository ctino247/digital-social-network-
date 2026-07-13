<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class NotificationController extends Controller
{
    protected User $userModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->userModel = new User();
    }

    /**
     * Lists notifications received by the logged in user
     */
    public function index(): void
    {
        $userId = $this->authId();

        $notifications = $this->userModel->fetchAll(
            "SELECT n.*, u.username as sender_username, u.avatar_url as sender_avatar
             FROM notifications n
             LEFT JOIN users u ON n.sender_id = u.id
             WHERE n.user_id = :user_id
             ORDER BY n.created_at DESC LIMIT 100",
            ['user_id' => $userId]
        );

        $this->view('notifications.index', [
            'notifications' => $notifications,
            'csrf_token'    => $this->session->generateCsrfToken()
        ]);
    }

    /**
     * Marks all user notifications as read via POST
     */
    public function readAll(): void
    {
        $this->validateCsrf();
        $userId = $this->authId();

        $this->userModel->query(
            "UPDATE notifications SET is_read = 1 WHERE user_id = :user_id",
            ['user_id' => $userId]
        );

        $this->session->setFlash('success', 'All notifications marked as read.');
        $this->redirect('/notifications');
    }
}
