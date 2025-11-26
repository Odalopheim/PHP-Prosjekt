<?php
/*
 * Enkel wrapper som lar skjemaer sende til en sikker URL under public for å ikke ha tilgang til controller
 * Sender til controllers/Auth.php som allerede har en dispatcher basert på ?action=
 */
require_once __DIR__ . '/../controllers/Auth.php';

?>
