<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /login.php");
    exit;
}

$identity = trim($_POST['identity'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($identity) || empty($password)) {
    set_flash('login_error', 'Please enter your username/email and password.', 'error');
    header("Location: /login.php");
    exit;
}

// Find user by either username or email (case-insensitive)
$stmt = $pdo->prepare("SELECT * FROM users WHERE (LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?)) AND status = 'active' LIMIT 1");
$stmt->execute([$identity, $identity]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    set_flash('login_error', 'Invalid username/email or password.', 'error');
    header("Location: /login.php");
    exit;
}

// User authenticated successfully! Set session
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];

// Record login action to Supabase
require_once __DIR__ . '/supabase.php';
supabase_record_action('user_login', [
    'user_id' => $user['id'],
    'username' => $user['username'],
    'role' => $user['role'],
    'login_time' => date('c')
], 'auth', $user['id']);

// Session token for fallback in cross-origin iframes
$sid = session_id();
$sidQuery = !empty($sid) ? '?sid=' . urlencode($sid) : '';

// Ensure cookie is flushed with SameSite=None; Secure; Partitioned
if ($sid && !headers_sent()) {
    header("Set-Cookie: PHPSESSID=" . rawurlencode($sid) . "; Path=/; Max-Age=2592000; Secure; HttpOnly; SameSite=None; Partitioned", false);
}

// Fetch specific profile id
if ($user['role'] === 'provider') {
    $pStmt = $pdo->prepare("SELECT id, business_name FROM food_providers WHERE user_id = ?");
    $pStmt->execute([$user['id']]);
    $provider = $pStmt->fetch();
    if ($provider) {
        $_SESSION['provider_id'] = $provider['id'];
        $_SESSION['display_name'] = $provider['business_name'];
    }
    header("Location: /provider/dashboard.php" . $sidQuery);
    exit;
} elseif ($user['role'] === 'group') {
    $gStmt = $pdo->prepare("SELECT id, group_name FROM social_working_groups WHERE user_id = ?");
    $gStmt->execute([$user['id']]);
    $group = $gStmt->fetch();
    if ($group) {
        $_SESSION['group_id'] = $group['id'];
        $_SESSION['display_name'] = $group['group_name'];
    }
    header("Location: /group/dashboard.php" . $sidQuery);
    exit;
} elseif ($user['role'] === 'admin') {
    $_SESSION['display_name'] = 'Administrator';
    header("Location: /admin/dashboard.php" . $sidQuery);
    exit;
} else {
    header("Location: /" . $sidQuery);
    exit;
}
