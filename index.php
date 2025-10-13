<?php
session_start();

// Hvis ikke innlogget: send til login-side
if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

require_once 'controllers/ChatBot.php';
$bot = new ChatBot();

$input = $_GET['sted'] ?? '';
$response = $input ? $bot->respond($input) : "Hei 👋! Skriv inn et sted, så forteller jeg deg været der.";
$conversations = Conversation::getAllMessages();
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="UTF-8">
  <title>Værassistent</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
  
  <div class="chat">
    <h1>🌤️ Værassistent</h1>
      <div class="messages">
       
  <!-- Knapp for å vise/skjule tidligere samtaler -->
  <button id="showPrev" type="button" onclick="toggleMessages()">📜 Vis tidligere samtaler</button>

  <!-- Tidligere samtaler ligger skjult til knappen trykkes -->
  <div id="previous" style="display:none; margin-top: 10px;">
    <?php foreach ($conversations as $conv): ?>
      <div class="msg user"><?= htmlspecialchars($conv['user_input']) ?></div>
      <div class="msg bot"><?= htmlspecialchars($conv['bot_response']) ?></div>
    <?php endforeach; ?>
  </div>

  <!-- Nåværende melding -->
  <?php if ($input): ?>
    <div class="msg user"><?= htmlspecialchars($input) ?></div>
    <div class="msg bot"><?= htmlspecialchars($response) ?></div>
  <?php else: ?>
    <div class="msg bot"><?= htmlspecialchars($response) ?></div>
  <?php endif; ?>
</div>

    <form method="get">
      <input type="text" name="sted" placeholder="Skriv inn sted..." autofocus>
      <button>Send</button>
    </form>
  </div>
   <script src="public/js/script.js"></script>
</body>
</html>
