<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Simple Environment Loader
 */

if (!function_exists('env')) {
    function loadEnv(string $path): void {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }

    function env(string $key, mixed $default = null): mixed {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        return $value;
    }

    function url(string $path = ''): string {
        $appUrl = env('APP_URL');

        if (empty($appUrl) || $appUrl === 'http://localhost/ecommerce') {
            if (isset($_SERVER['HTTP_HOST'])) {
                $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
                $scriptDir = preg_replace('/\/public$/', '', str_replace('\\', '/', $scriptDir));
                $baseUrl = rtrim($scheme . '://' . $host . $scriptDir, '/');
            } else {
                $baseUrl = 'http://localhost/Client-Ecommerce-Project';
            }
        } else {
            $baseUrl = rtrim($appUrl, '/');
        }

        return $baseUrl . '/' . ltrim($path, '/');
    }

    function asset(string $path): string {
        return url('public/assets/' . ltrim($path, '/'));
    }
}

// Load the .env file from the project root
loadEnv(dirname(__DIR__) . '/.env');

// Load Cloudinary image helper
require_once __DIR__ . '/cloudinary.php';
