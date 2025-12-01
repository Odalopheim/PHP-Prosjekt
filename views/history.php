<?php
// Vis tidligere samtaler
// Sjekk session og hent meldinger fra modellen før output
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


// Hvis ikke innlogget: send til login-side via front controller
if (empty($_SESSION['user_id'])) {
    header('Location: ' . $base . '/index.php?page=login');
    exit;
}

require_once __DIR__ . '/../controllers/ChatBot.php';
$conversations = Conversation::getMessagesForUserByEmail();

require_once __DIR__ . '/header.php';
?>

  <main class="main-history">
  <h1>Dine tidligere samtaler</h1>
  <?php if (empty($conversations)): ?>
    <p>Ingen tidligere samtaler funnet.</p>
  <?php else: ?>
    <div class="messages-list">
      <?php foreach ($conversations as $conv): ?>
        <div class="history-box">
          <div class="history-date"><?= htmlspecialchars(date('Y-m-d H:i', strtotime($conv['created_at'] ?? 'now'))) ?></div>
          <div class="history-question">
            <strong>Spørsmål:</strong>
            <div class="msg-history"><?= htmlspecialchars($conv['user_input']) ?></div>
          </div>
          <div>
            <strong>Svar:</strong>
            <div class="user-history"><?= htmlspecialchars($conv['bot_response']) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php include_once __DIR__ . '/footer.php'; ?>
