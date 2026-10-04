<?php
require_once __DIR__ . '/../php/db.php';
$pageTitle = "My Food Listings";
require_once __DIR__ . '/header.php';

$providerId = $provider['id'] ?? 0;

// Handle deletion if requested
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delId = intval($_GET['id']);
    $delStmt = $pdo->prepare("DELETE FROM food_listings WHERE id = ? AND provider_id = ?");
    $delStmt->execute([$delId, $providerId]);
    set_flash('provider_success', 'Food listing removed successfully.', 'info');
    header("Location: /provider/my-food.php");
    exit;
}

// Fetch all listings for this provider
$stmt = $pdo->prepare("SELECT f.*, c.name as category_name,
    (SELECT COUNT(*) FROM food_requests WHERE food_id = f.id) as request_count 
    FROM food_listings f 
    JOIN food_categories c ON f.category_id = c.id 
    WHERE f.provider_id = ? 
    ORDER BY f.id DESC");
$stmt->execute([$providerId]);
$listings = $stmt->fetchAll();
?>

<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">My Surplus Food Listings</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
            Manage all surplus food lots posted from your kitchen facility.
        </p>
    </div>
    <div>
        <a href="/provider/add-food.php" class="btn btn-primary">
            ➕ Add New Surplus Food
        </a>
    </div>
</div>

<?php render_flash('provider_success'); ?>

<?php if (!empty($listings)): ?>
    <div class="cards-grid-3">
        <?php foreach ($listings as $item): ?>
            <div class="food-card">
                <div class="food-card-img-wrap">
                    <img src="/<?= escape($item['image_url'] ?: 'images/hero_food_share.jpg') ?>" alt="<?= escape($item['food_name']) ?>" class="food-card-img" referrerpolicy="no-referrer">
                    <span class="food-type-tag type-<?= strtolower(str_replace('-', '', $item['food_type'])) ?>">
                        <?= escape($item['food_type']) ?>
                    </span>
                </div>
                <div class="food-card-body">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.35rem;">
                        <h3 class="food-card-title"><?= escape($item['food_name']) ?></h3>
                        <span class="badge badge-<?= strtolower($item['status']) ?>">
                            <?= escape($item['status']) ?>
                        </span>
                    </div>

                    <div class="food-card-meta">
                        <span><?= escape($item['category_name']) ?></span>
                        <span>·</span>
                        <span class="tabular-nums">
                            <strong><?= floatval($item['available_quantity']) ?></strong> / <?= floatval($item['quantity']) ?> <?= escape($item['quantity_unit']) ?> left
                        </span>
                    </div>

                    <div style="font-size:0.8125rem; color:var(--color-text-muted); margin-bottom:0.75rem; display:flex; flex-direction:column; gap:0.2rem;">
                        <div>
                            <strong>Available Slot:</strong> <?= date('d M Y', strtotime($item['available_date'])) ?> 
                            (<?= date('h:i A', strtotime($item['available_start_time'])) ?> - <?= date('h:i A', strtotime($item['available_end_time'])) ?>)
                        </div>
                        <div>
                            <strong>Safe Expiry:</strong> <?= date('d M Y', strtotime($item['expiry_date'])) ?> 
                            (<?= date('h:i A', strtotime($item['expiry_time'])) ?>)
                        </div>
                        <div>
                            <strong>Incoming Requests:</strong> <a href="/provider/requests.php?food_id=<?= $item['id'] ?>" style="font-weight:600; text-decoration:underline;"><?= $item['request_count'] ?> requests</a>
                        </div>
                    </div>

                    <p class="food-card-desc"><?= escape(truncate_text($item['food_description'] ?? 'No description provided.', 90, '...')) ?></p>

                    <div class="food-card-footer">
                        <div class="food-price-wrap">
                            <span class="food-price-val tabular-nums">₹<?= floatval($item['price']) ?></span>
                            <span class="food-price-label">Recovery Price</span>
                        </div>
                        <div style="display:flex; gap:0.4rem;">
                            <a href="/provider/requests.php?food_id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary" title="View requests">
                                Requests (<?= $item['request_count'] ?>)
                            </a>
                            <a href="/provider/my-food.php?action=delete&id=<?= $item['id'] ?>" class="btn btn-sm btn-secondary" onclick="return confirm('Are you sure you want to delete this listing?')" style="color:var(--color-danger);" title="Delete listing">
                                🗑
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">🍲</div>
        <h3 class="empty-state-title">You haven't added any food listings yet.</h3>
        <p class="empty-state-desc">List your surplus dishes to allow verified NGOs and community workers to request them.</p>
        <a href="/provider/add-food.php" class="btn btn-primary btn-sm">Add Food Listing</a>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
