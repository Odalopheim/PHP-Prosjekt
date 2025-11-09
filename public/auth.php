<?php
// Enkel public-facing wrapper som lar skjemaer poste til en sikker URL under public/
// Forwarder til controllers/Auth.php som allerede har en liten dispatcher basert på ?action=
require_once __DIR__ . '/../controllers/Auth.php';

// Når denne filen inkluderes/requests, vil Auth.php sin dispatcher kjøre og håndtere action

?>
