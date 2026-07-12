<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AdminMiddleware extends Middleware
{
    public function execute(Request $request, Response $response): void
    {
        $session = new Session();
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'admin') {
            $session->setFlash('error', 'Unauthorized access to Administration panel.');
            $response->redirect('/');
        }
    }
}
