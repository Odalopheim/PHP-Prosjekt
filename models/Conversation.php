<?php
require_once 'models/Database.php';

class Conversation
{
    /**
     * 💾 Lagre en samtale (brukerinput + botens svar)
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

            $stmt = $db->prepare("
                INSERT INTO conversations (user_input, bot_response)
                VALUES (:user_input, :bot_response)
            ");

            $stmt->execute([
                ':user_input' => $userInput,
                ':bot_response' => $botResponse
            ]);
            return true;
        } catch (Exception $e) {
            // Optionally, log the error message: error_log($e->getMessage());
            return false;
        }
    }

    /**
     * 📜 Hent alle tidligere samtaler (nyeste først)
     */
    public static function getAllMessages()
    {
        $db = Database::connect();

        $stmt = $db->query("
            SELECT user_input, bot_response, created_at
            FROM conversations
            ORDER BY created_at DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 🧹 (Valgfritt) Tøm hele samtaleloggen
     */
    public static function clearAll()
    {
        $db = Database::connect();
        $db->exec("DELETE FROM conversations");
    }
}
