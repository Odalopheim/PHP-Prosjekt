<?php
session_start();
// beregn base-URL: bruk definert BASE_URL fra front controller hvis tilgjengelig
$base = defined('BASE_URL') ? BASE_URL : '/php/PHP-Prosjekt/public';
// Hvis allerede logget inn, send til index via front controller
if (!empty($_SESSION['user_id'])) {
  header('Location: ' . $base . '/index.php?page=chatbot');
  exit;
}
// Hent og tøm eventuell feil
$error = $_SESSION['auth_error'] ?? '';
unset($_SESSION['auth_error']);
?>
<?php include __DIR__  . '/header.php'; ?>
  <div class="auth-box">
    <h1>Logg inn</h1>
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
  <form method="post" action="<?= htmlspecialchars($base) ?>/auth.php?action=login">
      <label>E-post<br><input type="email" name="email" required></label><br>
      <label>Passord<br><input type="password" name="password" required></label><br>
      <button type="submit">Logg inn</button>
    </form>
    <p>Ny bruker? <a href="<?= htmlspecialchars($base) ?>/index.php?page=register">Registrer deg</a></p>
  </div>
<?php include __DIR__  . '/footer.php'; ?>

