<?php
require_once __DIR__ . '/../shared/session_config.php';
session_start();

if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        if ($needle === '' || $needle === null) return true;
        if ($haystack === '' || $haystack === null) return false;
        $length = strlen($needle);
        return $length === 0 || substr($haystack, -$length) === $needle;
    }
}

// Calculate mngtools base URL (parent of yoAdminPortal)
function get_mngtools_base_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    // Get the path to yoAdminPortal, then go up one level
    $portal_path = str_replace('\\', '/', dirname(__DIR__)); // /path/to/mngtools
    $doc_root = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
    $web_path = substr($portal_path, strlen($doc_root)); // /mngtools
    return $protocol . $host . $web_path;
}

// Configuration - absolute SSO URL
$mngtools_base = get_mngtools_base_url();
$sso_url = $mngtools_base . '/yoSSO/';

function get_current_base_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    $path = str_replace('\\', '/', $path);
    $path = rtrim($path, '/');
    return $protocol . $host . $path;
}

$base_url = get_current_base_url();

// 1. Check if already logged in (unified session key 'user')
if (!isset($_SESSION['user'])) {
    // Redirect to SSO with app name
    $callback_url = "$base_url/callback.php?next=" . urlencode($_SERVER['REQUEST_URI']);
    $redirect_uri = urlencode($callback_url);
    $app_name = urlencode("Portal");
    header("Location: $sso_url?redirect_uri=$redirect_uri&app_name=$app_name");
    exit;
}

// 1.5 Check Session Timeout (12 hours = 43200 seconds)
$timeout_duration = 12 * 60 * 60;
if (isset($_SESSION['login_time'])) {
    if ((time() - $_SESSION['login_time']) > $timeout_duration) {
        // Session expired
        session_destroy();
        $callback_url = "$base_url/callback.php?next=" . urlencode($_SERVER['REQUEST_URI']);
        $redirect_uri = urlencode($callback_url);
        $app_name = urlencode("Portal");
        header("Location: $sso_url?redirect_uri=$redirect_uri&app_name=$app_name");
        exit;
    }
} else {
    $_SESSION['login_time'] = time();
}

// Debug: Check session if requested
if (isset($_GET['debug_session'])) {
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    exit;
}

// Authenticated - script execution continues in the file that required this
?>
