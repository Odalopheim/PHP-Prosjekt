<?php
require_once 'models/Database.php';

try {
    $db = Database::connect();
    echo "✅ Databaseforbindelsen fungerer!";
} catch (Exception $e) {
    echo "❌ Feil: " . $e->getMessage();
}
?>