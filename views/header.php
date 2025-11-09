<?php
// Start session hvis den ikke allerede er aktiv
if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

// Definer base-URL
$base = defined('BASE_URL') ? BASE_URL : '/php/PHP-Prosjekt/public';

// Hjelpevariabler
$isLoggedIn = !empty($_SESSION['user_id']);
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'bruker');
$currentPage = $_GET['page'] ?? '';
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Værassistent</title>
  <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/css/style.css">
</head>
<body>
  <header class="site-header">
    <div class="site-header__inner">
      <a class="site-brand" href="<?= htmlspecialchars($base) ?>/index.php?page=chatbot">Værchatbot</a>
      <nav class="site-nav">
        <!-- sjekker om man er loggd in etter hvilken header man skal få -->
        <?php if ($isLoggedIn): ?>
          <span style="margin-right:0.6rem;">Hei, <?= $userName ?></span>
          <a class="site-nav__link" href="<?= htmlspecialchars($base) ?>/auth.php?action=logout">Logg ut</a>
          <a class="site-nav__link" href="<?= htmlspecialchars($base) ?>/index.php?page=<?= $currentPage === 'history' ? 'chatbot' : 'history' ?>">
            <!-- sjekker om man er på history-siden for å bytte link-text -->
            <?= $currentPage === 'history' ? 'Chatbot' : 'Historie' ?>
          </a>
        <!-- Hvis man ikke er logget inn er dette heaaderen -->
        <?php else: ?>
          <a class="site-nav__link" href="<?= htmlspecialchars($base) ?>/index.php?page=login">Logg inn</a>
          <a class="site-nav__link" href="<?= htmlspecialchars($base) ?>/index.php?page=register">Registrer</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>