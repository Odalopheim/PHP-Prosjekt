<?php
session_start();

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . $base . '/index.php?page=chatbot');
    exit;
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$error = $_SESSION['auth_error'] ?? '';
unset($_SESSION['auth_error']);
require_once __DIR__ . '/header.php';
?>



<div class="auth-box">
    <h1>Registrer ny bruker</h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= htmlspecialchars($base) ?>/auth.php?action=register">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

        <label>Navn<br><input type="text" name="name" required></label><br>
        <label>E-post<br><input type="email" name="email" required></label><br>
        <label>Passord<br><input type="password" name="password" required></label><br>
        <button type="submit">Registrer</button>
    </form>

    <p>Allerede bruker? <a href="<?= htmlspecialchars($base) ?>/index.php?page=login"class="button">Logg inn</a></p>
</div>

<?php include_once __DIR__ . '/footer.php'; ?>
