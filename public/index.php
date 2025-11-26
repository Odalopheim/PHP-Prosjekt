<?php
// Auto-deteksjon av BASE_URL (peker til /.../public)
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
define('BASE_URL', $scriptDir);

// ver sider som har egne kontrollere
$controllerPages = ['chatbot', 'auth'];
$viewPages = ['login', 'register', 'chatbot', 'history', 'admin'];

// Bestem hvilken side som skal lastes
$page = 'chatbot';
if (!empty($_GET['page'])) {
    $requestedPage = $_GET['page'];
    if (in_array($requestedPage, $controllerPages, true) || in_array($requestedPage, $viewPages, true)) {
        $page = $requestedPage;
    }
}

// Sjekk om det finnes en view-fil, inkluder den direkte 
$viewPath = __DIR__ . "/../views/{$page}.php";
if (file_exists($viewPath)) {
    require_once $viewPath;
    return;
}

// Hvis ikke view finnes, prøv å finne en controller
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