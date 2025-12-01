<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF token
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// Sjekk innlogging
if (empty($_SESSION['user_id'])) {
    header('Location: ' . $base . '/index.php?page=login');
    exit;
}

require_once __DIR__ . '/../controllers/ChatBot.php';
require_once __DIR__ . '/header.php';

$controller = new ChatController();

$input = $_POST['sted'] ?? null;

$result = $controller->handleRequest($input);

$input = $result['input'];
$response = $result['response'];
?>

<div class="chat">
  <h1>Værassistent</h1>
  <div class="messages">
    <a id="showPrev" class="button" href="<?= htmlspecialchars($base) ?>/index.php?page=history">Vis tidligere samtaler</a>

    <?php if ($input): ?>
      <div class="msg user"><?= htmlspecialchars($input) ?></div>
      <div class="msg bot"><?= htmlspecialchars($response) ?></div>
    <?php else: ?>
      <div class="msg bot"><?= htmlspecialchars($response) ?></div>
    <?php endif; ?>
  </div>

  <form method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
    <input type="text" name="sted" placeholder="Skriv inn sted..." autofocus>
    <button>Send</button>
  </form>
</div>
