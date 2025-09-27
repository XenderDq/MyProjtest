<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ob_start();

$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = trim($path, '/');

$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$protectedPages = ['profile'];

$redirectUrl = null;
if (in_array($path, $protectedPages) && !$isLoggedIn) {
    $redirectUrl = '/';
} elseif (($path === 'registration' || $path === 'login') && $isLoggedIn) {
    $redirectUrl = '/profile';
}

if ($redirectUrl) {
    ob_end_clean();
    header('Location: ' . $redirectUrl);
    exit;
}

include $_SERVER['DOCUMENT_ROOT'] . '/include/header.php';

switch ($path) {
    case '':
        include_once $_SERVER['DOCUMENT_ROOT'] . '/include/pages/main/index.php';
        break;
    case 'registration':
        include_once $_SERVER['DOCUMENT_ROOT'] . '/include/pages/registr/index.php';
        break;
    case 'login':
        include_once $_SERVER['DOCUMENT_ROOT'] . '/include/pages/login/index.php';
        break;
    case 'profile':
        include_once $_SERVER['DOCUMENT_ROOT'] . '/include/pages/profile/index.php';
        break;
    case 'logout':
        include_once $_SERVER['DOCUMENT_ROOT'] . '/include/pages/logout/index.php';
        break;
    default:
        http_response_code(404);
        include_once $_SERVER['DOCUMENT_ROOT'] . '/include/pages/404/index.php';
        break;
}

include $_SERVER['DOCUMENT_ROOT'] . '/include/footer.php';

ob_end_flush();
?>