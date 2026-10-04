<?php
require_once __DIR__ . '/../php/db.php';
$pageTitle = "Admin Overview";
require_once __DIR__ . '/header.php';

// Platform Statistics
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalProviders = $pdo->query("SELECT COUNT(*) FROM food_providers")->fetchColumn();
$totalGroups = $pdo->query("SELECT COUNT(*) FROM social_working_groups")->fetchColumn();
$totalListings = $pdo->query("SELECT COUNT(*) FROM food_listings")->fetchColumn();
$availableFood = $pdo->query("SELECT COUNT(*) FROM food_listings WHERE status = 'Available' AND available_quantity > 0")->fetchColumn();
$pendingRequests = $pdo->query("SELECT COUNT(*) FROM food_requests WHERE status = 'Pending'")->fetchColumn();
$acceptedRequests = $pdo->query("SELECT COUNT(*) FROM food_requests WHERE status = 'Accepted'")->fetchColumn();
$rejectedRequests = $pdo->query("SELECT COUNT(*) FROM food_requests WHERE status = 'Rejected'")->fetchColumn();

// Recent Registrations
$recentUsers = $pdo->query("SELECT id, username, email, role, status, created_at FROM users ORDER BY id DESC LIMIT 5")->fetchAll();

// Recent Requests
$recentReqs = $pdo->query("SELECT r.*, f.food_name, p.business_name, g.group_name 
    FROM food_requests r 
    JOIN food_listings f ON r.food_id = f.id 
    JOIN food_providers p ON f.provider_id = p.id 
    JOIN social_working_groups g ON r.group_id = g.id 
    ORDER BY r.id DESC LIMIT 5")->fetchAll();
?>

<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Platform Administration Overview</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
            Real-time status of users, surplus food lots, and community requests across Sevam.
        </p>
    </div>
</div>

<!-- Overview Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-title">Total Users</div>
        <div class="stat-card-val tabular-nums"><?= $totalUsers ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title">Food Providers</div>
        <div class="stat-card-val tabular-nums" style="color: var(--color-primary);"><?= $totalProviders ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title">Social Groups</div>
        <div class="stat-card-val tabular-nums" style="color: var(--color-primary-accent);"><?= $totalGroups ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title">Food Listings</div>
        <div class="stat-card-val tabular-nums"><?= $totalListings ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title">Available Lots</div>
        <div class="stat-card-val tabular-nums" style="color: var(--color-success);"><?= $availableFood ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title">Pending Requests</div>
        <div class="stat-card-val tabular-nums" style="color: var(--color-warning);"><?= $pendingRequests ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title">Accepted Requests</div>
        <div class="stat-card-val tabular-nums" style="color: var(--color-success);"><?= $acceptedRequests ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title">Rejected Requests</div>
        <div class="stat-card-val tabular-nums" style="color: var(--color-danger);"><?= $rejectedRequests ?></div>
    </div>
</div>

<div class="cards-grid-2" style="margin-top: 2rem; align-items: start;">
    <!-- Recent Users -->
    <div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h2 style="font-size: 1.15rem;">Recent User Registrations</h2>
            <a href="/admin/users.php" style="font-size: 0.85rem; font-weight: 600;">Manage Users →</a>
        </div>
        <div class="table-wrap">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $u): ?>
                            <tr>
                                <td>
                                    <strong><?= escape($u['username']) ?></strong><br>
                                    <span style="font-size:0.75rem; color:var(--color-text-muted);"><?= escape($u['email']) ?></span>
                                </td>
                                <td>
                                    <span style="text-transform: capitalize; font-weight:600; font-size:0.8rem;">
                                        <?= escape($u['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= ($u['status'] === 'active') ? 'accepted' : 'rejected' ?>">
                                        <?= escape($u['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Platform Requests -->
    <div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h2 style="font-size: 1.15rem;">Recent Food Requests</h2>
            <a href="/admin/requests.php" style="font-size: 0.85rem; font-weight: 600;">Manage Requests →</a>
        </div>
        <div class="table-wrap">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Food Item</th>
                            <th>Parties</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentReqs as $r): ?>
                            <tr>
                                <td>
                                    <strong><?= escape($r['food_name']) ?></strong><br>
                                    <span style="font-size:0.775rem; color:var(--color-text-muted);"><?= floatval($r['requested_quantity']) ?> units requested</span>
                                </td>
                                <td style="font-size:0.8rem;">
                                    <strong>Provider:</strong> <?= escape($r['business_name']) ?><br>
                                    <strong>Group:</strong> <?= escape($r['group_name']) ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= strtolower($r['status']) ?>">
                                        <?= escape($r['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
