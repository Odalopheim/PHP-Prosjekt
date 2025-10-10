<?php
require_once 'models/Database.php';

class Conversation
{
    /**
     * 💾 Lagre en samtale (brukerinput + botens svar)
     */
    public static function saveMessage($userInput, $botResponse)
    {
        $db = Database::connect();

        $stmt = $db->prepare("
            INSERT INTO conversations (user_input, bot_response)
            VALUES (:user_input, :bot_response)
        ");

        $stmt->execute([
            ':user_input' => $userInput,
            ':bot_response' => $botResponse
        ]);
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
