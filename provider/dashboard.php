<?php
require_once __DIR__ . '/../php/db.php';
$pageTitle = "Provider Dashboard";
require_once __DIR__ . '/header.php';

$providerId = $provider['id'] ?? 0;

// Calculate Provider Stats from database
$totalListings = $pdo->prepare("SELECT COUNT(*) FROM food_listings WHERE provider_id = ?");
$totalListings->execute([$providerId]);
$countTotal = $totalListings->fetchColumn();

$availListings = $pdo->prepare("SELECT COUNT(*) FROM food_listings WHERE provider_id = ? AND status = 'Available' AND available_quantity > 0");
$availListings->execute([$providerId]);
$countAvailable = $availListings->fetchColumn();

$pendingReqs = $pdo->prepare("SELECT COUNT(*) FROM food_requests r JOIN food_listings f ON r.food_id = f.id WHERE f.provider_id = ? AND r.status = 'Pending'");
$pendingReqs->execute([$providerId]);
$countPending = $pendingReqs->fetchColumn();

$acceptedReqs = $pdo->prepare("SELECT COUNT(*) FROM food_requests r JOIN food_listings f ON r.food_id = f.id WHERE f.provider_id = ? AND r.status = 'Accepted'");
$acceptedReqs->execute([$providerId]);
$countAccepted = $acceptedReqs->fetchColumn();

// Recent requests
$recentReqStmt = $pdo->prepare("SELECT r.*, f.food_name, f.quantity_unit, g.group_name, g.representative_name, g.phone 
    FROM food_requests r 
    JOIN food_listings f ON r.food_id = f.id 
    JOIN social_working_groups g ON r.group_id = g.id 
    WHERE f.provider_id = ? 
    ORDER BY r.id DESC LIMIT 4");
$recentReqStmt->execute([$providerId]);
$recentRequests = $recentReqStmt->fetchAll();

// Recent active listings
$recentFoodStmt = $pdo->prepare("SELECT f.*, c.name as category_name,
    (SELECT COUNT(*) FROM food_requests WHERE food_id = f.id) as request_count 
    FROM food_listings f 
    JOIN food_categories c ON f.category_id = c.id 
    WHERE f.provider_id = ? 
    ORDER BY f.id DESC LIMIT 3");
$recentFoodStmt->execute([$providerId]);
$recentListings = $recentFoodStmt->fetchAll();
?>

<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Welcome, <?= escape($provider['business_name'] ?? 'Provider') ?></h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
            Overview of your surplus food listings and incoming community requests.
        </p>
    </div>
    <div style="display:flex; gap:0.75rem;">
        <a href="/provider/add-food.php" class="btn btn-primary">
            ➕ Add Surplus Food
        </a>
    </div>
</div>

<?php render_flash('provider_success'); ?>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-title">Total Food Listings</div>
        <div class="stat-card-val tabular-nums"><?= $countTotal ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title">Currently Available</div>
        <div class="stat-card-val tabular-nums" style="color: var(--color-success);"><?= $countAvailable ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title">Pending Requests</div>
        <div class="stat-card-val tabular-nums" style="color: var(--color-warning);"><?= $countPending ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title">Accepted Requests</div>
        <div class="stat-card-val tabular-nums" style="color: var(--color-primary);"><?= $countAccepted ?></div>
    </div>
</div>

<!-- Recent Incoming Requests -->
<div style="margin-bottom: 2.5rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h2 style="font-size: 1.25rem;">Recent Community Requests</h2>
        <a href="/provider/requests.php" style="font-size: 0.875rem; font-weight: 600;">View All Requests →</a>
    </div>

    <?php if (!empty($recentRequests)): ?>
        <div class="table-wrap">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Social Working Group</th>
                            <th>Food Item</th>
                            <th>Requested Qty</th>
                            <th>Pickup Slot</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentRequests as $req): ?>
                            <tr>
                                <td>
                                    <strong><?= escape($req['group_name']) ?></strong><br>
                                    <span style="font-size:0.775rem; color:var(--color-text-muted);"><?= escape($req['representative_name']) ?> (<?= escape($req['phone']) ?>)</span>
                                </td>
                                <td><?= escape($req['food_name']) ?></td>
                                <td class="tabular-nums"><strong><?= floatval($req['requested_quantity']) ?></strong> <?= escape($req['quantity_unit']) ?></td>
                                <td><?= date('d M Y', strtotime($req['requested_date'])) ?> · <?= date('h:i A', strtotime($req['requested_time'])) ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower($req['status']) ?>">
                                        <?= escape($req['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($req['status'] === 'Pending'): ?>
                                        <div style="display:flex; gap:0.4rem;">
                                            <a href="/php/request-status-process.php?id=<?= $req['id'] ?>&action=accept" class="btn btn-sm btn-primary">Accept</a>
                                            <a href="/php/request-status-process.php?id=<?= $req['id'] ?>&action=reject" class="btn btn-sm btn-secondary" onclick="return confirm('Reject this request?')">Reject</a>
                                        </div>
                                    <?php else: ?>
                                        <span style="font-size:0.8rem; color:var(--color-text-muted);">Decided</span>
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
            <p class="empty-state-desc">When social working groups request food from your listings, they will show up right here.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Active Listings Overview -->
<div>
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h2 style="font-size: 1.25rem;">Active Food Listings</h2>
        <a href="/provider/my-food.php" style="font-size: 0.875rem; font-weight: 600;">Manage All Listings →</a>
    </div>

    <?php if (!empty($recentListings)): ?>
        <div class="cards-grid-3">
            <?php foreach ($recentListings as $listing): ?>
                <div class="food-card">
                    <div class="food-card-img-wrap">
                        <img src="/<?= escape($listing['image_url'] ?: 'images/hero_food_share.jpg') ?>" alt="<?= escape($listing['food_name']) ?>" class="food-card-img" referrerpolicy="no-referrer">
                        <span class="food-type-tag type-<?= strtolower(str_replace('-', '', $listing['food_type'])) ?>">
                            <?= escape($listing['food_type']) ?>
                        </span>
                    </div>
                    <div class="food-card-body">
                        <h3 class="food-card-title"><?= escape($listing['food_name']) ?></h3>
                        <div class="food-card-meta">
                            <span><?= escape($listing['category_name']) ?></span>
                            <span>·</span>
                            <span class="tabular-nums"><?= floatval($listing['available_quantity']) ?> / <?= floatval($listing['quantity']) ?> <?= escape($listing['quantity_unit']) ?></span>
                        </div>
                        <div style="margin-bottom:0.75rem;">
                            <span class="badge badge-<?= strtolower($listing['status']) ?>"><?= escape($listing['status']) ?></span>
                            <span style="font-size:0.75rem; color:var(--color-text-muted); margin-left:0.5rem;"><?= $listing['request_count'] ?> requests</span>
                        </div>
                        <div class="food-card-footer">
                            <div class="food-price-wrap">
                                <span class="food-price-val tabular-nums">₹<?= floatval($listing['price']) ?></span>
                            </div>
                            <a href="/provider/my-food.php" class="btn btn-sm btn-secondary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">🍲</div>
            <h3 class="empty-state-title">You haven't added any food listings yet.</h3>
            <p class="empty-state-desc">List your surplus food dishes to prevent waste and support community relief work.</p>
            <a href="/provider/add-food.php" class="btn btn-primary btn-sm">Add Your First Food Item</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
