<?php
require_once __DIR__ . '/../services/ChatBotService.php';
require_once __DIR__ . '/../models/Conversation.php';

class ChatController {

    private ChatBotService $chatService;

    public function __construct() {
        $this->chatService = new ChatBotService();
    }

    /**
     * Håndterer brukerinput og returnerer både input og response
     */
    public function handleRequest(?string $input): array {
       
        $input = trim($input ?? '');

        if ($input === '') {
            $response = "Hei! Skriv inn et sted, så forteller jeg deg været der.";
            return ['input' => '', 'response' => $response];
        }

        // Kall ChatBotService for svar
        $response = $this->chatService->respond($input);

        // Lagre melding med brukerens e-post
        $userEmail = $_SESSION['user_email'] ?? null;
        if ($userEmail) {
            Conversation::saveMessage($input, $response);
        }

        return ['input' => $input, 'response' => $response];
    }
}
