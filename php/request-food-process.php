<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

check_auth(['group']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /group/food-availability.php");
    exit;
}

$groupId = $_SESSION['group_id'] ?? null;
if (!$groupId) {
    $g = get_current_profile($pdo);
    $groupId = $g['id'] ?? null;
}

if (!$groupId) {
    die("Social working group profile not found.");
}

$food_id = intval($_POST['food_id'] ?? 0);
$requested_quantity = floatval($_POST['requested_quantity'] ?? 0);
$requested_date = trim($_POST['requested_date'] ?? '');
$requested_time = trim($_POST['requested_time'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($food_id <= 0 || $requested_quantity <= 0 || empty($requested_date) || empty($requested_time)) {
    set_flash('group_error', 'Please provide valid requested quantity, date, and pickup time.', 'error');
    header("Location: /group/food-availability.php");
    exit;
}

// Fetch food listing to check availability and constraints
$foodStmt = $pdo->prepare("SELECT * FROM food_listings WHERE id = ?");
$foodStmt->execute([$food_id]);
$food = $foodStmt->fetch();

if (!$food || $food['status'] !== 'Available') {
    set_flash('group_error', 'This food listing is no longer available.', 'error');
    header("Location: /group/food-availability.php");
    exit;
}

if ($requested_quantity > $food['available_quantity']) {
    set_flash('group_error', "Requested quantity ({$requested_quantity} {$food['quantity_unit']}) exceeds currently available amount ({$food['available_quantity']} {$food['quantity_unit']}).", 'error');
    header("Location: /group/food-availability.php");
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO food_requests 
        (food_id, group_id, requested_quantity, requested_date, requested_time, message, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->execute([$food_id, $groupId, $requested_quantity, $requested_date, $requested_time, $message]);
    $newRequestId = $pdo->lastInsertId();

    // Record action and sync request to Supabase backend
    require_once __DIR__ . '/supabase.php';
    supabase_record_action('food_requested', [
        'request_id' => $newRequestId,
        'food_id' => $food_id,
        'food_name' => $food['food_name'],
        'group_id' => $groupId,
        'requested_quantity' => $requested_quantity,
        'quantity_unit' => $food['quantity_unit'],
        'requested_date' => $requested_date,
        'requested_time' => $requested_time,
        'message' => $message
    ], 'food_request', $newRequestId);
    supabase_sync_food_request([
        'id' => $newRequestId,
        'food_id' => $food_id,
        'group_id' => $groupId,
        'requested_quantity' => $requested_quantity,
        'requested_date' => $requested_date,
        'requested_time' => $requested_time,
        'status' => 'Pending',
        'message' => $message
    ]);

    set_flash('group_success', "Request for '{$food['food_name']}' has been sent to the Food Provider! You can track its status in My Requests.", 'success');
    header("Location: /group/my-requests.php");
    exit;
} catch (Exception $e) {
    set_flash('group_error', 'Failed to submit request: ' . $e->getMessage(), 'error');
    header("Location: /group/food-availability.php");
    exit;
}
