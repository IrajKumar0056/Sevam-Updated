<?php
require_once __DIR__ . '/../php/db.php';
$pageTitle = "My Food Requests";
require_once __DIR__ . '/header.php';

$groupId = $group['id'] ?? 0;

$stmt = $pdo->prepare("SELECT r.*, f.food_name, f.quantity_unit, f.price, f.food_description, f.pickup_info,
        p.business_name, p.owner_name, p.phone as provider_phone, p.address as provider_address, p.city as provider_city, p.fssai_status, p.fssai_number
        FROM food_requests r
        JOIN food_listings f ON r.food_id = f.id
        JOIN food_providers p ON f.provider_id = p.id
        WHERE r.group_id = ?
        ORDER BY r.id DESC");
$stmt->execute([$groupId]);
$requests = $stmt->fetchAll();
?>

<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">My Surplus Food Requests</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
            Track approval status, confirmed pickup logistics, and contact details for requested meals.
        </p>
    </div>
    <div>
        <a href="/group/food-availability.php" class="btn btn-primary">
            Find More Food
        </a>
    </div>
</div>

<?php render_flash('group_success'); ?>

<?php if (!empty($requests)): ?>
    <div style="display:flex; flex-direction:column; gap:1.5rem;">
        <?php foreach ($requests as $req): ?>
            <div class="card" style="padding: 1.5rem; border-left: 4px solid <?= ($req['status'] === 'Accepted') ? '#15803d' : (($req['status'] === 'Pending') ? '#b45309' : '#b91c1c') ?>;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:0.5rem; margin-bottom:1rem;">
                    <div>
                        <div style="font-size:0.8rem; color:var(--color-text-muted);">
                            Request #<?= $req['id'] ?> · Submitted on <?= date('d M Y, h:i A', strtotime($req['created_at'])) ?>
                        </div>
                        <h3 style="font-size:1.3rem; margin-top:0.25rem;"><?= escape($req['food_name']) ?></h3>
                        <div style="font-size:0.9rem; color:var(--color-text-muted); margin-top:0.2rem;">
                            Offered by <strong><?= escape($req['business_name']) ?></strong> (<?= escape($req['provider_city']) ?>)
                        </div>
                    </div>

                    <div style="text-align: right;">
                        <span class="badge badge-<?= strtolower($req['status']) ?>" style="font-size:0.85rem; padding:0.35rem 0.75rem;">
                            <?= escape($req['status']) ?>
                        </span>
                    </div>
                </div>

                <div class="cards-grid-3" style="gap:1rem; margin-bottom:1rem; padding:0.75rem; background:var(--color-surface-subtle); border-radius:var(--radius-sm); font-size:0.875rem;">
                    <div>
                        <span style="color:var(--color-text-muted); font-size:0.75rem; display:block;">Requested Quantity:</span>
                        <strong class="tabular-nums" style="font-size:1rem; color:var(--color-text-main);">
                            <?= floatval($req['requested_quantity']) ?> <?= escape($req['quantity_unit']) ?>
                        </strong>
                    </div>
                    <div>
                        <span style="color:var(--color-text-muted); font-size:0.75rem; display:block;">Requested Pickup Slot:</span>
                        <strong style="color:var(--color-text-main);">
                            <?= date('d M Y', strtotime($req['requested_date'])) ?> at <?= date('h:i A', strtotime($req['requested_time'])) ?>
                        </strong>
                    </div>
                    <div>
                        <span style="color:var(--color-text-muted); font-size:0.75rem; display:block;">Est. Recovery Price:</span>
                        <strong class="tabular-nums" style="font-size:1rem; color:var(--color-primary);">
                            ₹<?= floatval($req['price']) ?>
                        </strong>
                    </div>
                </div>

                <!-- Status Specific Detailed Information -->
                <?php if ($req['status'] === 'Accepted'): ?>
                    <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:var(--radius-sm); padding:1.25rem; margin-top:0.5rem;">
                        <div style="display:flex; align-items:center; gap:0.5rem; color:#15803d; font-weight:700; font-size:0.95rem; margin-bottom:0.75rem;">
                            <span>✅ Request Approved — Ready for Scheduled Collection</span>
                        </div>
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; font-size:0.875rem; color:#1e293b;">
                            <div>
                                <strong>Contact Person:</strong> <?= escape($req['owner_name']) ?><br>
                                <strong>Direct Mobile:</strong> <a href="tel:<?= escape($req['provider_phone']) ?>" style="font-weight:700; text-decoration:underline;"><?= escape($req['provider_phone']) ?></a>
                            </div>
                            <div>
                                <strong>Collection Point Address:</strong><br>
                                <?= escape($req['provider_address']) ?>, <?= escape($req['provider_city']) ?>
                            </div>
                            <div>
                                <strong>Pickup Instructions:</strong><br>
                                <?= escape($req['pickup_info']) ?>
                            </div>
                        </div>
                    </div>
                <?php elseif ($req['status'] === 'Rejected'): ?>
                    <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:var(--radius-sm); padding:1rem; margin-top:0.5rem; color:#991b1b; font-size:0.875rem;">
                        <strong>Request Rejected:</strong> The food provider was unable to accommodate this request due to timing or inventory limits. You can explore other active surplus listings.
                    </div>
                <?php else: ?>
                    <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:var(--radius-sm); padding:0.85rem; margin-top:0.5rem; color:#92400e; font-size:0.85rem;">
                        <strong>Waiting for Provider:</strong> The food provider has been notified and will confirm the collection slot shortly.
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">📋</div>
        <h3 class="empty-state-title">You don't have any requests yet.</h3>
        <p class="empty-state-desc">Find available surplus meals from community kitchens and submit your first request.</p>
        <a href="/group/food-availability.php" class="btn btn-primary btn-sm">Explore Available Food</a>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
