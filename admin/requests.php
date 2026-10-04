<?php
require_once __DIR__ . '/../php/db.php';
$pageTitle = "Food Requests";
require_once __DIR__ . '/header.php';

$statusFilter = trim($_GET['status'] ?? '');

$sql = "SELECT r.*, f.food_name, f.quantity_unit, f.price, 
        p.business_name, p.phone as provider_phone,
        g.group_name, g.representative_name, g.phone as group_phone
        FROM food_requests r 
        JOIN food_listings f ON r.food_id = f.id 
        JOIN food_providers p ON f.provider_id = p.id 
        JOIN social_working_groups g ON r.group_id = g.id 
        WHERE 1=1";

$params = [];
if (!empty($statusFilter)) {
    $sql .= " AND r.status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY r.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();
?>

<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Platform Food Requests</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
            Full ledger of distribution requests between social groups and provider kitchens.
        </p>
    </div>
</div>

<?php render_flash('request_success'); ?>
<?php render_flash('request_error'); ?>

<div class="form-card" style="padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
    <form action="/admin/requests.php" method="GET" style="display:flex; gap:1rem; align-items:flex-end;">
        <div style="flex:1; max-width:260px;">
            <label class="form-label" style="font-size:0.8rem;">Filter by Status</label>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="Pending" <?= ($statusFilter === 'Pending') ? 'selected' : '' ?>>Pending</option>
                <option value="Accepted" <?= ($statusFilter === 'Accepted') ? 'selected' : '' ?>>Accepted</option>
                <option value="Rejected" <?= ($statusFilter === 'Rejected') ? 'selected' : '' ?>>Rejected</option>
                <option value="Completed" <?= ($statusFilter === 'Completed') ? 'selected' : '' ?>>Completed</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-primary" style="height: 42px;">Filter</button>
            <?php if (!empty($statusFilter)): ?>
                <a href="/admin/requests.php" class="btn btn-secondary" style="height: 42px;">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="table-wrap">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Req #</th>
                    <th>Food Item</th>
                    <th>Food Provider</th>
                    <th>Social Working Group</th>
                    <th>Quantity</th>
                    <th>Requested Slot</th>
                    <th>Submitted On</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                    <tr>
                        <td class="tabular-nums">#<?= $r['id'] ?></td>
                        <td>
                            <strong><?= escape($r['food_name']) ?></strong><br>
                            <span style="font-size:0.75rem; color:var(--color-text-muted);">₹<?= floatval($r['price']) ?></span>
                        </td>
                        <td>
                            <strong><?= escape($r['business_name']) ?></strong><br>
                            <span style="font-size:0.75rem; color:var(--color-text-muted);"><?= escape($r['provider_phone']) ?></span>
                        </td>
                        <td>
                            <strong><?= escape($r['group_name']) ?></strong><br>
                            <span style="font-size:0.75rem; color:var(--color-text-muted);">Rep: <?= escape($r['representative_name']) ?> (<?= escape($r['group_phone']) ?>)</span>
                        </td>
                        <td class="tabular-nums">
                            <strong><?= floatval($r['requested_quantity']) ?></strong> <?= escape($r['quantity_unit']) ?>
                        </td>
                        <td style="font-size:0.8rem;">
                            <?= date('d M Y', strtotime($r['requested_date'])) ?><br>
                            <?= date('h:i A', strtotime($r['requested_time'])) ?>
                        </td>
                        <td class="tabular-nums" style="font-size:0.8rem; color:var(--color-text-muted);">
                            <?= date('d M Y, h:i A', strtotime($r['created_at'])) ?>
                        </td>
                        <td>
                            <span class="badge badge-<?= strtolower($r['status']) ?>">
                                <?= escape($r['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
