<?php
/**
 * mngtools Shared Path Configuration
 * 
 * This file provides a centralized path configuration system.
 * Each tool can include this file and use the paths defined here.
 * 
 * When adminTools folder moves, only this file needs to be updated.
 */

// Detect the root of the mngtools system by looking for shared folder
function mng_detect_root() {
    // Try to find root from current file location first
    $sharedDir = dirname(__FILE__);
    $possibleRoot = dirname($sharedDir);
    
    // Verify this is the root (should contain shared folder)
    if (is_dir($possibleRoot . '/shared') && is_dir($possibleRoot . '/yoAdminBuilder')) {
        return $possibleRoot;
    }
    
    // Fallback: search upward from document root
    $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    $scriptPath = dirname($_SERVER['SCRIPT_FILENAME'] ?? '');
    
    $current = $scriptPath;
    $maxDepth = 10;
    while ($maxDepth-- > 0 && strlen($current) > strlen($docRoot)) {
        if (is_dir($current . '/shared') && is_dir($current . '/yoAdminBuilder')) {
            return $current;
        }
        $current = dirname($current);
    }
    
    return $possibleRoot; // Fallback
}

// Configuration - Edit these when folder structure changes
class MngPaths {
    private static $instance = null;
    private $root;
    private $webRoot;
    private $config = [];
    
    private function __construct() {
        $this->root = mng_detect_root();
        $this->webRoot = $this->calculateWebRoot();
        
        // Load custom config if exists
        $configFile = $this->root . '/shared/paths_config.json';
        if (file_exists($configFile)) {
            $this->config = json_decode(file_get_contents($configFile), true) ?: [];
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function calculateWebRoot() {
        // Calculate web-accessible path from document root
        $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '/');
        $rootPath = realpath($this->root);
        
        if ($docRoot && $rootPath && strpos($rootPath, $docRoot) === 0) {
            return str_replace('\\', '/', substr($rootPath, strlen($docRoot)));
        }
        
        // Fallback: use config or default
        return $this->config['webRoot'] ?? '/mngtools';
    }
    
    /**
     * Get filesystem path
     */
    public function getPath($key) {
        $paths = [
            'root' => $this->root,
            'shared' => $this->root . '/shared',
            'yoAdminBuilder' => $this->root . '/yoAdminBuilder',
            'yoAdminPortal' => $this->root . '/yoAdminPortal',
            'yoSSO' => $this->root . '/yoSSO',
            // adminTools - these can be configured
            'adminTools' => $this->config['adminTools'] ?? $this->root . '/adminTools',
            'mnguser' => ($this->config['adminTools'] ?? $this->root . '/adminTools') . '/mnguser',
            'cms' => ($this->config['adminTools'] ?? $this->root . '/adminTools') . '/cms',
            'webcron' => ($this->config['adminTools'] ?? $this->root . '/adminTools') . '/webcron',
        ];
        
        return $paths[$key] ?? null;
    }
    
    /**
     * Get web-accessible URL path (relative to document root)
     */
    public function getUrl($key) {
        $adminToolsWeb = $this->config['adminToolsWebPath'] ?? $this->webRoot . '/adminTools';
        
        $urls = [
            'root' => $this->webRoot,
            'shared' => $this->webRoot . '/shared',
            'yoAdminBuilder' => $this->webRoot . '/yoAdminBuilder',
            'yoAdminPortal' => $this->webRoot . '/yoAdminPortal',
            'yoSSO' => $this->webRoot . '/yoSSO',
            // adminTools
            'adminTools' => $adminToolsWeb,
            'mnguser' => $adminToolsWeb . '/mnguser',
            'cms' => $adminToolsWeb . '/cms',
            'webcron' => $adminToolsWeb . '/webcron',
        ];
        
        return $urls[$key] ?? null;
    }
    
    /**
     * Get relative path from one location to another
     * @param string $from Key or absolute path
     * @param string $to Key or absolute path
     */
    public function getRelative($from, $to) {
        $fromPath = $this->getUrl($from) ?: $from;
        $toPath = $this->getUrl($to) ?: $to;
        
        $fromParts = explode('/', trim($fromPath, '/'));
        $toParts = explode('/', trim($toPath, '/'));
        
        // Find common prefix
        $common = 0;
        $minLen = min(count($fromParts), count($toParts));
        for ($i = 0; $i < $minLen; $i++) {
            if ($fromParts[$i] === $toParts[$i]) {
                $common++;
            } else {
                break;
            }
        }
        
        // Build relative path
        $upCount = count($fromParts) - $common;
        $relative = str_repeat('../', $upCount) . implode('/', array_slice($toParts, $common));
        
        return $relative ?: '.';
    }
    
    /**
     * Output JavaScript paths object
     */
    public function toJavaScript() {
        $adminToolsWeb = $this->config['adminToolsWebPath'] ?? $this->webRoot . '/adminTools';
        
        return json_encode([
            'root' => $this->webRoot,
            'shared' => $this->webRoot . '/shared',
            'yoAdminBuilder' => $this->webRoot . '/yoAdminBuilder',
            'yoAdminPortal' => $this->webRoot . '/yoAdminPortal',
            'yoSSO' => $this->webRoot . '/yoSSO',
            'adminTools' => $adminToolsWeb,
            'mnguser' => $adminToolsWeb . '/mnguser',
            'cms' => $adminToolsWeb . '/cms',
            'webcron' => $adminToolsWeb . '/webcron',
        ]);
    }
    
    /**
     * Get configuration value
     */
    public function getConfig($key, $default = null) {
        return $this->config[$key] ?? $default;
    }
}

// Helper function for easy access
function mng_path($key) {
    return MngPaths::getInstance()->getPath($key);
}

function mng_url($key) {
    return MngPaths::getInstance()->getUrl($key);
}

function mng_relative($from, $to) {
    return MngPaths::getInstance()->getRelative($from, $to);
}

// Output JS paths variable
function mng_js_paths() {
    return '<script>window.mngPaths = ' . MngPaths::getInstance()->toJavaScript() . ';</script>';
}
