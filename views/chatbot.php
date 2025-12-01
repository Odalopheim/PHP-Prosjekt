<?php
// Sjekk om session er startet, hvis ikke start
if (session_status() === PHP_SESSION_NONE) {
  session_set_cookie_params([
    'httponly' => true,
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Strict'
  ]);
  session_start();
}

// CSRF token
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

if (empty($_SESSION['user_id'])) {
  header('Location: ' . $base . '/index.php?page=login');
  exit;
}

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../controllers/ChatBot.php';

$bot = new ChatBotService();
$input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF verification
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        die("Invalid CSRF token");
    }

    // Renser input
    $input = filter_input(INPUT_POST, 'sted', FILTER_SANITIZE_SPECIAL_CHARS);
}

$response = $input ? $bot->respond($input) : "Hei! Skriv inn et sted, så forteller jeg deg været der.";

?>
  <div class="chat">
    <h1>Værassistent</h1>
      <div class="messages">
        <!-- Knapp som går til historikk-siden -->
        <a id="showPrev" class="button" href="<?= htmlspecialchars($base) ?>/index.php?page=history">Vis tidligere samtaler</a>

        <!-- Nåværende melding -->
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
<?php
include_once __DIR__ . '/footer.php';
?>
