<?php
session_start();

// Hvis allerede logget inn, send til index via front controller
if (!empty($_SESSION['user_id'])) {
  header('Location: ' . $base . '/index.php?page=chatbot');
  exit;
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// Hent og fjerne eventuell feil
$error = $_SESSION['auth_error'] ?? '';
unset($_SESSION['auth_error']);

// Hent og fjerne eventuell innloggingfeil
$message = $_SESSION['auth_message'] ?? '';
unset($_SESSION['auth_message']);

require_once __DIR__ . '/header.php';
?>

  <div class="auth-box">
    <h1>Logg inn</h1>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($message): ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

  <form method="post" action="<?= htmlspecialchars($base) ?>/auth.php?action=login">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
      <label>E-post<br><input type="email" name="email" required></label><br>
      <label>Passord<br><input type="password" name="password" required></label><br>
      <button type="submit">Logg inn</button>
    </form>
    
    <p>Ny bruker? <a href="<?= htmlspecialchars($base) ?>/index.php?page=register" class="button">Registrer deg</a></p>
  </div>
<?php include __DIR__  . '/footer.php'; ?>

