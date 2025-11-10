<?php
// bruk __DIR__ for å sikre at inkludering er relativ til denne filens mappe
require_once __DIR__ . '/Database.php';

class Conversation
{
    /**
     * Lagre en samtale (brukerinput + botens svar)
     * Hvis `user_email`-kolonnen finnes i databasen, vil den også lagres.
     */
    public static function saveMessage($userInput, $botResponse)
    {
        // Input validation: ensure neither input is empty
        if (empty($userInput) || empty($botResponse)) {
            // Optionally, you could throw an exception or log this event
            return false;
        }

        try {
            $db = Database::connect();

            // Hvis `conversations`-tabellen har en user_email-kolonne, inkluder den
            if (self::hasColumn('user_email')) {
                $stmt = $db->prepare("INSERT INTO conversations (user_input, bot_response, user_email) VALUES (:user_input, :bot_response, :user_email)");
                $stmt->execute([
                    ':user_input' => $userInput,
                    ':bot_response' => $botResponse,
                    // Vi bruker en global variabel som settes av kallet som ønsker å lagre epost
                    ':user_email' => $GLOBALS['__conversation_user_email'] ?? null
                ]);
            } else {
                $stmt = $db->prepare("INSERT INTO conversations (user_input, bot_response) VALUES (:user_input, :bot_response)");
                $stmt->execute([
                    ':user_input' => $userInput,
                    ':bot_response' => $botResponse
                ]);
            }
            return true;
        } catch (Exception $e) {
            // Optionally, log the error message: error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Hent alle tidligere samtaler (nyeste først)
     */
    public static function getAllMessages()
    {
        $db = Database::connect();

        // Hvis user_email finnes i skjemaet, hent den også
        if (self::hasColumn('user_email')) {
            $sql = "SELECT user_input, bot_response, user_email, created_at FROM conversations ORDER BY created_at DESC";
        } else {
            $sql = "SELECT user_input, bot_response, NULL AS user_email, created_at FROM conversations ORDER BY created_at DESC";
        }
        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tøm hele samtaleloggen
     */
    public static function clearAll()
    {
        $db = Database::connect();
        $db->exec("DELETE FROM conversations");
    }

    // Sjekk om en kolonne finnes i conversations-tabellen
    private static function hasColumn(string $column): bool {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'conversations' AND COLUMN_NAME = :col");
            $stmt->execute([':col' => $column]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}

