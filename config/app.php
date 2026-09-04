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

        // On live web host (e.g. biswas-enterprise.in), HTTP_HOST takes precedence over local .env APP_URL
        if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== 'localhost' && $_SERVER['HTTP_HOST'] !== '127.0.0.1') {
            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' && $_SERVER['HTTPS'] !== '0') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
            $scriptDir = str_replace('\\', '/', $scriptDir);
            if (strpos($scriptDir, '/public') !== false) {
                $scriptDir = preg_replace('/\/public$/', '', $scriptDir);
            }
            if ($scriptDir === '/' || $scriptDir === '.') {
                $scriptDir = '';
            }
            $baseUrl = rtrim($scheme . '://' . $host . $scriptDir, '/');
        } elseif (!empty($appUrl) && strpos($appUrl, 'localhost') === false) {
            $baseUrl = rtrim($appUrl, '/');
        } else {
            if (isset($_SERVER['HTTP_HOST'])) {
                $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' && $_SERVER['HTTPS'] !== '0') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
                $scriptDir = str_replace('\\', '/', $scriptDir);
                if (strpos($scriptDir, '/public') !== false) {
                    $scriptDir = preg_replace('/\/public$/', '', $scriptDir);
                }
                if ($scriptDir === '/' || $scriptDir === '.') {
                    $scriptDir = '';
                }
                $baseUrl = rtrim($scheme . '://' . $host . $scriptDir, '/');
            } else {
                $baseUrl = 'http://localhost/Client-Ecommerce-Project';
            }
        }

        return $baseUrl . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }

    function asset(string $path): string {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $requestUri = str_replace('\\', '/', $_SERVER['REQUEST_URI'] ?? '');
        $relativePath = ltrim($path, '/');

        // Check if public/ prefix is needed
        if (strpos($scriptName, '/public/') !== false || strpos($requestUri, '/public/') !== false) {
            $fullUrl = url('public/assets/' . $relativePath);
            $filePath = __DIR__ . '/../public/assets/' . $relativePath;
        } else {
            $fullUrl = url('assets/' . $relativePath);
            $filePath = __DIR__ . '/../public/assets/' . $relativePath;
        }

        // Automatic cache busting for CSS and JS assets
        $v = file_exists($filePath) ? filemtime($filePath) : '1.2';
        $separator = strpos($fullUrl, '?') === false ? '?' : '&';
        return $fullUrl . $separator . 'v=' . $v;
    }
}

// Load the .env file from the project root
loadEnv(dirname(__DIR__) . '/.env');

// Set default timezone to Indian Standard Time (IST)
date_default_timezone_set(env('APP_TIMEZONE', 'Asia/Kolkata'));

// Load Cloudinary image helper
require_once __DIR__ . '/cloudinary.php';
