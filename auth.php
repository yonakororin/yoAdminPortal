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
    
    // SCRIPT_NAME から mngtools のパスを取得
    // 例: /mngtools/yoAdminPortal/viewer.php → /mngtools
    $script_path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    $parts = explode('/', trim($script_path, '/'));
    
    // yoAdminPortal の親ディレクトリ = mngtools
    // パス構造: /mngtools/yoAdminPortal/file.php
    if (count($parts) >= 2) {
        // 最後の2つ（yoAdminPortal/file.php）を除いて結合
        array_pop($parts); // file.php を削除
        array_pop($parts); // yoAdminPortal を削除
        $web_path = '/' . implode('/', $parts);
        if ($web_path === '/') {
            $web_path = '';
        }
    } else {
        $web_path = '';
    }
    
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

// 現在のページの完全なURLを取得（クエリパラメータを含む）
function get_current_page_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    return $protocol . $host . $uri;
}

$base_url = get_current_base_url();

// 1. Check if already logged in (unified session key 'user')
if (!isset($_SESSION['user'])) {
    // 現在のページの完全なURLを取得
    $current_page = get_current_page_url();
    $callback_url = "$base_url/callback.php?next=" . urlencode($current_page);
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
        $current_page = get_current_page_url();
        $callback_url = "$base_url/callback.php?next=" . urlencode($current_page);
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

// 2. Load user permissions from mnguser data
$permissions = ['*']; // Default to admin (full access) for backward compatibility
$current_user = $_SESSION['user'] ?? '';

if (!empty($current_user)) {
    // First check mnguser data
    $mnguser_file = dirname(__DIR__) . '/adminTools/mnguser/data/users/' . $current_user . '.json';
    
    if (file_exists($mnguser_file)) {
        $user_data = json_decode(file_get_contents($mnguser_file), true);
        if (is_array($user_data) && isset($user_data['permissions'])) {
            $permissions = $user_data['permissions'];
        }
    } else {
        // If user file doesn't exist in mnguser:
        // - 'admin' gets full access by default
        // - Other users could get empty permissions or full access depending on policy
        if ($current_user === 'admin') {
            $permissions = ['*'];
        } else {
            // For users not in mnguser, grant full access by default
            // (this can be changed to [] for stricter policy)
            $permissions = ['*'];
        }
    }
}

// Debug: Check permissions if requested
if (isset($_GET['debug_permissions'])) {
    echo "<pre>";
    echo "Current User: " . htmlspecialchars($current_user) . "\n";
    echo "mnguser file: " . $mnguser_file . "\n";
    echo "File exists: " . (file_exists($mnguser_file) ? 'YES' : 'NO') . "\n";
    echo "Permissions:\n";
    print_r($permissions);
    echo "</pre>";
    exit;
}

/**
 * 現在のページへのアクセス権限をチェック
 * 権限がない場合は403エラーを表示して終了
 * 
 * @param string|null $required_url チェック対象のURL（nullの場合は現在のURLを使用）
 */
function check_page_permission($required_url = null) {
    global $permissions, $current_user;
    
    // 管理者（*権限）は全てアクセス可能
    if (in_array('*', $permissions)) {
        return true;
    }
    
    // チェック対象のURLを決定
    if ($required_url === null) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $required_url = $protocol . $host . $_SERVER['REQUEST_URI'];
        
        // クエリパラメータを除去してベースURLで比較
        $required_url = strtok($required_url, '?');
    }
    
    // 権限リストにURLが含まれているかチェック
    foreach ($permissions as $allowed_url) {
        // 完全一致
        if ($allowed_url === $required_url) {
            return true;
        }
        
        // 部分一致（URLがパターンで終わる場合）
        // 例: http://localhost/mngtools/yoAdminBuilder/ で始まるURL全て許可
        if (substr($allowed_url, -1) === '*') {
            $pattern = rtrim($allowed_url, '*');
            if (strpos($required_url, $pattern) === 0) {
                return true;
            }
        }
        
        // URLの末尾のスラッシュを正規化して比較
        $normalized_allowed = rtrim($allowed_url, '/');
        $normalized_required = rtrim($required_url, '/');
        if ($normalized_allowed === $normalized_required) {
            return true;
        }
    }
    
    // アクセス拒否
    http_response_code(403);
    echo '<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>アクセス拒否</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #0d1117;
            color: #e6edf3;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            text-align: center;
            padding: 2rem;
        }
        h1 {
            color: #f85149;
            font-size: 4rem;
            margin-bottom: 0.5rem;
        }
        p {
            color: #8b949e;
            font-size: 1.2rem;
        }
        a {
            color: #58a6ff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>403</h1>
        <p>このページへのアクセス権限がありません</p>
        <p><a href="javascript:history.back()">戻る</a> | <a href="/mngtools/yoAdminPortal/viewer.php">ポータルへ</a></p>
    </div>
</body>
</html>';
    exit;
}

// Authenticated - script execution continues in the file that required this
?>
