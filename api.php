<?php
require_once 'auth.php';
header('Content-Type: application/json');

// Browse Action
if (isset($_GET['action']) && $_GET['action'] === 'browse') {
    $requested_path = $_GET['path'] ?? '';
    
    if (empty($requested_path)) {
        $base_dir = __DIR__;
    } else {
        if (!is_dir($requested_path)) {
            $relative_attempt = __DIR__ . DIRECTORY_SEPARATOR . $requested_path;
            if (is_dir($relative_attempt)) {
                $base_dir = $relative_attempt;
            } else {
                $base_dir = __DIR__;
            }
        } else {
            $base_dir = $requested_path;
        }
    }
    $base_dir = realpath($base_dir);
    
    $items = scandir($base_dir);
    $result = [];
    
    foreach ($items as $item) {
        if ($item === '.') continue;
        $full = $base_dir . DIRECTORY_SEPARATOR . $item;
        $type = is_dir($full) ? 'dir' : 'file';
        
        $allowedExts = isset($_GET['exts']) ? explode(',', $_GET['exts']) : ['json'];
        if ($type === 'file') {
            $ext = pathinfo($item, PATHINFO_EXTENSION);
            if (!in_array('*', $allowedExts)) {
                if (!in_array(strtolower($ext), $allowedExts)) continue;
            }
        }
        
        $result[] = [
            'name' => $item,
            'type' => $type,
            'path' => $full
        ];
    }
    
    usort($result, function($a, $b) {
        if ($a['type'] === $b['type']) return strnatcmp($a['name'], $b['name']);
        return ($a['type'] === 'dir') ? -1 : 1;
    });

    echo json_encode([
        'current_path' => $base_dir,
        'items' => $result
    ]);
    exit;
}

// Default file if none specified
$filename = 'portal_config.json';

if (isset($_GET['file'])) {
    $requestedFile = $_GET['file'];
    if (str_ends_with($requestedFile, '.json')) {
        $filename = $requestedFile;
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid filename (must be .json)']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetFile = $_POST['filename'] ?? $filename;
    $content = $_POST['config'] ?? file_get_contents('php://input');

    if ($content) {
        if (!str_ends_with($targetFile, '.json')) $targetFile .= '.json';
        
        // ファイルパスを解決
        // 絶対パスでない場合は __DIR__ を基準にする
        if (!preg_match('/^(\/|[a-zA-Z]:)/', $targetFile)) {
            $targetFile = __DIR__ . '/' . $targetFile;
        }
        
        // セキュリティチェック: __DIR__ 配下のみ許可（オプション）
        $realTargetDir = realpath(dirname($targetFile));
        $realBaseDir = realpath(__DIR__);
        
        // ディレクトリが存在しない場合は作成を試みる
        $targetDir = dirname($targetFile);
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create directory']);
                exit;
            }
        }
        
        $result = file_put_contents($targetFile, $content);
        
        if ($result !== false) {
            echo json_encode(['success' => true, 'file' => $targetFile, 'bytes' => $result]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to write file',
                'file' => $targetFile,
                'writable' => is_writable(dirname($targetFile))
            ]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No data received']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // GETでもパスを解決
    $targetFile = $filename;
    if (!preg_match('/^(\/|[a-zA-Z]:)/', $targetFile)) {
        $targetFile = __DIR__ . '/' . $targetFile;
    }
    
    if (file_exists($targetFile)) {
        readfile($targetFile);
    } else {
        echo json_encode(['title' => 'Portal', 'links' => []]);
    }
}

