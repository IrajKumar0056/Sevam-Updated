<?php
require_once __DIR__ . '/../php/db.php';
$pageTitle = "Add Surplus Food";
require_once __DIR__ . '/header.php';

// Fetch active categories
$catStmt = $pdo->query("SELECT * FROM food_categories WHERE status = 'active' ORDER BY name ASC");
$categories = $catStmt->fetchAll();

$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
?>

<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Add Surplus Food Listing</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
            Provide food details, accurate portion quantities, and specific collection time slots.
        </p>
    </div>
    <div>
        <a href="/provider/my-food.php" class="btn btn-secondary">
            View Existing Listings
        </a>
    </div>
</div>

<?php render_flash('food_error'); ?>

<div class="form-card" style="max-width: 860px;">
    <form action="/php/add-food-process.php" method="POST" enctype="multipart/form-data">
        
        <div style="border-bottom: 1px solid var(--color-border); padding-bottom: 0.75rem; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.15rem; color: var(--color-text-main);">1. Basic Food Information</h3>
        </div>

        <div class="form-row">
            <div class="form-group" style="flex: 2;">
                <label class="form-label" for="food_name">Food Item Name <span class="req">*</span></label>
                <input type="text" id="food_name" name="food_name" class="form-input" placeholder="e.g. Steamed Rice, Dal Tadka, Chapati Batch" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="food_type">Dietary Type <span class="req">*</span></label>
                <select id="food_type" name="food_type" class="form-select" required>
                    <option value="Veg" selected>Vegetarian (Veg)</option>
                    <option value="Non-Veg">Non-Vegetarian</option>
                    <option value="Vegan">Vegan</option>
                    <option value="Jain">Jain Friendly</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="category_id">Food Category <span class="req">*</span></label>
                <select id="category_id" name="category_id" class="form-select" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= escape($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="quantity">Available Quantity <span class="req">*</span></label>
                <input type="number" id="quantity" name="quantity" class="form-input" step="0.5" min="1" placeholder="e.g. 15" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="quantity_unit">Quantity Unit <span class="req">*</span></label>
                <select id="quantity_unit" name="quantity_unit" class="form-select" required>
                    <option value="kg" selected>Kilograms (kg)</option>
                    <option value="pieces">Pieces / Units</option>
                    <option value="plates">Plates / Meals</option>
                    <option value="packets">Sealed Packets</option>
                    <option value="liters">Liters (Liquids/Soups)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="price">Reasonable Recovery Price (₹) <span class="req">*</span></label>
                <input type="number" id="price" name="price" class="form-input" step="1" min="0" placeholder="e.g. 400" required>
                <div class="form-hint">Nominal cost recovery for Social Working Groups.</div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="food_description">Food Description & Ingredients</label>
            <textarea id="food_description" name="food_description" class="form-textarea" rows="2" placeholder="Briefly describe the food preparation method, taste, packaging, and temperature condition..."></textarea>
        </div>

        <div style="border-bottom: 1px solid var(--color-border); padding-bottom: 0.75rem; margin-top: 2rem; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.15rem; color: var(--color-text-main);">2. Time Slots & Expiry Timestamps</h3>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="prep_date">Preparation Date <span class="req">*</span></label>
                <input type="date" id="prep_date" name="prep_date" class="form-input" value="<?= $today ?>" max="<?= $today ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="available_date">Available For Pickup Date <span class="req">*</span></label>
                <input type="date" id="available_date" name="available_date" class="form-input" value="<?= $today ?>" min="<?= $today ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="available_start_time">Available Start Time <span class="req">*</span></label>
                <input type="time" id="available_start_time" name="available_start_time" class="form-input" value="17:00" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="available_end_time">Available End Time <span class="req">*</span></label>
                <input type="time" id="available_end_time" name="available_end_time" class="form-input" value="21:30" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="expiry_date">Safe Expiry Date <span class="req">*</span></label>
                <input type="date" id="expiry_date" name="expiry_date" class="form-input" value="<?= $tomorrow ?>" min="<?= $today ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="expiry_time">Safe Expiry Time <span class="req">*</span></label>
                <input type="time" id="expiry_time" name="expiry_time" class="form-input" value="03:00" required>
                <div class="form-hint">Time after which food must not be consumed.</div>
            </div>
        </div>

        <div style="border-bottom: 1px solid var(--color-border); padding-bottom: 0.75rem; margin-top: 2rem; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.15rem; color: var(--color-text-main);">3. Collection & Pickup Logistics</h3>
        </div>

        <div class="form-group">
            <label class="form-label" for="pickup_info">Pickup / Collection Instructions <span class="req">*</span></label>
            <textarea id="pickup_info" name="pickup_info" class="form-textarea" rows="2" placeholder="e.g. Please bring clean food-grade vessels and thermal containers. Collect from rear kitchen dispatch gate." required><?= escape($provider['address'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label" for="food_image">Food Photo (Optional)</label>
            <input type="file" id="food_image" name="food_image" class="form-input" accept="image/*">
            <div class="form-hint">Leave empty to use a professional category photo.</div>
        </div>

        <div style="margin-top: 2rem; display:flex; gap:1rem;">
            <button type="submit" class="btn btn-primary btn-lg">
                Publish Food Listing
            </button>
            <a href="/provider/my-food.php" class="btn btn-secondary btn-lg">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
