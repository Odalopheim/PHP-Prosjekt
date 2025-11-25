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
    public static function register(string $name, string $email, string $password, enum $role): bool
    {
        // Enkel validering
        if (empty($name) || empty($email) || empty($password)) {
            return false;
        }

        // Sjekk om e-post allerede finnes
        if (self::findByEmail($email)) {
            return false;
        }

        // Hash passord
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Sett inn ny bruker
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password_hash, created_at) 
            VALUES (:name, :email, :password_hash, NOW())
        ");

        return $stmt->execute([
            ':name'          => $name,
            ':email'         => $email,
            ':password_hash' => $hash
        ]);
    }

    /**
     * Verifiser innloggingsinformasjon.
     */
    public static function verifyCredentials(string $email, string $password): false|array
    {
        $pdo  = Database::connect();
        $user = self::findByEmail($email);

        if (!$user) {
            return false;
        }

        // Sjekk mislykkede forsøk (hvis kolonnene finnes)
        $failedAttempts = isset($user['failed_attempts']) ? (int)$user['failed_attempts'] : 0;
        $lastFailed     = $user['last_failed'] ?? null;

        if ($failedAttempts >= 5 && $lastFailed && strtotime($lastFailed) > strtotime('-15 minutes')) {
            return false; // Midlertidig sperret
        }

        // Sjekk passord
        if (!password_verify($password, $user['password_hash'])) {
            if (self::hasColumn('failed_attempts') && self::hasColumn('last_failed')) {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET failed_attempts = failed_attempts + 1, last_failed = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$user['id']]);
            }
            return false;
        }

        // Nullstill mislykkede forsøk ved vellykket innlogging
        if (self::hasColumn('failed_attempts') && self::hasColumn('last_failed')) {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET failed_attempts = 0, last_failed = NULL 
                WHERE id = ?
            ");
            $stmt->execute([$user['id']]);
        }

        return $user;
    }

    /**
     * Sjekk dynamisk om en kolonne finnes i `users`-tabellen.
     */
    private static function hasColumn(string $column): bool
    {
        try {
            $pdo = Database::connect();
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'users' 
                AND COLUMN_NAME = :col");
            $stmt->execute([':col' => $column]);

            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            // Ved feil, anta at kolonnen ikke finnes
            return false;
        }
    }
}