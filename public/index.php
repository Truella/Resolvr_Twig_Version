<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

// For HTTPS environments (Railway uses HTTPS)
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    ini_set('session.cookie_secure', 1);
}

// Set session save path to a writable directory
$sessionPath = sys_get_temp_dir() . '/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);

// Start session BEFORE any redirects or output
session_start();


require_once __DIR__ . '/../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader, [
    'cache' => false, // Disable cache for development
    'debug' => true,
]);

// Simple routing
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_uri = rtrim($request_uri, '/');

// Route handler
switch ($request_uri) {
    case '':
    case '/':
        echo $twig->render('landing.twig');
        break;

    case '/login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../controllers/auth.php';

            handleLogin($twig);
        } else {
            echo $twig->render('auth/login.twig', ['error' => $_SESSION['error']  ?? null, 'success' => $_SESSION['success'] ?? null]);
            unset($_SESSION['error']);
            unset($_SESSION['success']);
        }
        break;

    case '/signup':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../controllers/auth.php';

            handleSignup($twig);
        } else {
            echo $twig->render('auth/signup.twig', ['error' => $_SESSION['error'] ?? null]);
            unset($_SESSION['error']);
        }
        break;

    case '/logout':
        require_once __DIR__ . '/../controllers/auth.php';
        handleLogout();
        break;

    case '/dashboard':
    if (!isset($_SESSION['user'])) {
        header('Location: /login');
        exit;
    }

    require_once __DIR__ . '/../controllers/dashboard.php'; 
    renderDashboard($twig);
    break;

    case '/dashboard/tickets':
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../controllers/tickets.php';
            handleTicketAction($twig);
        } else {
            require_once __DIR__ . '/../controllers/tickets.php';

            renderTickets($twig);
        }
        break;

    default:
        http_response_code(404);
        echo $twig->render('404.twig');
        break;
}