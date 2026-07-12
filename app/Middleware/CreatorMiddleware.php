<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CreatorMiddleware extends Middleware
{
    public function execute(Request $request, Response $response): void
    {
        $session = new Session();
        $user = $session->get('user');

        if (!$user || !in_array($user['role'], ['creator', 'admin'])) {
            $session->setFlash('error', 'You must be an approved Creator to view the Creator Dashboard.');
            $response->redirect('/profile/' . ($user['username'] ?? ''));
        }
    }
}
