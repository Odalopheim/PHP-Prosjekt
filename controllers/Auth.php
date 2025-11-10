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
            // Lagre brukerens epost i session for å knytte meldinger til epost
            $_SESSION['user_email'] = $user['email'] ?? $email;
            // Marker admin hvis epost slutter med @admin.no
            if (self::isAdminEmail($user['email'])) {
                $_SESSION['is_admin'] = true;
                $_SESSION['admin_notice'] = 'Du er administrator.';
            } else {
                $_SESSION['is_admin'] = false;
            }
            // Redirect tilbake til prosjektets index (stamme-nivå)
            header('Location: ../public/index.php?page=chatbot');
            exit;
        } else {
            $_SESSION['auth_error'] = 'Ugyldig e-post eller passord.';
            header('Location: ../public/index.php?page=login');
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
            // Lagre brukerens epost i session for å knytte meldinger til epost
            $_SESSION['user_email'] = $user['email'] ?? $email;
            // Marker admin hvis epost slutter med @admin.no
            if (self::isAdminEmail($user['email'])) {
                $_SESSION['is_admin'] = true;
                $_SESSION['admin_notice'] = 'Du er administrator.';
            } else {
                $_SESSION['is_admin'] = false;
            }
            header('Location: ../public/index.php?page=chatbot');
            exit;
        } else {
            $_SESSION['auth_error'] = 'Kunne ikke registrere brukeren. E-post kan allerede være i bruk.';
            header('Location: ../public/index.php?page=register');
            exit;
        }
    }

    public static function logout() {
        session_start();
        session_unset();
        session_destroy();
        header('Location: ../public/index.php?page=login');
        exit;
    }

    // Sjekk om epost tilhører admin-domene
    private static function isAdminEmail(string $email): bool {
        return preg_match('/@admin\.no$/i', $email) === 1;
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
