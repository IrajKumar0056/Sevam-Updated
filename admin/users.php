<?php
require_once __DIR__ . '/../php/db.php';
$pageTitle = "User Management";
require_once __DIR__ . '/header.php';

// Handle Toggle Status or Delete
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $userId = intval($_GET['id']);

    if ($userId !== $_SESSION['user_id']) { // protect self
        require_once __DIR__ . '/../php/supabase.php';
        if ($action === 'activate') {
            $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$userId]);
            set_flash('admin_success', "User #{$userId} activated.");
            supabase_record_action('admin_user_activated', ['target_user_id' => $userId], 'user', $userId);
            supabase_request("users?id=eq.{$userId}", 'PATCH', ['status' => 'active']);
        } elseif ($action === 'deactivate') {
            $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = ?")->execute([$userId]);
            set_flash('admin_success', "User #{$userId} deactivated.");
            supabase_record_action('admin_user_deactivated', ['target_user_id' => $userId], 'user', $userId);
            supabase_request("users?id=eq.{$userId}", 'PATCH', ['status' => 'inactive']);
        } elseif ($action === 'delete') {
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
            set_flash('admin_success', "User #{$userId} deleted.");
            supabase_record_action('admin_user_deleted', ['target_user_id' => $userId], 'user', $userId);
            supabase_request("users?id=eq.{$userId}", 'DELETE');
        }
    }
    header("Location: /admin/users.php");
    exit;
}

$roleFilter = trim($_GET['role'] ?? '');
$search = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($roleFilter)) {
    $sql .= " AND role = ?";
    $params[] = $roleFilter;
}
if (!empty($search)) {
    $sql .= " AND (username LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">User Accounts Management</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
            Control access, status, and role configurations for all registered users.
        </p>
    </div>
</div>

<?php render_flash('admin_success'); ?>

<div class="form-card" style="padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
    <form action="/admin/users.php" method="GET" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end;">
        <div style="flex:2; min-width:200px;">
            <label class="form-label" style="font-size:0.8rem;">Search user</label>
            <input type="text" name="q" class="form-input" placeholder="Search by username or email..." value="<?= escape($search) ?>">
        </div>

        <div style="flex:1; min-width:160px;">
            <label class="form-label" style="font-size:0.8rem;">Filter by Role</label>
            <select name="role" class="form-select">
                <option value="">All Roles</option>
                <option value="provider" <?= ($roleFilter === 'provider') ? 'selected' : '' ?>>Food Provider</option>
                <option value="group" <?= ($roleFilter === 'group') ? 'selected' : '' ?>>Social Group</option>
                <option value="admin" <?= ($roleFilter === 'admin') ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-primary" style="height: 42px;">Filter</button>
            <?php if (!empty($search) || !empty($roleFilter)): ?>
                <a href="/admin/users.php" class="btn btn-secondary" style="height: 42px;">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="table-wrap">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email Address</th>
                    <th>Account Role</th>
                    <th>Status</th>
                    <th>Registered At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="tabular-nums">#<?= $u['id'] ?></td>
                        <td><strong><?= escape($u['username']) ?></strong></td>
                        <td><?= escape($u['email']) ?></td>
                        <td>
                            <span style="text-transform: capitalize; font-weight:600;">
                                <?= escape($u['role']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?= ($u['status'] === 'active') ? 'accepted' : 'rejected' ?>">
                                <?= escape($u['status']) ?>
                            </span>
                        </td>
                        <td class="tabular-nums" style="font-size:0.8rem; color:var(--color-text-muted);">
                            <?= date('d M Y', strtotime($u['created_at'])) ?>
                        </td>
                        <td>
                            <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                <div style="display:flex; gap:0.4rem;">
                                    <?php if ($u['status'] === 'active'): ?>
                                        <a href="/admin/users.php?action=deactivate&id=<?= $u['id'] ?>" class="btn btn-sm btn-secondary" title="Deactivate user">
                                            Deactivate
                                        </a>
                                    <?php else: ?>
                                        <a href="/admin/users.php?action=activate&id=<?= $u['id'] ?>" class="btn btn-sm btn-primary" title="Activate user">
                                            Activate
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span style="font-size:0.75rem; color:var(--color-text-muted);">Current Admin</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
