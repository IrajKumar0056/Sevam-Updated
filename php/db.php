<?php
// Centralized Database Connection & Session Management for Sevam Platform
if (session_status() === PHP_SESSION_NONE) {
    // 1. Support session ID passed in URL or POST parameter for cross-origin iframes
    $sid = $_REQUEST['sid'] ?? $_REQUEST['PHPSESSID'] ?? null;
    if ($sid && preg_match('/^[a-zA-Z0-9,-]{16,128}$/', $sid)) {
        session_id($sid);
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
               (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    // 2. Configure session cookie
    // When running inside HTTPS cross-site iframe (AI Studio), SameSite=None and Secure are required.
    // When running locally on HTTP (localhost/XAMPP), Secure must be false and SameSite=Lax.
    session_set_cookie_params([
        'lifetime' => 86400 * 30,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => $isHttps ? 'None' : 'Lax'
    ]);

    @session_start();

    // 3. Emit Partitioned cookie header for Chrome CHIPS partitioned storage in iframes if on HTTPS
    $sessId = session_id();
    if ($sessId && !headers_sent() && $isHttps) {
        header("Set-Cookie: PHPSESSID=" . rawurlencode($sessId) . "; Path=/; Max-Age=2592000; Secure; HttpOnly; SameSite=None; Partitioned", false);
    }
}

$db_host = getenv('DB_HOST') ?: '127.0.0.1';
$db_port = getenv('DB_PORT') ?: '3306';
$db_name = getenv('DB_NAME') ?: 'sevam';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
$db_socket = getenv('DB_SOCKET') ?: '/run/mysqld/mysqld.sock';

$pdo = null;

// Try UNIX socket first if it exists
if (file_exists($db_socket) && extension_loaded('pdo_mysql')) {
    try {
        $pdo = new PDO("mysql:unix_socket={$db_socket};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 2,
        ]);
    } catch (Throwable $e) {
        // Fall back to TCP or SQLite
    }
}

// Try MySQL TCP connection
if (!$pdo && extension_loaded('pdo_mysql')) {
    try {
        $pdo = new PDO("mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 2,
        ]);
    } catch (Throwable $ex) {
        // Fall back to SQLite
    }
}

// Fallback to SQLite (ensures reliable standalone zero-config deployment in Cloud Run and serverless containers)
if (!$pdo) {
    try {
        $sqliteFile = __DIR__ . '/../database/sevam.sqlite';
        if (!file_exists($sqliteFile) || filesize($sqliteFile) < 1024) {
            $initScript = __DIR__ . '/../database/init_sqlite.php';
            if (file_exists($initScript)) {
                @include_once $initScript;
            }
        }
        $pdo = new PDO("sqlite:{$sqliteFile}", null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->exec("PRAGMA journal_mode = WAL;");
        $pdo->exec("PRAGMA busy_timeout = 5000;");
        $pdo->exec("PRAGMA foreign_keys = ON;");

        // Polyfill MySQL-compatible date/time functions for SQLite
        $pdo->sqliteCreateFunction('CURDATE', function() {
            return date('Y-m-d');
        }, 0);
        $pdo->sqliteCreateFunction('NOW', function() {
            return date('Y-m-d H:i:s');
        }, 0);
        $pdo->sqliteCreateFunction('CURRENT_DATE', function() {
            return date('Y-m-d');
        }, 0);
        $pdo->sqliteCreateFunction('CURRENT_TIME', function() {
            return date('H:i:s');
        }, 0);
    } catch (Throwable $sqlEx) {
        die("<div style='font-family:sans-serif;padding:2rem;color:#b91c1c;background:#fee2e2;border-radius:8px;'><h3>Database Connection Error</h3><p>Could not connect to database: " . htmlspecialchars($sqlEx->getMessage()) . "</p></div>");
    }
}

// Multibyte string polyfills (for environments without ext-mbstring)
if (!function_exists('mb_strlen')) {
    function mb_strlen($string, $encoding = null) {
        return strlen((string)$string);
    }
}

if (!function_exists('mb_substr')) {
    function mb_substr($string, $start, $length = null, $encoding = null) {
        $str = (string)$string;
        if ($length === null) {
            return substr($str, $start);
        }
        return substr($str, $start, $length);
    }
}

if (!function_exists('mb_strimwidth')) {
    function mb_strimwidth($string, $start, $width, $trimmarker = '', $encoding = null) {
        $str = (string)($string ?? '');
        if (strlen($str) <= $width) {
            return $str;
        }
        $marker = (string)$trimmarker;
        $markerLen = strlen($marker);
        $cutLength = max(0, $width - $markerLen);
        return substr($str, $start, $cutLength) . $marker;
    }
}

function truncate_text($string, $width = 90, $trimmarker = '...') {
    $str = (string)($string ?? '');
    if (strlen($str) <= $width) {
        return $str;
    }
    $cutLength = max(0, $width - strlen($trimmarker));
    return substr($str, 0, $cutLength) . $trimmarker;
}

// Sanitize & Escape Output
function escape($val) {
    return htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8');
}

// Authentication & Role-Based Access Control
function check_auth($allowed_roles = []) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: /login.php?msg=unauthorized");
        exit;
    }
    if (!empty($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: /login.php?msg=forbidden");
        exit;
    }
}

// Get Logged In User's Specific Profile Record
function get_current_profile($pdo) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        return null;
    }
    $role = $_SESSION['role'];
    $userId = $_SESSION['user_id'];

    if ($role === 'provider') {
        $stmt = $pdo->prepare("SELECT p.*, u.email, u.username FROM food_providers p JOIN users u ON p.user_id = u.id WHERE u.id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } elseif ($role === 'group') {
        $stmt = $pdo->prepare("SELECT g.*, u.email, u.username FROM social_working_groups g JOIN users u ON g.user_id = u.id WHERE u.id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } elseif ($role === 'admin') {
        $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    return null;
}
