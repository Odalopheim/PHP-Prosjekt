<?php
require_once __DIR__ . '/../models/User.php';

class Auth
{
    /**
     * Start session med trygge cookie-params om session ikke allerede er startet.
     */
    private static function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Disse parametrene krever at applikasjonen kjører over HTTPS i produksjon
            $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    /**
     * Redirect med feilmelding
     */
    private static function redirectWithError(string $page, string $message): void
    {
        $_SESSION['auth_error'] = $message;
        header('Location: ../public/index.php?page=' . $page);
        exit;
    }

    /**
     * CSRF-validering. Returnerer true hvis ok.
     */
    private static function validateCsrf(): bool
    {
        // Forutsetter at skjemaet sender feltet "csrf" og at token er generert i visningen
        $tokenSession = $_SESSION['csrf'] ?? '';
        $tokenPost = $_POST['csrf'] ?? '';
        if (empty($tokenSession) || empty($tokenPost)) {
            return false;
        }
        return hash_equals($tokenSession, $tokenPost);
    }

    /**
     * Håndter innlogging.
     */
    public static function handleLogin(): void
    {
        self::ensureSession();


        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirectWithError('login', 'Ugyldig forespørsel.');
        }

        if (!self::validateCsrf()) {
            self::redirectWithError('login', 'Sikkerhetsfeil. Last inn siden på nytt.');
        }

        // validering av brukerinput
        $email = trim((string)($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            self::redirectWithError('login', 'Alle felt må fylles ut.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            self::redirectWithError('login', 'Ugyldig e-postformat.');
        }

        $user = User::verifyCredentials($email, $password);

        // Sjekk om bruker er låst 
        if ($user === 'locked') {
            self::redirectWithError('login', 'For mange mislykkede innloggingsforsøk. Bruker er midlertidig sperret.');
        }

        if ($user === false || !is_array($user)) {
            self::redirectWithError('login', 'Ugyldig e-post eller passord.');
        }

        // Vellykket innlogging
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'] ?? '';
        $_SESSION['user_email'] = $user['email'] ?? $email;
        $_SESSION['is_admin'] = (isset($user['role']) && $user['role'] === 'Admin');
        if ($_SESSION['is_admin']) {
            $_SESSION['admin_notice'] = 'Du er administrator.';
        }

        header('Location: ../public/index.php?page=chatbot');
        exit;
    }

    /**
     * Håndter registrering.
     */
    public static function handleRegister(): void
    {
        self::ensureSession();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirectWithError('register', 'Ugyldig forespørsel.');
        }

        if (!self::validateCsrf()) {
            self::redirectWithError('register', 'Sikkerhetsfeil. Last inn siden på nytt.');
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        // Grunnleggende validering
        if ($name === '' || $email === '' || $password === '') {
            self::redirectWithError('register', 'Alle felt må fylles ut.');
        }

        if (strlen($name) < 2 || strlen($name) > 100) {
            self::redirectWithError('register', 'Navn må være mellom 2 og 100 tegn.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            self::redirectWithError('register', 'Ugyldig e-postformat.');
        }

        if (strlen($password) < 8) {
            self::redirectWithError('register', 'Passord må være minst 8 tegn.');
        }

        if (preg_match_all('/\d/', $password) < 2) {
            self::redirectWithError('register', 'Passord må inneholde minst 2 tall');
        }

        // Med @admin.no settes rolle til admin, hvis ikke settes til Standard
        $role = self::isAdminEmail($email) ? 'Admin' : 'Standard';

        $register = User::register($name, $email, $password, $role);
        if (!$register) {
            self::redirectWithError('register', 'Kunne ikke registrere brukeren. E-post kan allerede være i bruk.');
        }

        // Ved suksess, logg brukeren inn automatisk
        $user = User::verifyCredentials($email, $password);
        if ($user === false || $user === 'locked' || !is_array($user)) {
            $_SESSION['auth_message'] = 'Du er registrert!';
            header('Location: ../public/index.php?page=login');
            exit;
        }

        //sette session data
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'] ?? '';
        $_SESSION['user_email'] = $user['email'] ?? $email;
        $_SESSION['is_admin'] = (isset($user['role']) && $user['role'] === 'Admin');
        if ($_SESSION['is_admin']) {
            $_SESSION['admin_notice'] = 'Du er administrator.';
        }

        header('Location: ../public/index.php?page=chatbot');
        exit;
    }

    /**
     * Logg ut bruker.
     */
    public static function logout(): void
    {
        self::ensureSession();

        // Tøm session sikkert
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']
            );
        }
        session_destroy();

        // Ny session for melding
        session_start();
        $_SESSION['auth_message'] = 'Du er nå logget ut.';

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

// Sender bruker til riktig handling etter brukers posisjon
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
