<?php
class Database {
    //egen database mappe 
    private static $host = 'localhost';
    private static $db   = 'chatbot';
    private static $user = 'root';
    private static $pass = '';
    private static $charset = 'utf8mb4';

    public static function connect() {
        $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=" . self::$charset; //driver
        try {
            //$pdo = new PDO($dsn, self::$user, self::$pass);
            $pdo = new PDO("mysql:host=localhost;port=3307;dbname=chatbot", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage()); //ikke gjøre dette, ikke vise bruker feilmelding, sikkerhet
        }
    }
}
