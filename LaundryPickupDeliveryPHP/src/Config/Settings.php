<?php

declare(strict_types=1);

namespace LaundryLoop\Config;

class Settings
{
    private static array $data = [];

    public static function load(string $envFile): void
    {
        // Parse .env file
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (str_starts_with(trim($line), '#')) continue;
                if (!str_contains($line, '=')) continue;
                [$key, $value] = explode('=', $line, 2);
                $key   = trim($key);
                $value = trim($value);
                if (!isset($_ENV[$key])) {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }

        self::$data = [
            'app' => [
                'env'      => self::env('APP_ENV', 'production'),
                'base_url' => self::env('APP_BASE_URL', 'http://laundryloop.atwebpages.com'),
            ],
            'db' => [
                'host' => self::env('DB_HOST', 'fdb1006.awardspace.net'),
                'port' => (int) self::env('DB_PORT', '3306'),
                'name' => self::env('DB_NAME', '1459861_laundryloop'),
                'user' => self::env('DB_USER', '1459861_laundryloop'),
                'pass' => self::env('DB_PASS', 'xPAJ/dh38djSE^hB'),
            ],
            'auth' => [
                'username' => self::env('ADMIN_USERNAME', 'admin'),
                'password' => self::env('ADMIN_PASSWORD', 'changeme'),
            ],
            'users' => [
                [
                    'username' => 'admin',
                    'password' => 'admin123',
                    'role'     => 'admin',
                ],
                [
                    'username' => 'driver',
                    'password' => 'driver123',
                    'role'     => 'driver',
                ],
            ],
        ];
    }

    /**
     * Verify username/password against the hardcoded users list.
     * Returns the user array (without password) on success, or null on failure.
     */
    public static function authenticate(string $username, string $password): ?array
    {
        $users = self::get('users') ?? [];
        foreach ($users as $user) {
            if ($user['username'] === $username && hash_equals($user['password'], $password)) {
                return ['username' => $user['username'], 'role' => $user['role']];
            }
        }
        return null;
    }

    public static function get(string $key): mixed
    {
        $parts = explode('.', $key);
        $value = self::$data;
        foreach ($parts as $part) {
            if (!isset($value[$part])) return null;
            $value = $value[$part];
        }
        return $value;
    }

    private static function env(string $key, string $default = ''): string
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}
