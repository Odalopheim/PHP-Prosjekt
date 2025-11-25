<?php
// Bruk __DIR__ for å sikre at inkludering er relativ til denne filens mappe
require_once __DIR__ . '/Database.php';

class Conversation
{
    /**
     * Lagre en samtale (brukerinput + botens svar).
     * Hvis `user_email`-kolonnen finnes i databasen, lagres den også.
     */
    public static function saveMessage(string $userInput, string $botResponse): bool
    {
        // Valider input
        if (empty($userInput) || empty($botResponse)) {
            return false;
        }

        try {
            $db = Database::connect();

            // Bygg dynamisk INSERT basert på hvilke kolonner som finnes
            $columns = ['user_input', 'bot_response'];
            $placeholders = [':user_input', ':bot_response'];
            $params = [':user_input' => $userInput, ':bot_response' => $botResponse];

            if (self::hasColumn('user_id')) {
                $columns[] = 'user_id';
                $placeholders[] = ':user_id';
                $params[':user_id'] = $GLOBALS['__conversation_user_id'] ?? null;
            }

            if (self::hasColumn('user_email')) {
                $columns[] = 'user_email';
                $placeholders[] = ':user_email';
                $params[':user_email'] = $GLOBALS['__conversation_user_email'] ?? null;
            }

            $sql = sprintf(
                "INSERT INTO conversations (%s) VALUES (%s)",
                implode(', ', $columns),
                implode(', ', $placeholders)
            );

            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            return true;
        } catch (Exception $e) {
            // Du kan logge feilen her: error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Hent alle samtaler (nyeste først). Brukes av admin-visninger.
     */
    public static function getAllMessages(): array
    {
        $db = Database::connect();

        $cols = 'user_input, bot_response, created_at';
        if (self::hasColumn('user_email')) $cols .= ', user_email';
        if (self::hasColumn('user_id')) $cols .= ', user_id';

        $stmt = $db->prepare("SELECT $cols FROM conversations ORDER BY created_at DESC");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Hent meldinger for en gitt bruker-ID (bruker visning av historikk)
     */
    public static function getMessagesForUserById(int $userId): array
    {
        $db = Database::connect();

        // Hvis tabellen har user_id, filtrer på den
        if (self::hasColumn('user_id')) {
            $stmt = $db->prepare("SELECT user_input, bot_response, created_at, user_id, user_email FROM conversations WHERE user_id = :uid ORDER BY created_at DESC");
            $stmt->execute([':uid' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Fallback: hvis user_email finnes og ligger i session, bruk epost
        if (self::hasColumn('user_email')) {
            $email = $_SESSION['user_email'] ?? null;
            if ($email) {
                $stmt = $db->prepare("SELECT user_input, bot_response, created_at, user_email FROM conversations WHERE user_email = :email ORDER BY created_at DESC");
                $stmt->execute([':email' => $email]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        return [];
    }

    /**
     * Tøm hele samtaleloggen.
     */
    public static function clearAll(): void
    {
        $db = Database::connect();
        $db->exec("DELETE FROM conversations");
    }

    /**
     * Sjekk om en kolonne finnes i `conversations`-tabellen.
     */
    private static function hasColumn(string $column): bool
    {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT COUNT(*) 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'conversations' 
                  AND COLUMN_NAME = :col
            ");
            $stmt->execute([':col' => $column]);

            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}