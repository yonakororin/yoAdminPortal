<?php
// Check session status API
require_once __DIR__ . '/session_config.php';
session_start();

header('Content-Type: application/json');

$type = $_GET['type'] ?? 'tool'; // 'tool' or 'sso'

$logged_in = false;
if ($type === 'sso') {
    $logged_in = isset($_SESSION['yosso_user']);
} else {
    // Default to 'user' session key for tools
    $logged_in = isset($_SESSION['user']);
}

echo json_encode(['logged_in' => $logged_in]);
?>
