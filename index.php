<?php
require_once 'controllers/ChatBot.php';
$bot = new ChatBot();

$input = $_GET['sted'] ?? '';
$response = $input ? $bot->respond($input) : "Hei 👋! Skriv inn et sted, så forteller jeg deg været der.";
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
</body>
</html>
