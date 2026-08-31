<?php
require_once __DIR__ . '/app.php';

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $primaryHost = env('DB_HOST', '193.203.184.157');
            $db          = env('DB_NAME', 'u410000684_ecommerce');
            $user        = env('DB_USER', 'u410000684_admin');
            $pass        = env('DB_PASSWORD', 'Admin@#2026');
            $port        = env('DB_PORT', '3306');
            $charset     = 'utf8mb4';

            // Candidates list with timeout and fallback support
            $hostsToTry = array_unique([
                $primaryHost,
                '193.203.184.157',
                'srv1671.hstgr.io'
            ]);

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 4, // 4-second connection timeout
            ];

            $lastException = null;

            // 1. Try Remote Hostinger DB candidates
            foreach ($hostsToTry as $host) {
                if (empty($host)) continue;
                try {
                    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
                    self::$instance = new PDO($dsn, $user, $pass, $options);
                    return self::$instance;
                } catch (\PDOException $e) {
                    $lastException = $e;
                }
            }

            // 2. Fallback to Local XAMPP MySQL if remote database is unreachable
            try {
                $localDsn = "mysql:host=localhost;port=3306;charset=$charset";
                $localPdo = new PDO($localDsn, 'root', '', $options);
                $localPdo->exec("CREATE DATABASE IF NOT EXISTS `u410000684_ecommerce`");
                $localPdo->exec("USE `u410000684_ecommerce`");
                self::$instance = $localPdo;
                return self::$instance;
            } catch (\PDOException $localErr) {
                // If local also fails, throw primary exception
                throw $lastException ?? $localErr;
            }
        }

        return self::$instance;
    }
}
