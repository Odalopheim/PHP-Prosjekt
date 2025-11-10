<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Auto-deteksjon av BASE_URL (peker til /.../public)
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
define('BASE_URL', $scriptDir);

// Hvitliste over sider som har egne kontrollere
$controllerPages = ['chatbot', 'auth'];

// Liste over statiske visninger
$viewPages = ['login', 'register', 'chatbot', 'history', 'admin_messages'];

// Bestem hvilken side som skal lastes
$page = 'chatbot';
if (!empty($_GET['page'])) {
    $requestedPage = $_GET['page'];
    if (in_array($requestedPage, $controllerPages, true) || in_array($requestedPage, $viewPages, true)) {
        $page = $requestedPage;
    }
}

// Først: hvis det finnes en view-fil, inkluder den direkte (det er enklest
// og kompatibelt med prosjektstrukturen hvor views/* finnes).
$viewPath = __DIR__ . "/../views/{$page}.php";
if (file_exists($viewPath)) {
    require_once $viewPath;
    return;
}

// Hvis ikke view finnes, prøv å finne en controller i ../controllers eller ../app/controllers
$className = ucfirst($page);
$controllerPaths = [
    __DIR__ . "/../controllers/{$className}.php",
];
$loaded = false;
foreach ($controllerPaths as $controllerPath) {
    if (file_exists($controllerPath)) {
        require_once $controllerPath;
        if (class_exists($className)) {
            $controller = new $className();
            if (method_exists($controller, 'handleRequest')) {
                $controller->handleRequest();
            }
            $loaded = true;
            break;
        }
    }
}

if (! $loaded) {
    echo "Feil: Fant verken view eller controller for siden '{$page}'.";
}
?>