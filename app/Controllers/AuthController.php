<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Core\Security;

class AuthController extends Controller
{
    protected User $userModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->userModel = new User();
    }

    public function register(): void
    {
        $this->view('auth.register', [
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }

    public function handleRegister(): void
    {
        $this->validateCsrf();
        $username = trim($this->request->get('username', ''));
        $email = trim($this->request->get('email', ''));
        $password = $this->request->get('password', '');
        $fullName = trim($this->request->get('full_name', ''));

        $errors = [];

        if (strlen($username) < 3 || strlen($username) > 30 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username must be 3-30 characters and contain only letters, numbers, or underscores.';
        } elseif ($this->userModel->findByUsername($username)) {
            $errors[] = 'Username is already taken.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } elseif ($this->userModel->findByEmail($email)) {
            $errors[] = 'Email is already registered.';
        }

        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }

        if (empty($fullName)) {
            $errors[] = 'Full Name is required.';
        }

        if (!empty($errors)) {
            $this->session->setFlash('errors', implode('<br>', $errors));
            $this->redirect('/auth/register');
        }

        // Get referrer from session for Smart Referral Landing
        $referredByUserId = $this->session->get('referred_by_user_id', null);

        // Create member. Auto-verify to make testing smooth in sandboxed environment.
        $userId = $this->userModel->create([
            'username'    => $username,
            'email'       => $email,
            'password'    => $password,
            'full_name'   => $fullName,
            'role'        => 'member',
            'is_verified' => 1, // Auto verified
            'referred_by' => $referredByUserId
        ]);

        $createdUser = $this->userModel->findById($userId);
        $this->session->set('user', $createdUser);

        // Trigger automated welcome mail
        \App\Services\Mailer::sendWelcomeEmail($email, $fullName, $username);

        // Smart Referral Landing Redirect Logic
        $redirectUrl = '/';
        if ($referredByUserId) {
            $referrer = $this->userModel->findById((int)$referredByUserId);
            if ($referrer) {
                // If referrer is Creator or Admin, redirect to products tab
                if (in_array($referrer['role'], ['creator', 'admin'])) {
                    $redirectUrl = "/profile/" . $referrer['username'] . "?tab=products";
                } else {
                    // Otherwise they are only Sales Partner, redirect to recommendations tab
                    $redirectUrl = "/profile/" . $referrer['username'] . "?tab=recommendations";
                }

                // Clear session reference
                $this->session->remove('referred_by_user_id');
            }
        }

        $this->session->setFlash('success', 'Registration successful! Welcome to Mimshack.');
        $this->redirect($redirectUrl);
    }

    public function login(): void
    {
        $this->view('auth.login', [
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }

    public function handleLogin(): void
    {
        $this->validateCsrf();
        $loginInput = trim($this->request->get('login', ''));
        $password = $this->request->get('password', '');
        $remember = $this->request->get('remember', null);

        $user = null;
        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            $user = $this->userModel->findByEmail($loginInput);
        } else {
            $user = $this->userModel->findByUsername($loginInput);
        }

        if (!$user || !Security::verifyPassword($password, $user['password_hash'])) {
            $this->session->setFlash('error', 'Invalid username/email or password.');
            $this->redirect('/auth/login');
        }

        // Set session
        $this->session->set('user', $user);

        // Handle "Remember Me"
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $this->userModel->updateRememberToken($user['id'], $token);
            setcookie('remember_token', $token, time() + (86400 * 30), "/", "", false, true);
        }

        $this->session->setFlash('success', "Welcome back, {$user['full_name']}!");

        // Redirect to admin if role is admin
        if ($user['role'] === 'admin') {
            $this->redirect('/admin/dashboard');
        } else {
            $this->redirect('/');
        }
    }

    public function logout(): void
    {
        $userId = $this->authId();
        if ($userId) {
            $this->userModel->updateRememberToken($userId, null);
        }
        setcookie('remember_token', '', time() - 3600, "/");
        $this->session->destroy();

        // Start fresh session to set flash message
        $newSession = new \App\Core\Session();
        $newSession->setFlash('success', 'Logged out successfully.');
        $this->redirect('/auth/login');
    }

    public function forgotPassword(): void
    {
        $this->view('auth.forgot', [
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }

    public function handleForgotPassword(): void
    {
        $this->validateCsrf();
        $email = trim($this->request->get('email', ''));
        $user = $this->userModel->findByEmail($email);

        if ($user) {
            // Simulated reset token
            $this->session->setFlash('success', "Password reset link sent! (In production this would email, for demo: use link: /auth/reset-password?email=" . urlencode($email) . ")");
        } else {
            $this->session->setFlash('error', 'No account found with that email address.');
        }
        $this->redirect('/auth/forgot-password');
    }

    public function resetPassword(): void
    {
        $email = $this->request->get('email', '');
        $this->view('auth.reset', [
            'email'      => $email,
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }

    public function handleResetPassword(): void
    {
        $this->validateCsrf();
        $email = trim($this->request->get('email', ''));
        $password = $this->request->get('password', '');

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            $this->session->setFlash('error', 'Invalid request.');
            $this->redirect('/auth/login');
        }

        if (strlen($password) < 6) {
            $this->session->setFlash('error', 'Password must be at least 6 characters.');
            $this->redirect('/auth/reset-password?email=' . urlencode($email));
        }

        $this->userModel->updateProfile($user['id'], [
            'full_name' => $user['full_name'],
            'bio'       => $user['bio']
        ]);

        // Specifically set the new password hash
        $newHash = Security::hashPassword($password);
        $this->userModel->query("UPDATE users SET password_hash = :hash WHERE id = :id", [
            'id'   => $user['id'],
            'hash' => $newHash
        ]);

        $this->session->setFlash('success', 'Your password has been reset successfully. You can now login.');
        $this->redirect('/auth/login');
    }
}
