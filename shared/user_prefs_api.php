<?php
/**
 * mngtools User Preferences API
 * Stores and retrieves user-specific settings (like theme) in JSON file
 */

// Allow cross-origin for same-site tools
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$prefs_file = __DIR__ . '/user_prefs.json';

// Load existing preferences
function loadPrefs() {
    global $prefs_file;
    if (file_exists($prefs_file)) {
        $content = file_get_contents($prefs_file);
        return json_decode($content, true) ?: [];
    }
    return [];
}

// Save preferences
function savePrefs($prefs) {
    global $prefs_file;
    file_put_contents($prefs_file, json_encode($prefs, JSON_PRETTY_PRINT));
}

// GET: Retrieve user preferences
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = $_GET['user'] ?? null;
    
    if (!$user) {
        echo json_encode(['error' => 'Missing user parameter']);
        exit;
    }
    
    $prefs = loadPrefs();
    $userPrefs = $prefs[$user] ?? ['theme' => 'dark'];
    
    echo json_encode(['success' => true, 'prefs' => $userPrefs]);
    exit;
}

// POST: Save user preferences
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Also support form data
    if (!$input) {
        $input = $_POST;
    }
    
    $user = $input['user'] ?? null;
    $newPrefs = $input['prefs'] ?? null;
    
    if (!$user || !$newPrefs) {
        echo json_encode(['error' => 'Missing user or prefs parameter']);
        exit;
    }
    
    $prefs = loadPrefs();
    
    // Merge new preferences with existing
    if (!isset($prefs[$user])) {
        $prefs[$user] = [];
    }
    $prefs[$user] = array_merge($prefs[$user], $newPrefs);
    
    savePrefs($prefs);
    
    echo json_encode(['success' => true, 'prefs' => $prefs[$user]]);
    exit;
}

echo json_encode(['error' => 'Invalid request method']);
