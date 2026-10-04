<?php
require_once __DIR__ . '/../php/db.php';
$pageTitle = "Kitchen Profile";
require_once __DIR__ . '/header.php';

$providerId = $provider['id'] ?? 0;
$updateMsg = '';
$updateErr = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $business_name = trim($_POST['business_name'] ?? '');
    $owner_name = trim($_POST['owner_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $business_type = trim($_POST['business_type'] ?? '');
    $fssai_status = trim($_POST['fssai_status'] ?? 'Not Certified');
    $fssai_number = ($fssai_status === 'Certified') ? trim($_POST['fssai_number'] ?? '') : null;

    if (empty($business_name) || empty($owner_name) || empty($phone) || empty($city)) {
        $updateErr = 'Please fill in all required fields.';
    } else {
        try {
            $upStmt = $pdo->prepare("UPDATE food_providers SET 
                business_name = ?, owner_name = ?, phone = ?, address = ?, city = ?, state = ?, business_type = ?, fssai_status = ?, fssai_number = ? 
                WHERE id = ?");
            $upStmt->execute([$business_name, $owner_name, $phone, $address, $city, $state, $business_type, $fssai_status, $fssai_number, $providerId]);
            $updateMsg = 'Profile updated successfully!';
            
            // Record action and sync profile to Supabase
            require_once __DIR__ . '/../php/supabase.php';
            supabase_record_action('provider_profile_updated', [
                'provider_id' => $providerId,
                'business_name' => $business_name,
                'owner_name' => $owner_name,
                'city' => $city
            ], 'food_provider', $providerId);
            supabase_sync_provider([
                'id' => $providerId,
                'user_id' => $_SESSION['user_id'] ?? 0,
                'business_name' => $business_name,
                'owner_name' => $owner_name,
                'phone' => $phone,
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'business_type' => $business_type
            ]);

            // Refresh provider info
            $provider = get_current_profile($pdo);
        } catch (Exception $e) {
            $updateErr = 'Update failed: ' . $e->getMessage();
        }
    }
}
?>

<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Food Provider Profile</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
            Manage your kitchen details, contact information, and food safety certifications.
        </p>
    </div>
</div>

<?php if ($updateMsg): ?>
    <div class="alert alert-success"><span><?= escape($updateMsg) ?></span></div>
<?php endif; ?>
<?php if ($updateErr): ?>
    <div class="alert alert-danger"><span><?= escape($updateErr) ?></span></div>
<?php endif; ?>

<div class="cards-grid-2" style="max-width: 960px; align-items: start;">
    <div class="form-card">
        <form action="/provider/profile.php" method="POST">
            <div class="form-group">
                <label class="form-label" for="business_name">Business / Kitchen Name <span class="req">*</span></label>
                <input type="text" id="business_name" name="business_name" class="form-input" value="<?= escape($provider['business_name']) ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="owner_name">Owner / Representative Name <span class="req">*</span></label>
                    <input type="text" id="owner_name" name="owner_name" class="form-input" value="<?= escape($provider['owner_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Contact Mobile Number <span class="req">*</span></label>
                    <input type="tel" id="phone" name="phone" class="form-input" value="<?= escape($provider['phone']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="business_type">Business Type</label>
                <select id="business_type" name="business_type" class="form-select">
                    <?php
                    $types = ['Restaurant & Catering', 'Restaurant / Cafe', 'Hotel & Banquets', 'Catering Service', 'Hostel / College Mess', 'Cloud Kitchen', 'Event Organizer', 'Other Commercial Kitchen'];
                    foreach ($types as $t) {
                        $sel = ($provider['business_type'] === $t) ? 'selected' : '';
                        echo "<option value='" . escape($t) . "' {$sel}>" . escape($t) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="fssai_status">FSSAI Status</label>
                    <select id="fssai_status" name="fssai_status" class="form-select">
                        <option value="Certified" <?= ($provider['fssai_status'] === 'Certified') ? 'selected' : '' ?>>Certified</option>
                        <option value="Not Certified" <?= ($provider['fssai_status'] === 'Not Certified') ? 'selected' : '' ?>>Not Certified</option>
                    </select>
                </div>
                <div class="form-group" id="fssai_number_wrap">
                    <label class="form-label" for="fssai_number">FSSAI License Number</label>
                    <input type="text" id="fssai_number" name="fssai_number" class="form-input" value="<?= escape($provider['fssai_number']) ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="address">Pickup Address <span class="req">*</span></label>
                <textarea id="address" name="address" class="form-textarea" rows="2" required><?= escape($provider['address']) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="city">City <span class="req">*</span></label>
                    <input type="text" id="city" name="city" class="form-input" value="<?= escape($provider['city']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="state">State <span class="req">*</span></label>
                    <input type="text" id="state" name="state" class="form-input" value="<?= escape($provider['state']) ?>" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">
                Save Profile Changes
            </button>
        </form>
    </div>

    <div>
        <div class="card" style="background-color: var(--color-surface); margin-bottom: 1.5rem;">
            <div style="width: 52px; height: 52px; border-radius: var(--radius-sm); background: var(--color-primary-subtle); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800; margin-bottom: 1rem;">
                🏢
            </div>
            <h3 style="font-size: 1.2rem; margin-bottom: 0.25rem;"><?= escape($provider['business_name']) ?></h3>
            <p style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 1rem;">
                Account Username: <strong style="color:var(--color-text-main);"><?= escape($provider['username']) ?></strong> · <?= escape($provider['email']) ?>
            </p>

            <div style="border-top: 1px solid var(--color-border); padding-top: 1rem; display:flex; flex-direction:column; gap:0.6rem; font-size:0.875rem;">
                <div>
                    <strong>FSSAI Certification:</strong>
                    <?php if ($provider['fssai_status'] === 'Certified'): ?>
                        <span style="color:var(--color-success); font-weight:700;">Verified (<?= escape($provider['fssai_number']) ?>)</span>
                    <?php else: ?>
                        <span style="color:var(--color-warning);">Self Declared</span>
                    <?php endif; ?>
                </div>
                <div>
                    <strong>Registered Address:</strong><br>
                    <?= escape($provider['address']) ?>, <?= escape($provider['city']) ?>, <?= escape($provider['state']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
