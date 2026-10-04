<?php
require_once __DIR__ . '/../php/db.php';
$pageTitle = "Food Listings Management";
require_once __DIR__ . '/header.php';

// Handle Delete or Status Change
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $foodId = intval($_GET['id']);

    require_once __DIR__ . '/../php/supabase.php';
    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM food_listings WHERE id = ?")->execute([$foodId]);
        set_flash('admin_success', "Food listing #{$foodId} deleted.");
        supabase_record_action('admin_food_deleted', ['food_id' => $foodId], 'food_listing', $foodId);
        supabase_request("food_listings?id=eq.{$foodId}", 'DELETE');
    } elseif ($action === 'disable') {
        $pdo->prepare("UPDATE food_listings SET status = 'Unavailable' WHERE id = ?")->execute([$foodId]);
        set_flash('admin_success', "Food listing #{$foodId} marked as Unavailable.");
        supabase_record_action('admin_food_status_changed', ['food_id' => $foodId, 'status' => 'Unavailable'], 'food_listing', $foodId);
        supabase_update_food_listing_status($foodId, 'Unavailable');
    } elseif ($action === 'enable') {
        $pdo->prepare("UPDATE food_listings SET status = 'Available' WHERE id = ?")->execute([$foodId]);
        set_flash('admin_success', "Food listing #{$foodId} marked as Available.");
        supabase_record_action('admin_food_status_changed', ['food_id' => $foodId, 'status' => 'Available'], 'food_listing', $foodId);
        supabase_update_food_listing_status($foodId, 'Available');
    }
    header("Location: /admin/foods.php");
    exit;
}

$search = trim($_GET['q'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$sql = "SELECT f.*, p.business_name, p.city as provider_city, c.name as category_name,
        (SELECT COUNT(*) FROM food_requests WHERE food_id = f.id) as request_count
        FROM food_listings f 
        JOIN food_providers p ON f.provider_id = p.id 
        JOIN food_categories c ON f.category_id = c.id 
        WHERE 1=1";

$params = [];
if (!empty($statusFilter)) {
    $sql .= " AND f.status = ?";
    $params[] = $statusFilter;
}
if (!empty($search)) {
    $sql .= " AND (f.food_name LIKE ? OR p.business_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY f.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$foods = $stmt->fetchAll();
?>

<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Surplus Food Listings</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
            Monitor all posted surplus batches, safety expiry times, and disable inappropriate items.
        </p>
    </div>
</div>

<?php render_flash('admin_success'); ?>

<div class="form-card" style="padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
    <form action="/admin/foods.php" method="GET" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end;">
        <div style="flex:2; min-width:200px;">
            <label class="form-label" style="font-size:0.8rem;">Search listing or kitchen</label>
            <input type="text" name="q" class="form-input" placeholder="e.g. Rice, Annapurna..." value="<?= escape($search) ?>">
        </div>

        <div style="flex:1; min-width:160px;">
            <label class="form-label" style="font-size:0.8rem;">Filter by Status</label>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="Available" <?= ($statusFilter === 'Available') ? 'selected' : '' ?>>Available</option>
                <option value="Accepted" <?= ($statusFilter === 'Accepted') ? 'selected' : '' ?>>Accepted</option>
                <option value="Unavailable" <?= ($statusFilter === 'Unavailable') ? 'selected' : '' ?>>Unavailable</option>
                <option value="Completed" <?= ($statusFilter === 'Completed') ? 'selected' : '' ?>>Completed</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-primary" style="height: 42px;">Filter</button>
            <?php if (!empty($search) || !empty($statusFilter)): ?>
                <a href="/admin/foods.php" class="btn btn-secondary" style="height: 42px;">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="table-wrap">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Food Item & Category</th>
                    <th>Food Provider</th>
                    <th>Available / Total</th>
                    <th>Price</th>
                    <th>Available Window</th>
                    <th>Safe Expiry</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($foods as $f): ?>
                    <tr>
                        <td class="tabular-nums">#<?= $f['id'] ?></td>
                        <td>
                            <strong><?= escape($f['food_name']) ?></strong><br>
                            <span style="font-size:0.75rem; color:var(--color-text-muted);">
                                <?= escape($f['category_name']) ?> · <span style="font-weight:600;"><?= escape($f['food_type']) ?></span>
                            </span>
                        </td>
                        <td>
                            <strong><?= escape($f['business_name']) ?></strong><br>
                            <span style="font-size:0.75rem; color:var(--color-text-muted);"><?= escape($f['provider_city']) ?></span>
                        </td>
                        <td class="tabular-nums">
                            <strong><?= floatval($f['available_quantity']) ?></strong> / <?= floatval($f['quantity']) ?> <?= escape($f['quantity_unit']) ?>
                        </td>
                        <td class="tabular-nums" style="font-weight:700; color:var(--color-primary);">
                            ₹<?= floatval($f['price']) ?>
                        </td>
                        <td style="font-size:0.8rem;">
                            <?= date('d M', strtotime($f['available_date'])) ?> 
                            (<?= date('h:i A', strtotime($f['available_start_time'])) ?> - <?= date('h:i A', strtotime($f['available_end_time'])) ?>)
                        </td>
                        <td style="font-size:0.8rem;">
                            <?= date('d M', strtotime($f['expiry_date'])) ?> 
                            (<?= date('h:i A', strtotime($f['expiry_time'])) ?>)
                        </td>
                        <td>
                            <span class="badge badge-<?= strtolower($f['status']) ?>">
                                <?= escape($f['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div style="display:flex; gap:0.35rem;">
                                <?php if ($f['status'] === 'Available'): ?>
                                    <a href="/admin/foods.php?action=disable&id=<?= $f['id'] ?>" class="btn btn-sm btn-secondary" title="Mark unavailable">
                                        Disable
                                    </a>
                                <?php else: ?>
                                    <a href="/admin/foods.php?action=enable&id=<?= $f['id'] ?>" class="btn btn-sm btn-primary" title="Mark available">
                                        Enable
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
