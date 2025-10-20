<?php
session_start();
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
$error = $_SESSION['auth_error'] ?? '';
unset($_SESSION['auth_error']);
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <title>Registrer</title>
  <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
  <div class="auth-box">
    <h1>Registrer ny bruker</h1>
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="controllers/Auth.php?action=register">
      <label>Navn<br><input type="text" name="name" required></label><br>
      <label>E-post<br><input type="email" name="email" required></label><br>
      <label>Passord<br><input type="password" name="password" required></label><br>
      <button type="submit">Registrer</button>
    </form>
    <p>Allerede bruker? <a href="login.php">Logg inn</a></p>
  </div>
</body>
</html>
