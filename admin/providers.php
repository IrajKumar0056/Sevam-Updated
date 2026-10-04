<?php
require_once __DIR__ . '/../php/db.php';
$pageTitle = "Food Providers";
require_once __DIR__ . '/header.php';

$stmt = $pdo->query("SELECT p.*, u.username, u.email, u.status as user_status,
    (SELECT COUNT(*) FROM food_listings WHERE provider_id = p.id) as listing_count
    FROM food_providers p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.id DESC");
$providers = $stmt->fetchAll();
?>

<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Registered Food Providers</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
            Commercial kitchens, restaurants, and caterers verified on Sevam.
        </p>
    </div>
</div>

<div class="table-wrap">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Provider ID</th>
                    <th>Business / Kitchen</th>
                    <th>Contact Person</th>
                    <th>Location</th>
                    <th>Business Type</th>
                    <th>FSSAI Status</th>
                    <th>Listings</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($providers as $p): ?>
                    <tr>
                        <td class="tabular-nums">#<?= $p['id'] ?></td>
                        <td>
                            <strong><?= escape($p['business_name']) ?></strong><br>
                            <span style="font-size:0.75rem; color:var(--color-text-muted);"><?= escape($p['email']) ?></span>
                        </td>
                        <td>
                            <?= escape($p['owner_name']) ?><br>
                            <span style="font-size:0.8rem; color:var(--color-text-muted);"><?= escape($p['phone']) ?></span>
                        </td>
                        <td style="font-size:0.85rem;">
                            <?= escape($p['city']) ?>, <?= escape($p['state']) ?><br>
                            <span style="font-size:0.75rem; color:var(--color-text-muted);"><?= escape($p['address']) ?></span>
                        </td>
                        <td style="font-size:0.85rem;"><?= escape($p['business_type']) ?></td>
                        <td>
                            <?php if ($p['fssai_status'] === 'Certified'): ?>
                                <span class="badge badge-accepted">Certified</span><br>
                                <span style="font-size:0.75rem; color:var(--color-text-muted); font-family:monospace;"><?= escape($p['fssai_number']) ?></span>
                            <?php else: ?>
                                <span class="badge badge-pending">Not Certified</span>
                            <?php endif; ?>
                        </td>
                        <td class="tabular-nums">
                            <strong><?= $p['listing_count'] ?></strong> lots
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
