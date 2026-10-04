<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

check_auth(['provider', 'admin']);

$requestId = intval($_POST['request_id'] ?? $_GET['id'] ?? 0);
$action = trim($_POST['action'] ?? $_GET['action'] ?? '');

if ($requestId <= 0 || !in_array($action, ['accept', 'reject', 'complete'])) {
    set_flash('request_error', 'Invalid action or request ID.', 'error');
    header("Location: /provider/requests.php");
    exit;
}

$providerId = $_SESSION['provider_id'] ?? null;
if (!$providerId && $_SESSION['role'] === 'provider') {
    $p = get_current_profile($pdo);
    $providerId = $p['id'] ?? null;
}

// Fetch request with listing details
$stmt = $pdo->prepare("SELECT r.*, f.id as listing_id, f.provider_id, f.food_name, f.available_quantity, f.quantity_unit 
    FROM food_requests r 
    JOIN food_listings f ON r.food_id = f.id 
    WHERE r.id = ?");
$stmt->execute([$requestId]);
$request = $stmt->fetch();

if (!$request) {
    set_flash('request_error', 'Food request not found.', 'error');
    header("Location: /provider/requests.php");
    exit;
}

// Verify ownership if not admin
if ($_SESSION['role'] === 'provider' && $request['provider_id'] != $providerId) {
    set_flash('request_error', 'Unauthorized action.', 'error');
    header("Location: /provider/requests.php");
    exit;
}

try {
    $pdo->beginTransaction();

    if ($action === 'accept') {
        if ($request['status'] === 'Accepted') {
            throw new Exception("This request is already accepted.");
        }

        // Section 24: Food Quantity Protection
        $currentAvailable = floatval($request['available_quantity']);
        $reqQuantity = floatval($request['requested_quantity']);

        if ($reqQuantity > $currentAvailable) {
            throw new Exception("Cannot accept request: Requested quantity ({$reqQuantity} {$request['quantity_unit']}) exceeds remaining available quantity ({$currentAvailable} {$request['quantity_unit']}).");
        }

        $newAvailable = max(0, $currentAvailable - $reqQuantity);
        $newListingStatus = ($newAvailable <= 0) ? 'Unavailable' : 'Available';

        // Update listing available quantity & status
        $updateListing = $pdo->prepare("UPDATE food_listings SET available_quantity = ?, status = ? WHERE id = ?");
        $updateListing->execute([$newAvailable, $newListingStatus, $request['listing_id']]);

        // Update request status to Accepted
        $updateReq = $pdo->prepare("UPDATE food_requests SET status = 'Accepted' WHERE id = ?");
        $updateReq->execute([$requestId]);

        $pdo->commit();
        set_flash('request_success', "Request from Social Working Group accepted! Remaining available food is now {$newAvailable} {$request['quantity_unit']}.", 'success');

    } elseif ($action === 'reject') {
        // If it was previously accepted, return quantity
        if ($request['status'] === 'Accepted') {
            $newAvailable = floatval($request['available_quantity']) + floatval($request['requested_quantity']);
            $updateListing = $pdo->prepare("UPDATE food_listings SET available_quantity = ?, status = 'Available' WHERE id = ?");
            $updateListing->execute([$newAvailable, $request['listing_id']]);
        }

        $updateReq = $pdo->prepare("UPDATE food_requests SET status = 'Rejected' WHERE id = ?");
        $updateReq->execute([$requestId]);

        $pdo->commit();
        set_flash('request_success', "Request has been rejected.", 'info');

    } elseif ($action === 'complete') {
        $updateReq = $pdo->prepare("UPDATE food_requests SET status = 'Completed' WHERE id = ?");
        $updateReq->execute([$requestId]);

        $pdo->commit();
        set_flash('request_success', "Food handover marked as Completed!", 'success');
    }

    // Record action and sync update to Supabase
    require_once __DIR__ . '/supabase.php';
    $statusLabel = ($action === 'accept') ? 'Accepted' : (($action === 'reject') ? 'Rejected' : 'Completed');
    supabase_record_action('request_status_updated', [
        'request_id' => $requestId,
        'listing_id' => $request['listing_id'],
        'new_status' => $statusLabel,
        'action' => $action
    ], 'food_request', $requestId);
    supabase_update_food_request_status($requestId, $statusLabel);
    if ($action === 'accept' || $action === 'reject') {
        supabase_update_food_listing_status($request['listing_id'], $newListingStatus ?? 'Available', $newAvailable ?? null);
    }

    $redirect = ($_SESSION['role'] === 'admin') ? '/admin/requests.php' : '/provider/requests.php';
    header("Location: " . $redirect);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('request_error', $e->getMessage(), 'error');
    $redirect = ($_SESSION['role'] === 'admin') ? '/admin/requests.php' : '/provider/requests.php';
    header("Location: " . $redirect);
    exit;
}
