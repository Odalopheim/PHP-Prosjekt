<?php
require_once __DIR__ . '/../config.php';

class Database {
    private static $charset = 'utf8mb4';

    /*
    *   Hent verdier fra miljøvariabler
    */
    public static function connect() {
        $host = DB_HOST;
        $port = DB_PORT;
        $db = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASS;
        
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=" . self::$charset;

        try {
            $pdo = new PDO($dsn, $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die('Database connection failed.');
        }
    }
}
?>