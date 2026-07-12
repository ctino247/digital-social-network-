<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware extends Middleware
{
    public function execute(Request $request, Response $response): void
    {
        $session = new Session();
        if (!$session->has('user')) {
            $session->setFlash('error', 'Please login to access this page.');
            $response->redirect('/auth/login');
        }
    }
}
