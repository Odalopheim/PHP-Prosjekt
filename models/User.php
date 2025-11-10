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
    $pdo = Database::connect();
    $user = self::findByEmail($email);

    if (!$user) return false;

    // Sjekk om brukeren har for mange mislykkede forsøk.
    // Vær robust hvis kolonnene ikke finnes i databasen (bakoverkompatibilitet).
    $failedAttempts = isset($user['failed_attempts']) ? (int)$user['failed_attempts'] : 0;
    $lastFailed = isset($user['last_failed']) ? $user['last_failed'] : null;
    if ($failedAttempts >= 5 && $lastFailed && strtotime($lastFailed) > strtotime('-15 minutes')) {
        return false; // Midlertidig sperret
    }

    // Sjekk passord
    if (!password_verify($password, $user['password_hash'])) {
        // Oppdater mislykket forsøk hvis kolonnene finnes
        if (self::hasColumn('failed_attempts') && self::hasColumn('last_failed')) {
            $stmt = $pdo->prepare("UPDATE users SET failed_attempts = failed_attempts + 1, last_failed = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
        }
        return false;
    }

    // Nullstill mislykkede forsøk ved vellykket innlogging (hvis kolonnene finnes)
    if (self::hasColumn('failed_attempts') && self::hasColumn('last_failed')) {
        $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, last_failed = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);
    }

    return $user;
}

    /**
     * Sjekk dynamisk om en kolonne finnes i `users`-tabellen.
     * Returnerer true hvis kolonnen finnes, false ellers.
     */
    private static function hasColumn(string $column): bool {
        try {
            $pdo = Database::connect();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = :col");
            $stmt->execute([':col' => $column]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            // Ved eventuelle feil, anta at kolonnen ikke finnes for sikkerhets skyld
            return false;
        }
    }
}
