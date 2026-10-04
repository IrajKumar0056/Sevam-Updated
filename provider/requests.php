<?php
require_once __DIR__ . '/../php/db.php';
$pageTitle = "Incoming Food Requests";
require_once __DIR__ . '/header.php';

$providerId = $provider['id'] ?? 0;
$filterFoodId = intval($_GET['food_id'] ?? 0);

// Base query for requests received for this provider's listings
$sql = "SELECT r.*, f.food_name, f.available_quantity, f.quantity_unit, f.price, f.available_date, f.available_start_time, f.available_end_time,
        g.group_name, g.representative_name, g.phone, g.address as group_address, g.city as group_city, g.organization_type, g.darpan_id 
        FROM food_requests r 
        JOIN food_listings f ON r.food_id = f.id 
        JOIN social_working_groups g ON r.group_id = g.id 
        WHERE f.provider_id = ?";

$params = [$providerId];
if ($filterFoodId > 0) {
    $sql .= " AND f.id = ?";
    $params[] = $filterFoodId;
}

$sql .= " ORDER BY r.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();
?>

<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Incoming Food Requests</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
            Review and respond to food requests from verified Social Working Groups.
        </p>
    </div>
    <?php if ($filterFoodId > 0): ?>
        <div>
            <a href="/provider/requests.php" class="btn btn-secondary btn-sm">
                Show All Requests
            </a>
        </div>
    <?php endif; ?>
</div>

<?php render_flash('request_success'); ?>
<?php render_flash('request_error'); ?>

<?php if (!empty($requests)): ?>
    <div class="table-wrap">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Req #</th>
                        <th>Social Working Group</th>
                        <th>Food Item</th>
                        <th>Requested Qty</th>
                        <th>Requested Pickup</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td class="tabular-nums">#<?= $req['id'] ?></td>
                            <td>
                                <strong><?= escape($req['group_name']) ?></strong>
                                <?php if (!empty($req['darpan_id'])): ?>
                                    <span style="background:#e0f2fe; color:#0369a1; border-radius:3px; padding:1px 5px; font-size:10px; font-weight:700;">Verified NGO</span>
                                <?php endif; ?>
                                <br>
                                <span style="font-size:0.8125rem; color:var(--color-text-muted);">
                                    Rep: <?= escape($req['representative_name']) ?> · Ph: <?= escape($req['phone']) ?>
                                </span><br>
                                <span style="font-size:0.775rem; color:var(--color-text-muted);">
                                    <?= escape($req['group_address']) ?>, <?= escape($req['group_city']) ?>
                                </span>
                                <?php if (!empty($req['message'])): ?>
                                    <div style="margin-top:0.35rem; font-size:0.8rem; background:var(--color-surface-subtle); padding:0.35rem 0.5rem; border-radius:4px; font-style:italic;">
                                        "<?= escape($req['message']) ?>"
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= escape($req['food_name']) ?></strong><br>
                                <span style="font-size:0.775rem; color:var(--color-text-muted);">
                                    Currently <?= floatval($req['available_quantity']) ?> <?= escape($req['quantity_unit']) ?> remaining
                                </span>
                            </td>
                            <td class="tabular-nums">
                                <strong><?= floatval($req['requested_quantity']) ?></strong> <?= escape($req['quantity_unit']) ?>
                            </td>
                            <td>
                                <?= date('d M Y', strtotime($req['requested_date'])) ?><br>
                                <span style="font-size:0.8rem; color:var(--color-text-muted);">
                                    <?= date('h:i A', strtotime($req['requested_time'])) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?= strtolower($req['status']) ?>">
                                    <?= escape($req['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($req['status'] === 'Pending'): ?>
                                    <div style="display:flex; gap:0.4rem;">
                                        <form action="/php/request-status-process.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                            <input type="hidden" name="action" value="accept">
                                            <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Accept request for <?= floatval($req['requested_quantity']) ?> <?= escape($req['quantity_unit']) ?>?')">
                                                Accept
                                            </button>
                                        </form>
                                        <form action="/php/request-status-process.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Reject this request?')">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                <?php elseif ($req['status'] === 'Accepted'): ?>
                                    <form action="/php/request-status-process.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                        <input type="hidden" name="action" value="complete">
                                        <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Confirm food handover completed?')">
                                            Mark Handover Complete
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size:0.8rem; color:var(--color-text-muted); font-weight:500;">
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
        <div class="empty-state-icon">📩</div>
        <h3 class="empty-state-title">You don't have any requests yet.</h3>
        <p class="empty-state-desc">Requests submitted by social working groups for your surplus food will be displayed here.</p>
        <a href="/provider/my-food.php" class="btn btn-primary btn-sm">Check Active Listings</a>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
