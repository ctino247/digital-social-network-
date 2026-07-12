<?php

namespace App\Core;

class Controller
{
    public Request $request;
    public Response $response;
    public Session $session;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->session = new Session();
    }

    public function view(string $view, array $data = []): void
    {
        // Extract variables to view context
        extract($data);

        // Path to the view file
        $viewFile = ROOT_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            die("View [{$view}] not found at {$viewFile}");
        }

        // Render the view file
        include_once $viewFile;
    }

    public function redirect(string $url): void
    {
        $this->response->redirect($url);
    }

    public function json(array $data, int $statusCode = 200): void
    {
        $this->response->json($data, $statusCode);
    }

    // CSRF Protection Checks
    protected function validateCsrf(): void
    {
        if ($this->request->isPost()) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!$this->session->validateCsrfToken($token)) {
                $this->response->setStatusCode(403);
                die("CSRF verification failed.");
            }
        }
    }

    // Auth Helpers
    protected function authUser(): ?array
    {
        return $this->session->get('user');
    }

    protected function isLoggedIn(): bool
    {
        return $this->session->has('user');
    }

    protected function authId(): ?int
    {
        $user = $this->authUser();
        return $user ? (int)$user['id'] : null;
    }
}
