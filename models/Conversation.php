<?php
require_once __DIR__ . '/Database.php';

class Conversation
{
    /**
     * Lagre samtalen
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

            $stmt = $db->prepare(
                "INSERT INTO conversations (user_input, bot_response, user_email) 
                VALUES (:user_input, :bot_response, :user_email)");
            $stmt->execute([
                ':user_input' => $userInput,
                ':bot_response' => $botResponse,
                ':user_email' => $_SESSION['user_email'] ?? null
                ]);
    
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Hent alle samtaler. Må være admin.
     */
    public static function getAllMessages(): array
    {
        $db = Database::connect();

        $isAdmin = $_SESSION['is_admin'] ?? false;

        if ($isAdmin) {
        $sql = "SELECT user_input, bot_response, user_email, created_at
                FROM conversations
                ORDER BY created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}   
    /**
     * Viser egen historie til bruker
     */
    public static function getMessagesForUserByEmail(): array {

        $db = Database::connect();

        $userEmail = $_SESSION['user_email'] ?? null;
        
        if (!$userEmail) {
            return [];
        }

        if ($userEmail) {
              $sql = "SELECT user_input, bot_response, user_email, created_at 
                FROM conversations 
                WHERE user_email = :user_email 
                ORDER BY created_at DESC";

        } else {
            return [];  
        }
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user_email', $userEmail);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}