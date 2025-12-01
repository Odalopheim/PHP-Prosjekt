<?php
require_once __DIR__ . '/../services/ChatBotService.php';

class ChatController {
    public function handleRequest(string $input) {
        $chatService = new ChatBotService();
        $response = $chatService->respond($input);
        include __DIR__ . '/../views/chatView.php';
    }
}