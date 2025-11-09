<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Auto-deteksjon av BASE_URL (peker til /.../public)
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
define('BASE_URL', $scriptDir);

// Hvitliste over sider
$allowedPages = ['chatbot', 'login'];
$page = 'chatbot';
if (!empty($_GET['page']) && in_array($_GET['page'], $allowedPages, true)) {
    $page = $_GET['page'];
}

// Enkel router: include view-filen (views kan selv kreve controllere/modeller)
// Dette unngår hardkodede app/ stier som ikke finnes i repoet.
if (in_array($page, $allowedPages, true)) {
    require_once __DIR__ . '/../views/' . $page . '.php';
} else {
    // fallback
    require_once __DIR__ . '/../views/chatbot.php';
}
?>