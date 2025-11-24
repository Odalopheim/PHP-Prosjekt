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

            // Sjekk om tabellen har user_email-kolonnen
            if (self::hasColumn('user_email')) {
                $stmt = $db->prepare("
                    INSERT INTO conversations (user_input, bot_response, user_email) 
                    VALUES (:user_input, :bot_response, :user_email)
                ");
                $stmt->execute([
                    ':user_input'  => $userInput,
                    ':bot_response'=> $botResponse,
                    ':user_email'  => $GLOBALS['__conversation_user_email'] ?? null
                ]);
            }

            return true;
        } catch (Exception $e) {
            // Du kan logge feilen her: error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Hent alle tidligere samtaler for innlogget bruker (nyeste først).
     */
    public static function getAllMessages(): array
    {
        $db = Database::connect();

        // Hent e-post fra session
        $userEmail = $_SESSION['user_email'] ?? null;
        if (!$userEmail) {
            return []; // Ingen bruker logget inn
        }

        // Sjekk om tabellen har user_email-kolonnen
        if (!self::hasColumn('user_email')) {
            return [];
        }

        $stmt = $db->prepare("
            SELECT user_input, bot_response, user_email, created_at
            FROM conversations
            WHERE user_email = :user_email
            ORDER BY created_at DESC
        ");
        $stmt->bindParam(':user_email', $userEmail);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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