<?php
// Start session safely and set cookie params only if session not started yet
if (session_status() === PHP_SESSION_NONE) {
  session_set_cookie_params([
    'httponly' => true,
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Strict'
  ]);
  session_start();
} else {
  // session already active
  // ensure session is started
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
}

// CSRF setup
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$base = defined('BASE_URL') ? BASE_URL : '/php/PHP-Prosjekt/public';

if (empty($_SESSION['user_id'])) {
  header('Location: ' . $base . '/index.php?page=login');
  exit;
}

include_once __DIR__ . '/header.php';
require_once __DIR__ . '/../controllers/ChatBot.php';
require_once __DIR__ . '/../models/Conversation.php';

$bot = new ChatBot();
$input = '';

//Hent noen tidligere meldinger for visning i chatten (admins ser alle)
if (!empty($_SESSION['is_admin'])) {
  $conversations = Conversation::getAllMessages();
} else {
  $uid = $_SESSION['user_email'] ?? null;
  if ($uid) {
    $conversations = Conversation::getMessagesForUserById($uid);
  } else {
    $conversations = [];
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF verification
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token");
    }

    // Sanitize input
    $input = filter_input(INPUT_POST, 'sted', FILTER_SANITIZE_SPECIAL_CHARS);

}

$response = $input ? $bot->respond($input) : "Hei! Skriv inn et sted, så forteller jeg deg været der.";

?>

  
  <div class="chat">
    <h1>Værassistent</h1>
      <div class="messages">
       
  <!-- Knapp som går til historikk-siden -->
  <a id="showPrev" class="button" href="<?= htmlspecialchars($base) ?>/index.php?page=history">Vis tidligere samtaler</a>

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

    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <input type="text" name="sted" placeholder="Skriv inn sted..." autofocus>
      <button>Send</button>
    </form>
  </div>
  <script src="<?= \htmlspecialchars($base) ?>/js/script.js"></script>
<?php
// inkluder footer som har closing tags
include_once __DIR__ . '/footer.php';

?>
