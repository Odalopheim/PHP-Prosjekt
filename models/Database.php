<?php
class Database {
    private static $host = 'localhost';
    private static $db   = 'chatbot';   // databasenavnet du laget i phpMyAdmin
    private static $user = 'root';      // standard XAMPP-bruker
    private static $pass = '';          // passord: tomt som standard i XAMPP
    private static $charset = 'utf8mb4';

    public static function connect() {
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
