<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

check_auth(['provider']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /provider/add-food.php");
    exit;
}

$providerId = $_SESSION['provider_id'] ?? null;
if (!$providerId) {
    $p = get_current_profile($pdo);
    $providerId = $p['id'] ?? null;
}

if (!$providerId) {
    die("Provider profile not found.");
}

$food_name = trim($_POST['food_name'] ?? '');
$category_id = intval($_POST['category_id'] ?? 0);
$food_type = trim($_POST['food_type'] ?? 'Veg');
$quantity = floatval($_POST['quantity'] ?? 0);
$quantity_unit = trim($_POST['quantity_unit'] ?? 'kg');
$price = floatval($_POST['price'] ?? 0);
$prep_date = trim($_POST['prep_date'] ?? '');
$available_date = trim($_POST['available_date'] ?? '');
$available_start_time = trim($_POST['available_start_time'] ?? '');
$available_end_time = trim($_POST['available_end_time'] ?? '');
$expiry_date = trim($_POST['expiry_date'] ?? '');
$expiry_time = trim($_POST['expiry_time'] ?? '');
$food_description = trim($_POST['food_description'] ?? '');
$pickup_info = trim($_POST['pickup_info'] ?? '');

// Validation
if (empty($food_name) || $category_id <= 0 || $quantity <= 0 || empty($prep_date) || empty($available_date) || empty($available_start_time) || empty($available_end_time) || empty($expiry_date) || empty($expiry_time) || empty($pickup_info)) {
    set_flash('food_error', 'Please fill in all mandatory fields with valid values.', 'error');
    header("Location: /provider/add-food.php");
    exit;
}

// Basic image handling or default
$image_url = 'images/hero_food_share.jpg';
if (!empty($_FILES['food_image']['name']) && $_FILES['food_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../images/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $ext = strtolower(pathinfo($_FILES['food_image']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
        $fileName = 'food_' . time() . '_' . rand(100, 999) . '.' . $ext;
        if (move_uploaded_file($_FILES['food_image']['tmp_name'], $uploadDir . $fileName)) {
            $image_url = 'images/uploads/' . $fileName;
        }
    }
} elseif ($food_type === 'Veg') {
    $image_url = 'images/provider_kitchen.jpg';
} else {
    $image_url = 'images/community_distrib.jpg';
}

try {
    $stmt = $pdo->prepare("INSERT INTO food_listings 
        (provider_id, category_id, food_name, food_type, quantity, available_quantity, quantity_unit, price, prep_date, available_date, available_start_time, available_end_time, expiry_date, expiry_time, food_description, image_url, pickup_info, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Available')");
    
    $stmt->execute([
        $providerId,
        $category_id,
        $food_name,
        $food_type,
        $quantity,
        $quantity, // Initially available_quantity = quantity
        $quantity_unit,
        $price,
        $prep_date,
        $available_date,
        $available_start_time,
        $available_end_time,
        $expiry_date,
        $expiry_time,
        $food_description,
        $image_url,
        $pickup_info
    ]);

    $newListingId = $pdo->lastInsertId();

    // Sync action and listing to Supabase
    require_once __DIR__ . '/supabase.php';
    supabase_record_action('food_listed', [
        'listing_id' => $newListingId,
        'provider_id' => $providerId,
        'food_name' => $food_name,
        'quantity' => $quantity,
        'quantity_unit' => $quantity_unit,
        'price' => $price,
        'expiry_date' => $expiry_date
    ], 'food_listing', $newListingId);
    supabase_sync_food_listing([
        'id' => $newListingId,
        'provider_id' => $providerId,
        'food_name' => $food_name,
        'food_type' => $food_type,
        'quantity' => $quantity,
        'available_quantity' => $quantity,
        'quantity_unit' => $quantity_unit,
        'price' => $price,
        'available_date' => $available_date,
        'available_start_time' => $available_start_time,
        'available_end_time' => $available_end_time,
        'expiry_date' => $expiry_date,
        'status' => 'Available'
    ]);

    set_flash('provider_success', "Surplus food listing '{$food_name}' successfully added and published for Social Working Groups!", 'success');
    header("Location: /provider/my-food.php");
    exit;
} catch (Exception $e) {
    set_flash('food_error', 'Error adding food listing: ' . $e->getMessage(), 'error');
    header("Location: /provider/add-food.php");
    exit;
}
