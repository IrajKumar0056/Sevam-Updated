<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /register.php");
    exit;
}

$role = trim($_POST['role'] ?? '');

if ($role !== 'provider' && $role !== 'group') {
    set_flash('register_error', 'Invalid role selected.', 'error');
    header("Location: /register.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$state = trim($_POST['state'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Basic validations
if (empty($username) || empty($email) || empty($password) || empty($phone) || empty($city)) {
    set_flash('register_error', 'All required fields must be filled.', 'error');
    header("Location: /register.php?role=" . urlencode($role));
    exit;
}

if ($password !== $confirm_password) {
    set_flash('register_error', 'Passwords do not match.', 'error');
    header("Location: /register.php?role=" . urlencode($role));
    exit;
}

if (strlen($password) < 6) {
    set_flash('register_error', 'Password must be at least 6 characters long.', 'error');
    header("Location: /register.php?role=" . urlencode($role));
    exit;
}

// Check existing user
$checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
$checkStmt->execute([$username, $email]);
if ($checkStmt->fetch()) {
    set_flash('register_error', 'Username or email is already registered. Please choose another or login.', 'error');
    header("Location: /register.php?role=" . urlencode($role));
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

try {
    $pdo->beginTransaction();

    // 1. Insert into users
    $userStmt = $pdo->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, 'active')");
    $userStmt->execute([$username, $email, $hashedPassword, $role]);
    $userId = $pdo->lastInsertId();

    if ($role === 'provider') {
        $business_name = trim($_POST['business_name'] ?? '');
        $owner_name = trim($_POST['owner_name'] ?? '');
        $business_type = trim($_POST['business_type'] ?? 'Restaurant');
        $fssai_status = trim($_POST['fssai_status'] ?? 'Not Certified');
        $fssai_number = ($fssai_status === 'Certified') ? trim($_POST['fssai_number'] ?? '') : null;

        if (empty($business_name) || empty($owner_name)) {
            throw new Exception("Business and contact person names are required.");
        }

        $provStmt = $pdo->prepare("INSERT INTO food_providers (user_id, business_name, owner_name, phone, address, city, state, business_type, fssai_status, fssai_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $provStmt->execute([$userId, $business_name, $owner_name, $phone, $address, $city, $state, $business_type, $fssai_status, $fssai_number]);
        $providerProfileId = intval($pdo->lastInsertId());
    } else { // group
        $group_name = trim($_POST['group_name'] ?? '');
        $representative_name = trim($_POST['representative_name'] ?? '');
        $organization_type = trim($_POST['organization_type'] ?? 'Registered NGO');
        $darpan_id = trim($_POST['darpan_id'] ?? '');

        if (empty($group_name) || empty($representative_name)) {
            throw new Exception("Organization and representative names are required.");
        }

        $grpStmt = $pdo->prepare("INSERT INTO social_working_groups (user_id, group_name, representative_name, phone, address, city, state, organization_type, darpan_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $grpStmt->execute([$userId, $group_name, $representative_name, $phone, $address, $city, $state, $organization_type, !empty($darpan_id) ? $darpan_id : null]);
        $groupProfileId = intval($pdo->lastInsertId());
    }

    $pdo->commit();

    // Synchronize action and entity details to Supabase backend
    require_once __DIR__ . '/supabase.php';
    supabase_record_action('user_registered', [
        'user_id' => $userId,
        'username' => $username,
        'email' => $email,
        'role' => $role,
        'phone' => $phone,
        'address' => $address,
        'city' => $city,
        'state' => $state,
        'organization_name' => ($role === 'provider') ? $business_name : $group_name
    ], 'user', $userId);

    supabase_sync_user([
        'id' => $userId,
        'username' => $username,
        'email' => $email,
        'role' => $role,
        'status' => 'active'
    ]);

    if ($role === 'provider') {
        supabase_sync_provider([
            'id' => $providerProfileId,
            'user_id' => $userId,
            'business_name' => $business_name,
            'owner_name' => $owner_name,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'business_type' => $business_type,
            'fssai_status' => $fssai_status,
            'fssai_number' => $fssai_number
        ]);
    } else {
        supabase_sync_group([
            'id' => $groupProfileId,
            'user_id' => $userId,
            'group_name' => $group_name,
            'representative_name' => $representative_name,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'organization_type' => $organization_type,
            'darpan_id' => !empty($darpan_id) ? $darpan_id : null
        ]);
    }

    set_flash('login_success', 'Registration successful! You can now log in to your dashboard.', 'success');
    header("Location: /login.php");
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('register_error', 'Registration failed: ' . $e->getMessage(), 'error');
    header("Location: /register.php?role=" . urlencode($role));
    exit;
}
