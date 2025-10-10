<?php
class Database {
    // Load database credentials from environment variables for security.
    private static $host = null;
    private static $db   = null;
    private static $user = null;
    private static $pass = null;
    private static $charset = 'utf8mb4';

    public static function connect() {
        // Initialize credentials from environment variables if not already set
        if (self::$host === null) self::$host = getenv('DB_HOST');
        if (self::$db === null) self::$db = getenv('DB_NAME');
        if (self::$user === null) self::$user = getenv('DB_USER');
        if (self::$pass === null) self::$pass = getenv('DB_PASS');
        $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=" . self::$charset;
        try {
            $pdo = new PDO($dsn, self::$user, self::$pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            throw;
        }
    }
}
