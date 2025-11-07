<?php
// Last inn miljøvariabler fra .env
function loadEnv($path) {
    if (!file_exists($path)) {
        die('.env-fil ikke funnet!');
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorer kommentarer
        if (strpos(trim($line), '#') === 0) continue;
        
        // Del opp i nøkkel=verdi
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Sett som konstant
        if (!defined($key)) {
            define($key, $value);
        }
    }
}

// Last inn .env-filen
loadEnv(__DIR__ . '/.env');
?>