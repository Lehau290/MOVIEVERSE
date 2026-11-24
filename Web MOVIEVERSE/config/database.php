<?php
/**
 * MovieVerse - Database Configuration & PDO Factory
 * Supports MySQL (Default for XAMPP) with SQLite auto-fallback
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'movieverse');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static ?PDO $instance = null;
    private static string $driver = 'mysql';

    public static function getConnection(): PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        // Try MySQL first (Standard XAMPP setup)
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 2,
            ];
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            self::$driver = 'mysql';
            return self::$instance;
        } catch (PDOException $e) {
            // If MySQL is not running or database does not exist, try creating MySQL DB if connection to host works
            try {
                $dsnHost = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
                $tempPdo = new PDO($dsnHost, DB_USER, DB_PASS);
                $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                unset($tempPdo);

                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
                self::$driver = 'mysql';
                self::ensureSchemaAndSeed();
                return self::$instance;
            } catch (Exception $fallbackEx) {
                // If MySQL is completely unavailable (e.g., service stopped in XAMPP during preview),
                // fall back smoothly to SQLite so the application works seamlessly!
                $sqlitePath = dirname(__DIR__) . '/database/movieverse.sqlite';
                $needInit = !file_exists($sqlitePath);
                $dsn = "sqlite:" . $sqlitePath;
                self::$instance = new PDO($dsn, null, null, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                self::$driver = 'sqlite';
                if ($needInit || filesize($sqlitePath) === 0) {
                    self::ensureSchemaAndSeed();
                }
                return self::$instance;
            }
        }
    }

    public static function getDriver(): string {
        return self::$driver;
    }

    public static function ensureSchemaAndSeed(): void {
        require_once __DIR__ . '/../database/seed_data.php';
        MovieVerseSeeder::run(self::$instance, self::$driver);
    }
}
