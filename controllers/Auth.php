<?php
require_once __DIR__ . '/../models/User.php';

class Auth
{
    /**
     * Håndter innlogging.
     */
    public static function handleLogin(): void
    {
        session_start();

        $email    = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = User::verifyCredentials($email, $password);

        if ($user) {
            // Sett session-variabler
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_email'] = $user['email'] ?? $email;

            // Marker admin hvis e-post slutter med @admin.no
            if (self::isAdminEmail($user['email'])) {
                $_SESSION['is_admin']      = true;
                $_SESSION['admin_notice']  = 'Du er administrator.';
            } else {
                $_SESSION['is_admin'] = false;
            }

            // Redirect til chatbot
            header('Location: ../public/index.php?page=chatbot');
            exit;
        }

        // Ugyldig innlogging
        $_SESSION['auth_error'] = 'Ugyldig e-post eller passord.';
        header('Location: ../public/index.php?page=login');
        exit;
    }

    /**
     * Håndter registrering.
     */
    public static function handleRegister(): void
    {
        session_start();

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $ok = User::register($name, $email, $password);

        if ($ok) {
            // Automatisk innlogging etter registrering
            $user = User::verifyCredentials($email, $password);

            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_email'] = $user['email'] ?? $email;

            if (self::isAdminEmail($user['email'])) {
                $_SESSION['is_admin']     = true;
                $_SESSION['admin_notice'] = 'Du er administrator.';
            } else {
                $_SESSION['is_admin'] = false;
            }

            header('Location: ../public/index.php?page=chatbot');
            exit;
        }

        // Registrering feilet
        $_SESSION['auth_error'] = 'Kunne ikke registrere brukeren. E-post kan allerede være i bruk.';
        header('Location: ../public/index.php?page=register');
        exit;
    }

    /**
     * Logg ut bruker.
     */
    public static function logout(): void
    {
        session_start();
        session_unset();
        session_destroy();

        header('Location: ../public/index.php?page=login');
        exit;
    }

    /**
     * Sjekk om e-post tilhører admin-domene.
     */
    private static function isAdminEmail(string $email): bool
    {
        return preg_match('/@admin\.no$/i', $email) === 1;
    }
}

// Enkel dispatcher slik at denne filen kan kalles direkte fra form action
if (php_sapi_name() !== 'cli') {
    $action = $_GET['action'] ?? '';

    if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        Auth::handleLogin();
    } elseif ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        Auth::handleRegister();
    } elseif ($action === 'logout') {
        Auth::logout();
    }
}