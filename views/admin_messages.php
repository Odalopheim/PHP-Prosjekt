<?php
// Enkel admin-side for å se alle meldinger
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Sjekk admin
if (empty($_SESSION['is_admin'])) {
    // Ikke autorisert
    http_response_code(403);
    echo "<h2>Ikke autorisert</h2><p>Du må være administrator for å se denne siden.</p>";
    exit;
}

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../models/Conversation.php';

$messages = Conversation::getAllMessages();
?>
<div class="container">
  <h2>Meldinger mottatt</h2>
  <p>Totalt: <?= count($messages) ?> meldinger</p>
   <table class="admin-table">
    <thead>
      <tr>
        <th>Tid</th>
        <th>E-post</th>
        <th>Brukermelding</th>
        <th>Bot</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($messages as $m): ?>
      <tr>
        <td><?= htmlspecialchars($m['created_at'] ?? '') ?></td>
        <td><?= htmlspecialchars($m['user_email'] ?? '—') ?></td>
        <td><?= nl2br(htmlspecialchars($m['user_input'])) ?></td>
        <td><?= nl2br(htmlspecialchars($m['bot_response'])) ?></td>
      </tr>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/footer.php';
