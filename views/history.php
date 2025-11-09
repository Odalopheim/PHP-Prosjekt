<?php
// Vis tidligere samtaler
// Sjekk session og hent meldinger fra modellen før output
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$base = defined('BASE_URL') ? BASE_URL : rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

// Hvis ikke innlogget: send til login-side via front controller
if (empty($_SESSION['user_id'])) {
    header('Location: ' . $base . '/index.php?page=login');
    exit;
}

require_once __DIR__ . '/../models/Conversation.php';
$conversations = Conversation::getAllMessages();
?>
<?php include_once __DIR__ . '/header.php'; ?>

  <main style="width:100%;max-width:720px;margin:1rem auto;padding:0 1rem;box-sizing:border-box;">
    <h1>Tidligere samtaler</h1>
    <?php if (empty($conversations)): ?>
      <p>Ingen tidligere samtaler funnet.</p>
    <?php else: ?>
      <div class="messages-list">
        <?php foreach ($conversations as $conv): ?>
          <div class="history-item" style="margin-bottom:1rem;padding:0.8rem;background:#2e2f36;border-radius:8px;">
            <div style="font-size:0.85rem;color:#bdbdc3;margin-bottom:0.5rem;"><?= htmlspecialchars(date('Y-m-d H:i', strtotime($conv['created_at'] ?? 'now'))) ?></div>
            <div style="margin-bottom:0.5rem;"><strong>Spørsmål:</strong>
              <div class="msg user" style="display:inline-block;margin-top:0.3rem;"><?= htmlspecialchars($conv['user_input']) ?></div>
            </div>
            <div><strong>Svar:</strong>
              <div class="msg bot" style="display:inline-block;margin-top:0.3rem;"><?= htmlspecialchars($conv['bot_response']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

<?php include_once __DIR__ . '/footer.php'; ?>
