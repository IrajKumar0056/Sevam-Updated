<?php
require_once __DIR__ . '/../php/db.php';
$pageTitle = "Organization Profile";
require_once __DIR__ . '/header.php';

$groupId = $group['id'] ?? 0;
$updateMsg = '';
$updateErr = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_name = trim($_POST['group_name'] ?? '');
    $representative_name = trim($_POST['representative_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $organization_type = trim($_POST['organization_type'] ?? '');
    $darpan_id = trim($_POST['darpan_id'] ?? '');

    if (empty($group_name) || empty($representative_name) || empty($phone) || empty($city)) {
        $updateErr = 'Please fill in all required fields.';
    } else {
        try {
            $upStmt = $pdo->prepare("UPDATE social_working_groups SET 
                group_name = ?, representative_name = ?, phone = ?, address = ?, city = ?, state = ?, organization_type = ?, darpan_id = ? 
                WHERE id = ?");
            $upStmt->execute([$group_name, $representative_name, $phone, $address, $city, $state, $organization_type, !empty($darpan_id) ? $darpan_id : null, $groupId]);
            $updateMsg = 'Organization profile updated successfully!';
            
            // Record action and sync profile to Supabase
            require_once __DIR__ . '/../php/supabase.php';
            supabase_record_action('group_profile_updated', [
                'group_id' => $groupId,
                'group_name' => $group_name,
                'representative_name' => $representative_name,
                'city' => $city
            ], 'social_working_group', $groupId);
            supabase_sync_group([
                'id' => $groupId,
                'user_id' => $_SESSION['user_id'] ?? 0,
                'group_name' => $group_name,
                'representative_name' => $representative_name,
                'phone' => $phone,
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'organization_type' => $organization_type,
                'darpan_id' => !empty($darpan_id) ? $darpan_id : null
            ]);

            // Refresh
            $group = get_current_profile($pdo);
        } catch (Exception $e) {
            $updateErr = 'Update failed: ' . $e->getMessage();
        }
    }
}
?>

<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Social Working Group Profile</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
            Manage your group details, representative contacts, and NGO verification data.
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
        <form action="/group/profile.php" method="POST">
            <div class="form-group">
                <label class="form-label" for="group_name">Organization / Group Name <span class="req">*</span></label>
                <input type="text" id="group_name" name="group_name" class="form-input" value="<?= escape($group['group_name']) ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="representative_name">Representative Name <span class="req">*</span></label>
                    <input type="text" id="representative_name" name="representative_name" class="form-input" value="<?= escape($group['representative_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Contact Mobile Number <span class="req">*</span></label>
                    <input type="tel" id="phone" name="phone" class="form-input" value="<?= escape($group['phone']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="organization_type">Organization Type</label>
                <select id="organization_type" name="organization_type" class="form-select">
                    <?php
                    $types = ['Registered NGO', 'Community Volunteer Group', 'Charitable Trust', 'Youth Welfare Club', 'Homeless Shelter', 'Religious / Community Kitchen'];
                    foreach ($types as $t) {
                        $sel = ($group['organization_type'] === $t) ? 'selected' : '';
                        echo "<option value='" . escape($t) . "' {$sel}>" . escape($t) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="darpan_id">NGO Darpan ID</label>
                <input type="text" id="darpan_id" name="darpan_id" class="form-input" value="<?= escape($group['darpan_id']) ?>" placeholder="e.g. MH/2021/0129845">
            </div>

            <div class="form-group">
                <label class="form-label" for="address">Operational Address <span class="req">*</span></label>
                <textarea id="address" name="address" class="form-textarea" rows="2" required><?= escape($group['address']) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="city">City <span class="req">*</span></label>
                    <input type="text" id="city" name="city" class="form-input" value="<?= escape($group['city']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="state">State <span class="req">*</span></label>
                    <input type="text" id="state" name="state" class="form-input" value="<?= escape($group['state']) ?>" required>
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
                🤝
            </div>
            <h3 style="font-size: 1.2rem; margin-bottom: 0.25rem;"><?= escape($group['group_name']) ?></h3>
            <p style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 1rem;">
                Account Username: <strong style="color:var(--color-text-main);"><?= escape($group['username']) ?></strong> · <?= escape($group['email']) ?>
            </p>

            <div style="border-top: 1px solid var(--color-border); padding-top: 1rem; display:flex; flex-direction:column; gap:0.6rem; font-size:0.875rem;">
                <div>
                    <strong>Organization Type:</strong> <?= escape($group['organization_type']) ?>
                    <?php if (!empty($group['darpan_id'])): ?>
                        · <span style="color:var(--color-info); font-weight:700;">Darpan: <?= escape($group['darpan_id']) ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <strong>Lead Representative:</strong> <?= escape($group['representative_name']) ?> (<?= escape($group['phone']) ?>)
                </div>
                <div>
                    <strong>Dispatch Point:</strong><br>
                    <?= escape($group['address']) ?>, <?= escape($group['city']) ?>, <?= escape($group['state']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
