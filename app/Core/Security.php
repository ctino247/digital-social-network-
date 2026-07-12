<?php

namespace App\Core;

class Security
{
    // Sanitize output for XSS prevention
    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    // Short helper alias
    public static function e(string $value): string
    {
        return self::escape($value);
    }

    // Hash password with bcrypt
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    // Verify password against hash
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    // Simple IP/Session Rate Limiting
    public static function checkRateLimit(string $action, int $maxRequests = 10, int $timeWindowSeconds = 60): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $sessionKey = "rate_limit_{$action}";
        $now = time();
        $history = $_SESSION[$sessionKey] ?? [];

        // Filter out times older than the window
        $history = array_filter($history, function ($timestamp) use ($now, $timeWindowSeconds) {
            return ($now - $timestamp) < $timeWindowSeconds;
        });

        if (count($history) >= $maxRequests) {
            return false;
        }

        $history[] = $now;
        $_SESSION[$sessionKey] = $history;
        return true;
    }
}
