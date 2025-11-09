<?php
session_start();
// beregn base-URL: bruk definert BASE_URL fra front controller hvis tilgjengelig
$base = defined('BASE_URL') ? BASE_URL : '/php/PHP-Prosjekt/public';

// Hvis ikke innlogget: send til login-side via front controller
if (empty($_SESSION['user_id'])) {
  header('Location: ' . $base . '/index.php?page=login');
  exit;
}

include_once __DIR__ . '/header.php';

// require ChatBot fra controllers-mappen (én nivå opp fra views)
require_once __DIR__ . '/../controllers/ChatBot.php';
$bot = new ChatBot();

$input = $_GET['sted'] ?? '';
$response = $input ? $bot->respond($input) : "Hei! Skriv inn et sted, så forteller jeg deg været der.";
$conversations = Conversation::getAllMessages();
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

    <form method="get">
      <input type="text" name="sted" placeholder="Skriv inn sted..." autofocus>
      <button>Send</button>
    </form>
  </div>
  <script src="<?= \htmlspecialchars($base) ?>/js/script.js"></script>
<?php
// inkluder footer som har closing tags
include_once __DIR__ . '/footer.php';

?>
