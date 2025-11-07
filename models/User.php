<?php
require_once __DIR__ . '/Database.php';

class User {
    public static function findByEmail($email) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            SELECT * 
            FROM users 
            WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC); //fetch_obj 
    }

    public static function register($name, $email, $password) {
        // Basic validation
        if (empty($email) || empty($password) || empty($name)) return false;

        // Check existing
        if (self::findByEmail($email)) return false;

        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $pdo = Database::connect();
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, created_at) VALUES (:name, :email, :password_hash, NOW())');
        return $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password_hash' => $hash
        ]);
    }

    public static function verifyCredentials($email, $password) {
        $user = self::findByEmail($email);
        if (!$user) return false;
        if (password_verify($password, $user['password_hash'])) return $user;
        return false;
    }
}
