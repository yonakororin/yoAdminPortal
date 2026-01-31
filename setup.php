<?php
/**
 * yoAdminPortal セットアップスクリプト
 * 
 * yoAdminPortal/shared の設定ファイルを mngtools/shared へ展開します。
 * 既存のファイルがある場合は競合を検出してレポートします。
 * CLIまたはWebブラウザから実行可能です。
 * 
 * 使用方法 (CLI): php setup.php
 * 使用方法 (Web): ブラウザで setup.php にアクセス
 */

// パス設定
$local_shared = __DIR__ . '/shared';
$global_shared = dirname(__DIR__) . '/shared';

$is_cli = php_sapi_name() === 'cli';
$has_errors = false;
$has_warnings = false;

/**
 * 出力用ヘルパー関数
 */
function output($message, $is_cli, $type = 'info') {
    $prefix = '';
    $suffix = '';
    
    if (!$is_cli) {
        switch ($type) {
            case 'success':
                $prefix = '<span style="color: #22c55e;">';
                $suffix = '</span>';
                break;
            case 'error':
                $prefix = '<span style="color: #ef4444; font-weight: bold;">';
                $suffix = '</span>';
                break;
            case 'warning':
                $prefix = '<span style="color: #f59e0b;">';
                $suffix = '</span>';
                break;
            case 'conflict':
                $prefix = '<span style="color: #ef4444; background: rgba(239,68,68,0.1); padding: 2px 6px; border-radius: 3px;">';
                $suffix = '</span>';
                break;
        }
    }
    
    if ($is_cli) {
        echo $message . "\n";
    } else {
        echo $prefix . htmlspecialchars($message) . $suffix . "<br>";
    }
}

/**
 * 2つのPHPファイルを比較して競合を検出
 */
function compare_php_files($local_content, $global_content, $filename) {
    $result = [
        'identical' => false,
        'compatible' => true,
        'conflicts' => [],
        'differences' => []
    ];
    
    // 正規化して比較（空白の違いを無視）
    $local_normalized = preg_replace('/\s+/', ' ', trim($local_content));
    $global_normalized = preg_replace('/\s+/', ' ', trim($global_content));
    
    if ($local_normalized === $global_normalized) {
        $result['identical'] = true;
        return $result;
    }
    
    // 特定のファイルごとの競合チェック
    switch ($filename) {
        case 'session_config.php':
            // Cookie パスの確認
            if (preg_match('/session_set_cookie_params\s*\([^,]*,\s*[\'"]([^\'"]+)[\'"]/', $global_content, $m)) {
                $global_cookie_path = $m[1];
                if ($global_cookie_path !== '/') {
                    $result['conflicts'][] = [
                        'type' => 'cookie_path',
                        'message' => "グローバル設定の Cookie パスが '/' ではなく '$global_cookie_path' です",
                        'severity' => 'warning'
                    ];
                    $result['compatible'] = false;
                }
            }
            
            // sessionLifetime の違い
            $local_lifetime = 0;
            $global_lifetime = 0;
            if (preg_match('/\$sessionLifetime\s*=\s*(\d+)/', $local_content, $m)) {
                $local_lifetime = (int)$m[1];
            }
            if (preg_match('/\$sessionLifetime\s*=\s*(\d+)/', $global_content, $m)) {
                $global_lifetime = (int)$m[1];
            }
            if ($local_lifetime !== $global_lifetime && $global_lifetime !== 0) {
                $result['differences'][] = "セッション有効期限: ローカル=$local_lifetime, グローバル=$global_lifetime";
            }
            break;
            
        case 'check_session.php':
            // セッションキーの確認
            $local_keys = [];
            $global_keys = [];
            preg_match_all('/\$_SESSION\[[\'"]([^\'"]+)[\'"]\]/', $local_content, $m);
            $local_keys = $m[1] ?? [];
            preg_match_all('/\$_SESSION\[[\'"]([^\'"]+)[\'"]\]/', $global_content, $m);
            $global_keys = $m[1] ?? [];
            
            $missing = array_diff($local_keys, $global_keys);
            if (!empty($missing)) {
                $result['conflicts'][] = [
                    'type' => 'session_keys',
                    'message' => "グローバル設定に不足しているセッションキー: " . implode(', ', $missing),
                    'severity' => 'warning'
                ];
            }
            break;
            
        case 'theme.css':
        case 'theme.js':
            // テーマファイルはグローバルの方が新しいと想定
            $result['differences'][] = "ファイルの内容が異なります（グローバル側を優先）";
            break;
            
        case 'paths.php':
        case 'paths_config.json':
            // パス設定は固有なので競合チェック不要
            $result['differences'][] = "パス設定ファイルは環境固有です";
            break;
            
        default:
            $result['differences'][] = "ファイルの内容が異なります";
    }
    
    return $result;
}

/**
 * ファイルをコピーまたはマージ
 */
function deploy_file($local_file, $global_file, $filename, $is_cli, &$has_warnings) {
    $local_content = file_get_contents($local_file);
    
    if (file_exists($global_file)) {
        $global_content = file_get_contents($global_file);
        $comparison = compare_php_files($local_content, $global_content, $filename);
        
        if ($comparison['identical']) {
            output("[·] $filename は同一です（スキップ）", $is_cli);
            return true;
        }
        
        // 競合がある場合
        if (!empty($comparison['conflicts'])) {
            output("", $is_cli);
            foreach ($comparison['conflicts'] as $conflict) {
                $severity = $conflict['severity'] ?? 'warning';
                output("  [競合] $filename: " . $conflict['message'], $is_cli, 'conflict');
                if ($severity === 'error') {
                    return false;
                }
            }
            $has_warnings = true;
        }
        
        // 差異がある場合
        if (!empty($comparison['differences'])) {
            foreach ($comparison['differences'] as $diff) {
                output("  [情報] $filename: $diff", $is_cli, 'warning');
            }
        }
        
        output("[·] $filename はグローバル側に既に存在します（スキップ）", $is_cli);
        return true;
        
    } else {
        // グローバルにファイルがない場合はコピー
        if (copy($local_file, $global_file)) {
            output("[✓] $filename をコピーしました", $is_cli, 'success');
            return true;
        } else {
            output("[✗] $filename のコピーに失敗しました", $is_cli, 'error');
            return false;
        }
    }
}

// 出力開始
if (!$is_cli) {
    echo "<!DOCTYPE html><html><head><title>yoAdminPortal セットアップ</title>";
    echo "<style>body{font-family:monospace;padding:2rem;background:#1a1a2e;color:#eee;line-height:1.6;}</style>";
    echo "</head><body><h1>yoAdminPortal セットアップ</h1><pre>";
}

output("=================================", $is_cli);
output("  yoAdminPortal セットアップ", $is_cli);
output("=================================", $is_cli);
output("", $is_cli);

// 1. グローバル shared ディレクトリの確認/作成
output("[共有ディレクトリ]", $is_cli);
if (!file_exists($global_shared)) {
    if (mkdir($global_shared, 0755, true)) {
        output("[✓] shared ディレクトリを作成しました", $is_cli, 'success');
    } else {
        output("[✗] shared ディレクトリの作成に失敗しました", $is_cli, 'error');
        $has_errors = true;
    }
} else {
    output("[·] shared ディレクトリは既に存在します", $is_cli);
}

output("", $is_cli);
output("[ファイル展開]", $is_cli);

// 2. ローカル shared のファイルをグローバルに展開
$files_to_deploy = [
    'session_config.php' => 'セッション設定',
    'check_session.php' => 'セッション確認API',
    'theme.css' => 'テーマCSS',
    'theme.js' => 'テーマJS',
    'paths.php' => 'パス設定',
    'paths_config.json' => 'パス設定JSON',
    'user_prefs_api.php' => 'ユーザー設定API'
];

foreach ($files_to_deploy as $filename => $description) {
    $local_file = $local_shared . '/' . $filename;
    $global_file = $global_shared . '/' . $filename;
    
    if (!file_exists($local_file)) {
        output("[·] $filename はローカルに存在しません（スキップ）", $is_cli);
        continue;
    }
    
    if (!deploy_file($local_file, $global_file, $filename, $is_cli, $has_warnings)) {
        $has_errors = true;
    }
}

// 3. portal_config.json の確認/作成
output("", $is_cli);
output("[ポータル設定]", $is_cli);

$portal_config_file = __DIR__ . '/portal_config.json';
if (!file_exists($portal_config_file)) {
    $default_config = [
        'title' => 'Portal',
        'target_env' => 'dev',
        'links' => [
            [
                'label' => 'Dashboard',
                'url' => '#',
                'icon' => 'fa-chart-line'
            ]
        ]
    ];
    if (file_put_contents($portal_config_file, json_encode($default_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        output("[✓] portal_config.json を作成しました", $is_cli, 'success');
    } else {
        output("[✗] portal_config.json の作成に失敗しました", $is_cli, 'error');
        $has_errors = true;
    }
} else {
    output("[·] portal_config.json は既に存在します", $is_cli);
}

// 4. yoSSO セットアップの実行確認
output("", $is_cli);
output("[依存関係]", $is_cli);

$yosso_setup = dirname(__DIR__) . '/yoSSO/setup.php';
$yosso_data = dirname(__DIR__) . '/yoSSO/data/users.json';

if (file_exists($yosso_data)) {
    output("[·] yoSSO は既にセットアップ済みです", $is_cli);
} elseif (file_exists($yosso_setup)) {
    output("[!] yoSSO がセットアップされていません", $is_cli, 'warning');
    output("    次のコマンドを実行してください: php ../yoSSO/setup.php", $is_cli);
    $has_warnings = true;
} else {
    output("[!] yoSSO が見つかりません", $is_cli, 'warning');
    $has_warnings = true;
}

// 結果サマリー
output("", $is_cli);
output("=================================", $is_cli);

if ($has_errors) {
    output("  セットアップがエラーで完了しました", $is_cli, 'error');
} elseif ($has_warnings) {
    output("  セットアップが警告付きで完了しました", $is_cli, 'warning');
} else {
    output("  セットアップ完了！", $is_cli, 'success');
}

output("=================================", $is_cli);
output("", $is_cli);

if ($has_warnings) {
    output("上記の警告を確認してください。", $is_cli, 'warning');
}

if (!$has_errors) {
    output("", $is_cli);
    output("次のステップ:", $is_cli);
    output("  1. yoSSO でユーザーを作成/確認", $is_cli);
    output("  2. portal_config.json でポータル設定をカスタマイズ", $is_cli);
}

if (!$is_cli) {
    echo "</pre></body></html>";
}
