<?php
class Database {
    // Databasekonfigurasjon
    private static $host = 'localhost';
    private static $db   = 'chatbot';
    private static $user = 'root';
    private static $pass = '';
    private static $charset = 'utf8mb4';

    /**
     * Oppretter og returnerer en PDO-tilkobling til databasen.
     * Bruker try/catch for å håndtere eventuelle tilkoblingsfeil på en sikker måte.
     * 
     * @return PDO  Returnerer et PDO-objekt ved vellykket tilkobling.
     */
    public static function connect() {
        // Data Source Name (DSN) – spesifiserer database-driver, vert og navn
        $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=" . self::$charset;

        try {
            //for de som bruker annen port enn standard 3306
            //$pdo = new PDO($dsn, self::$user, self::$pass);
            //for de som bruker port 3307
            $pdo = new PDO("mysql:host=localhost;port=3307;dbname=chatbot", "root", "");

            // Setter feilhåndtering til å kaste unntak
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;
        } catch (PDOException $e) {
            // Unngå å vise detaljerte feilmeldinger til brukere av sikkerhetsårsaker
            die('Database connection failed.');
        }
    }
}
