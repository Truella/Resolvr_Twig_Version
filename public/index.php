<?php
// Configure session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 3600); // 1 hour

// For HTTPS environments (Railway uses HTTPS)
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    ini_set('session.cookie_secure', 1);
}

// Use a persistent writable directory for sessions
// Try multiple paths in order of preference
$possiblePaths = [
    __DIR__ . '/../data/sessions',  // Your app's data directory
    '/tmp/sessions',                 // Standard temp directory
    sys_get_temp_dir() . '/sessions' // System temp
];

$sessionPath = null;
foreach ($possiblePaths as $path) {
    if (!is_dir($path)) {
        @mkdir($path, 0777, true);
    }
    if (is_dir($path) && is_writable($path)) {
        $sessionPath = $path;
        break;
    }
}

if ($sessionPath) {
    session_save_path($sessionPath);
    error_log('Session save path: ' . $sessionPath);
} else {
    error_log('WARNING: No writable session path found!');
}

// Start session BEFORE any redirects or output
session_start();

// Debug: Log session info
error_log('Session started - ID: ' . session_id() . ', Save Path: ' . session_save_path());

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
        error_log('=== DASHBOARD ACCESS ===');
        error_log('Session ID: ' . session_id());
        error_log('Session data: ' . print_r($_SESSION, true));
        
        if (!isset($_SESSION['user'])) {
            error_log('No user in session, redirecting to login');
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