<?php
require_once 'ChatBot.php';
$bot = new ChatBot();

$input = $_GET['sted'] ?? '';
$response = $bot->respond($input);
?>

<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="UTF-8">
  <title>Værassistent</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>🌤️ Værassistent</h1>
  <form method="get">
    <input type="text" name="sted" placeholder="Skriv inn sted..." value="<?= htmlspecialchars($input) ?>">
    <button type="submit">Hent vær</button>
  </form>
  <p><?= htmlspecialchars($response) ?></p>
</body>
</html>
