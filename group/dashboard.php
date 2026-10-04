<?php
require_once __DIR__ . '/../php/db.php';
$pageTitle = "Group Dashboard";
require_once __DIR__ . '/header.php';

$groupId = $group['id'] ?? 0;

// Stats
$today = date('Y-m-d');
$availCountStmt = $pdo->prepare("SELECT COUNT(*) FROM food_listings WHERE status = 'Available' AND available_quantity > 0 AND expiry_date >= ?");
$availCountStmt->execute([$today]);
$availCount = $availCountStmt->fetchColumn();

$pendingCount = $pdo->prepare("SELECT COUNT(*) FROM food_requests WHERE group_id = ? AND status = 'Pending'");
$pendingCount->execute([$groupId]);
$countPending = $pendingCount->fetchColumn();

$acceptedCount = $pdo->prepare("SELECT COUNT(*) FROM food_requests WHERE group_id = ? AND status = 'Accepted'");
$acceptedCount->execute([$groupId]);
$countAccepted = $acceptedCount->fetchColumn();

$completedCount = $pdo->prepare("SELECT COUNT(*) FROM food_requests WHERE group_id = ? AND status = 'Completed'");
$completedCount->execute([$groupId]);
$countCompleted = $completedCount->fetchColumn();

// Recent requests made by this group
$recentStmt = $pdo->prepare("SELECT r.*, f.food_name, f.quantity_unit, f.price, p.business_name, p.phone as provider_phone, p.address as provider_address, p.city as provider_city 
    FROM food_requests r 
    JOIN food_listings f ON r.food_id = f.id 
    JOIN food_providers p ON f.provider_id = p.id 
    WHERE r.group_id = ? 
    ORDER BY r.id DESC LIMIT 4");
$recentStmt->execute([$groupId]);
$recentRequests = $recentStmt->fetchAll();
?>

<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Welcome, <?= escape($group['group_name'] ?? 'Social Partner') ?></h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
            Discover available surplus food lots and coordinate timely distribution for communities in need.
        </p>
    </div>
    <div>
        <a href="/group/food-availability.php" class="btn btn-primary">
            🍲 Browse Available Food
        </a>
    </div>
</div>

<?php render_flash('group_success'); ?>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-title">Available Food Listings</div>
        <div class="stat-card-val tabular-nums" style="color: var(--color-primary);"><?= $availCount ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title">Pending Requests</div>
        <div class="stat-card-val tabular-nums" style="color: var(--color-warning);"><?= $countPending ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title">Accepted / Ready Pickups</div>
        <div class="stat-card-val tabular-nums" style="color: var(--color-success);"><?= $countAccepted ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title">Completed Distributions</div>
        <div class="stat-card-val tabular-nums" style="color: #334155;"><?= $countCompleted ?></div>
    </div>
</div>

<!-- Recent Requests Activity -->
<div style="margin-top: 2rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h2 style="font-size: 1.25rem;">My Recent Food Requests</h2>
        <a href="/group/my-requests.php" style="font-size: 0.875rem; font-weight: 600;">View All Requests →</a>
    </div>

    <?php if (!empty($recentRequests)): ?>
        <div class="table-wrap">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Food Item</th>
                            <th>Food Provider</th>
                            <th>Requested Qty</th>
                            <th>Scheduled Slot</th>
                            <th>Status</th>
                            <th>Details & Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentRequests as $req): ?>
                            <tr>
                                <td><strong><?= escape($req['food_name']) ?></strong></td>
                                <td><?= escape($req['business_name']) ?> (<?= escape($req['provider_city']) ?>)</td>
                                <td class="tabular-nums"><strong><?= floatval($req['requested_quantity']) ?></strong> <?= escape($req['quantity_unit']) ?></td>
                                <td><?= date('d M Y', strtotime($req['requested_date'])) ?> at <?= date('h:i A', strtotime($req['requested_time'])) ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower($req['status']) ?>">
                                        <?= escape($req['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($req['status'] === 'Accepted'): ?>
                                        <span style="font-size:0.8rem; color:var(--color-success); font-weight:700;">
                                            Ready for Pickup (📞 <?= escape($req['provider_phone']) ?>)
                                        </span>
                                    <?php elseif ($req['status'] === 'Pending'): ?>
                                        <span style="font-size:0.8rem; color:var(--color-text-muted);">
                                            Waiting for Provider
                                        </span>
                                    <?php elseif ($req['status'] === 'Rejected'): ?>
                                        <span style="font-size:0.8rem; color:var(--color-danger);">
                                            Request Rejected
                                        </span>
                                    <?php else: ?>
                                        <span style="font-size:0.8rem; color:var(--color-text-muted);">
                                            <?= escape($req['status']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <h3 class="empty-state-title">You don't have any requests yet.</h3>
            <p class="empty-state-desc">Explore active food lots from partner kitchens and request what your group needs.</p>
            <a href="/group/food-availability.php" class="btn btn-primary btn-sm">Find Available Food</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
