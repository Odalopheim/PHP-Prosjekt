<?php
require_once __DIR__ . '/../models/User.php';

class Auth {
    public static function handleLogin() {
        session_start();
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = User::verifyCredentials($email, $password);
        if ($user) {
            // Sett session og redirect
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            // Redirect tilbake til prosjektets index (stamme-nivå)
            header('Location: ../index.php');
            exit;
        } else {
            $_SESSION['auth_error'] = 'Ugyldig e-post eller passord.';
            header('Location: ../login.php');
            exit;
        }
    }

    public static function handleRegister() {
        session_start();
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $ok = User::register($name, $email, $password);
        if ($ok) {
            // Etter registrering, logg inn automatisk
            $user = User::verifyCredentials($email, $password);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header('Location: ../index.php');
            exit;
        } else {
            $_SESSION['auth_error'] = 'Kunne ikke registrere brukeren. E-post kan allerede være i bruk.';
            header('Location: ../register.php');
            exit;
        }
    }

    public static function logout() {
        session_start();
        session_unset();
        session_destroy();
        header('Location: ../login.php');
        exit;
    }
}
// Enkel dispatcher slik at denne filen kan kalles direkte fra form action
if (php_sapi_name() !== 'cli') {
    $action = $_GET['action'] ?? '';
    if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        Auth::handleLogin();
    }
    if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        Auth::handleRegister();
    }
    if ($action === 'logout') {
        Auth::logout();
    }
}
