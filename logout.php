<?php
session_start();
session_destroy();

// Get the base URL for this application
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['SCRIPT_NAME']);
$path = str_replace('\\', '/', $path);
$path = rtrim($path, '/');
$base_url = $protocol . $host . $path;

// Redirect to SSO login with this app as the redirect target
$redirect_uri = urlencode("$base_url/callback.php");
$app_name = urlencode("Portal");
header("Location: ../yoSSO/?redirect_uri=$redirect_uri&app_name=$app_name");
exit;
?>
