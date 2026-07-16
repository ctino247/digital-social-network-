<?php

namespace App\Core;

class ErrorHandler
{
    public static function register(): void
    {
        error_reporting(E_ALL);
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }

    public static function handleError(int $level, string $message, string $file, int $line): void
    {
        throw new \ErrorException($message, 0, $level, $file, $line);
    }

    public static function handleException(\Throwable $exception): void
    {
        $code = $exception->getCode() ?: 500;
        if (!headers_sent()) {
            http_response_code(is_numeric($code) && $code >= 100 && $code < 600 ? $code : 500);
        }

        // Log the exception
        error_log($exception->getMessage() . "\n" . $exception->getTraceAsString());

        // Display a clean screen
        echo "<!DOCTYPE html><html class='dark'><head><title>System Error</title><script src='https://cdn.tailwindcss.com'></script></head>";
        echo "<body class='bg-[#131315] text-[#e4e2e4] flex flex-col justify-center items-center h-screen font-sans px-4'>";
        echo "<div class='max-w-2xl text-center'>";
        echo "<h1 class='text-6xl font-bold text-[#ffb4ab]'>Oops!</h1>";
        echo "<p class='text-2xl mt-4 font-semibold text-[#e4e2e4]'>Something went wrong on our end.</p>";
        echo "<p class='text-md mt-2 text-[#908fa0]'>A system error has occurred. Our engineers have been notified and are looking into it.</p>";

        // Detailed message only if we are on localhost or debugging
        if (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false || strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false) {
            echo "<div class='mt-6 p-4 bg-[#1b1b1d] rounded-lg text-left text-xs font-mono border border-[#464554] overflow-auto max-h-60'>";
            echo "<strong>Exception:</strong> " . get_class($exception) . "<br>";
            echo "<strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "<br>";
            echo "<strong>File:</strong> " . $exception->getFile() . " (Line: " . $exception->getLine() . ")<br><br>";
            echo "<strong>Stack Trace:</strong><br>" . nl2br(htmlspecialchars($exception->getTraceAsString()));
            echo "</div>";
        }

        echo "<a href='/' class='mt-8 inline-block px-6 py-3 bg-[#c0c1ff] text-[#1000a9] font-semibold rounded-full hover:opacity-90 transition'>Go Back Home</a>";
        echo "</div>";
        echo "</body></html>";
        exit;
    }
}
