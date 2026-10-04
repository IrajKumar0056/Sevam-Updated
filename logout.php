<?php
session_start();
$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'guest';

if ($userId) {
    require_once __DIR__ . '/php/supabase.php';
    supabase_record_action('user_logout', ['user_id' => $userId, 'role' => $role], 'auth', $userId);
}

$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
header("Location: /login.php");
exit;
