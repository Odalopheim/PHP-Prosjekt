<?php
require_once __DIR__ . '/Database.php';

class User
{
    /**
     * Finn en bruker basert på e-post.
     */
    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            SELECT * 
            FROM users 
            WHERE email = :email 
            LIMIT 1
        ");
        $stmt->execute([':email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Registrer en ny bruker.
     */
    public static function register(string $name, string $email, string $password, string $role): bool
    {
        if (empty($name) || empty($email) || empty($password)) {
            return false;
        }

        // Sjekk om e-post allerede finnes
        if (self::findByEmail($email)) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password_hash, role, created_at) 
            VALUES (:name, :email, :password_hash, :role, NOW())
        ");

        return $stmt->execute([
            ':name'          => $name,
            ':email'         => $email,
            ':password_hash' => $hash,
            ':role'          => $role
        ]);
    }

    /**
     * Verifiser innloggingsinformasjon.
     */
    public static function verifyCredentials(string $email, string $password): false|array|string
    {
        $pdo  = Database::connect();
        $user = self::findByEmail($email);

        if (!$user) {
            return false;
        }

        // Sjekk mislykkede forsøk 
        $failedAttempts = isset($user['failed_attempts']) ? (int)$user['failed_attempts'] : 0;
        $lastFailed     = $user['last_failed'] ?? null;

        if ($failedAttempts >= 3 && $lastFailed && strtotime($lastFailed) > strtotime('-15 minutes')) {
            return 'locked'; 
        }

        // Sjekk passord
        if (!password_verify($password, $user['password_hash'])) {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET failed_attempts = failed_attempts + 1, last_failed = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$user['id']]);
        
        return false;
        }

        // Nullstill mislykkede forsøk ved vellykket innlogging
        $stmt = $pdo->prepare("
            UPDATE users 
            SET failed_attempts = 0, last_failed = NULL 
            WHERE id = ?
        ");
        $stmt->execute([$user['id']]);
        
        return $user;
    }
}