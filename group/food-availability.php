<?php
require_once __DIR__ . '/../php/db.php';
$pageTitle = "Food Availability";
require_once __DIR__ . '/header.php';

// Category filter & Type filter
$selectedCat = intval($_GET['cat'] ?? 0);
$selectedType = trim($_GET['type'] ?? '');
$search = trim($_GET['q'] ?? '');

$today = date('Y-m-d');
$sql = "SELECT f.*, p.business_name, p.address as provider_address, p.city as provider_city, p.fssai_status, p.fssai_number, c.name as category_name 
        FROM food_listings f 
        JOIN food_providers p ON f.provider_id = p.id 
        JOIN food_categories c ON f.category_id = c.id 
        WHERE f.status = 'Available' AND f.available_quantity > 0 AND f.expiry_date >= ?";

$params = [$today];
if ($selectedCat > 0) {
    $sql .= " AND f.category_id = ?";
    $params[] = $selectedCat;
}
if (!empty($selectedType)) {
    $sql .= " AND f.food_type = ?";
    $params[] = $selectedType;
}
if (!empty($search)) {
    $sql .= " AND (f.food_name LIKE ? OR p.business_name LIKE ? OR p.city LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY f.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$availableFoods = $stmt->fetchAll();

// Fetch categories for filter dropdown
$catList = $pdo->query("SELECT * FROM food_categories WHERE status = 'active'")->fetchAll();
?>

<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Surplus Food Availability</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
            Browse safe, fresh surplus food lots ready for pickup across registered commercial kitchens.
        </p>
    </div>
</div>

<?php render_flash('group_success'); ?>
<?php render_flash('group_error'); ?>

<!-- Filter and Search Bar -->
<div class="form-card" style="padding: 1.25rem; margin-bottom: 2rem;">
    <form action="/group/food-availability.php" method="GET" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end;">
        <div style="flex:2; min-width:200px;">
            <label class="form-label" style="font-size:0.8rem;">Search food or kitchen</label>
            <input type="text" name="q" class="form-input" placeholder="e.g. Rice, Annapurna, Mumbai..." value="<?= escape($search) ?>">
        </div>

        <div style="flex:1; min-width:160px;">
            <label class="form-label" style="font-size:0.8rem;">Food Category</label>
            <select name="cat" class="form-select">
                <option value="0">All Categories</option>
                <?php foreach ($catList as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($selectedCat == $c['id']) ? 'selected' : '' ?>><?= escape($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="flex:1; min-width:140px;">
            <label class="form-label" style="font-size:0.8rem;">Dietary Type</label>
            <select name="type" class="form-select">
                <option value="">All Types</option>
                <option value="Veg" <?= ($selectedType === 'Veg') ? 'selected' : '' ?>>Vegetarian</option>
                <option value="Non-Veg" <?= ($selectedType === 'Non-Veg') ? 'selected' : '' ?>>Non-Veg</option>
                <option value="Vegan" <?= ($selectedType === 'Vegan') ? 'selected' : '' ?>>Vegan</option>
                <option value="Jain" <?= ($selectedType === 'Jain') ? 'selected' : '' ?>>Jain</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-primary" style="height: 42px;">Filter</button>
            <?php if (!empty($search) || $selectedCat > 0 || !empty($selectedType)): ?>
                <a href="/group/food-availability.php" class="btn btn-secondary" style="height: 42px;">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Food Cards Grid -->
<?php if (!empty($availableFoods)): ?>
    <div class="cards-grid-3">
        <?php foreach ($availableFoods as $food): ?>
            <div class="food-card">
                <div class="food-card-img-wrap">
                    <img src="/<?= escape($food['image_url'] ?: 'images/hero_food_share.jpg') ?>" alt="<?= escape($food['food_name']) ?>" class="food-card-img" referrerpolicy="no-referrer">
                    <span class="food-type-tag type-<?= strtolower(str_replace('-', '', $food['food_type'])) ?>">
                        <?= escape($food['food_type']) ?>
                    </span>
                </div>
                
                <div class="food-card-body">
                    <h3 class="food-card-title"><?= escape($food['food_name']) ?></h3>
                    
                    <div class="food-card-meta">
                        <span><?= escape($food['category_name']) ?></span>
                        <span>·</span>
                        <span class="tabular-nums" style="font-weight:700; color:var(--color-primary);">
                            <?= floatval($food['available_quantity']) ?> <?= escape($food['quantity_unit']) ?> available
                        </span>
                    </div>

                    <div style="font-size:0.8125rem; color:var(--color-text-muted); margin-bottom:0.75rem; display:flex; flex-direction:column; gap:0.25rem;">
                        <div>
                            <strong>Pickup Window:</strong> <?= date('d M Y', strtotime($food['available_date'])) ?> 
                            (<?= date('h:i A', strtotime($food['available_start_time'])) ?> - <?= date('h:i A', strtotime($food['available_end_time'])) ?>)
                        </div>
                        <div>
                            <strong>Safe Expiry:</strong> <?= date('d M Y', strtotime($food['expiry_date'])) ?> 
                            (<?= date('h:i A', strtotime($food['expiry_time'])) ?>)
                        </div>
                    </div>

                    <p class="food-card-desc"><?= escape(truncate_text($food['food_description'] ?? 'Freshly prepared food ready for pickup.', 95, '...')) ?></p>

                    <div class="food-card-provider">
                        <div class="provider-name"><?= escape($food['business_name']) ?></div>
                        <div class="provider-loc">
                            <?= escape($food['provider_address']) ?>, <?= escape($food['provider_city']) ?>
                        </div>
                        <div style="font-size:0.75rem; color:var(--color-text-muted); margin-top:0.2rem;">
                            FSSAI: 
                            <?php if ($food['fssai_status'] === 'Certified'): ?>
                                <span style="color:var(--color-success); font-weight:700;">Certified (<?= escape($food['fssai_number']) ?>)</span>
                            <?php else: ?>
                                <span>Not Certified</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="food-card-footer">
                        <div class="food-price-wrap">
                            <span class="food-price-val tabular-nums">₹<?= floatval($food['price']) ?></span>
                            <span class="food-price-label">Cost Recovery</span>
                        </div>
                        
                        <button type="button" class="btn btn-primary btn-sm btn-open-request-modal" 
                            data-id="<?= $food['id'] ?>"
                            data-name="<?= escape($food['food_name']) ?>"
                            data-provider="<?= escape($food['business_name']) ?>"
                            data-qty="<?= floatval($food['available_quantity']) ?>"
                            data-unit="<?= escape($food['quantity_unit']) ?>"
                            data-price="<?= floatval($food['price']) ?>"
                            data-date="<?= $food['available_date'] ?>"
                            data-start="<?= date('h:i A', strtotime($food['available_start_time'])) ?>"
                            data-end="<?= date('h:i A', strtotime($food['available_end_time'])) ?>">
                            Request Food
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">🍲</div>
        <h3 class="empty-state-title">No surplus food currently matches your filters.</h3>
        <p class="empty-state-desc">Try clearing your filters or check back later as kitchens update their surplus batches in the afternoon and evening.</p>
        <a href="/group/food-availability.php" class="btn btn-secondary btn-sm">Clear Filters</a>
    </div>
<?php endif; ?>

<!-- MODAL: REQUEST FOOD DIALOG -->
<div id="food-request-modal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">Request Surplus Food</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>

        <form action="/php/request-food-process.php" method="POST">
            <input type="hidden" id="modal-food-id" name="food_id" value="">

            <div class="modal-body">
                <div style="background:var(--color-surface-subtle); padding:1rem; border-radius:var(--radius-sm); margin-bottom:1.25rem;">
                    <h4 id="modal-food-title" style="font-size:1.1rem; color:var(--color-text-main); margin-bottom:0.25rem;">Food Name</h4>
                    <div style="font-size:0.875rem; color:var(--color-text-muted); display:flex; flex-direction:column; gap:0.2rem;">
                        <div>Provider: <strong id="modal-provider-name" style="color:var(--color-text-main);"></strong></div>
                        <div>Available: <strong id="modal-available-qty" style="color:var(--color-primary);"></strong> · Estimated Price: <strong id="modal-price"></strong></div>
                        <div>Allowed Window: <span id="modal-slot"></span></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="modal-req-qty">
                        Requested Quantity (<span id="modal-qty-unit-label">kg</span>) <span class="req">*</span>
                    </label>
                    <input type="number" id="modal-req-qty" name="requested_quantity" class="form-input" step="0.5" min="0.5" required>
                    <div class="form-hint">You can request all or a portion of the available food.</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="modal-req-date">Requested Pickup Date <span class="req">*</span></label>
                        <input type="date" id="modal-req-date" name="requested_date" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="modal-req-time">Pickup Arrival Time <span class="req">*</span></label>
                        <input type="time" id="modal-req-time" name="requested_time" class="form-input" value="18:30" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="modal-message">Note for Kitchen Team</label>
                    <textarea id="modal-message" name="message" class="form-textarea" rows="2" placeholder="e.g. We will arrive in a sanitized vehicle with insulated stainless steel vessels."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary">Send Request</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
