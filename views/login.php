<?php
session_start();
// Hvis allerede logget inn, send til index
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
// Hent og tøm eventuell feil
$error = $_SESSION['auth_error'] ?? '';
unset($_SESSION['auth_error']);
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <title>Logg inn</title>
  <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
  <div class="auth-box">
    <h1>Logg inn</h1>
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="controllers/Auth.php?action=login">
      <label>E-post<br><input type="email" name="email" required></label><br>
      <label>Passord<br><input type="password" name="password" required></label><br>
      <button type="submit">Logg inn</button>
    </form>
    <p>Ny bruker? <a href="register.php">Registrer deg</a></p>
  </div>
</body>
</html>
