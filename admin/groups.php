<?php
require_once __DIR__ . '/../php/db.php';
$pageTitle = "Social Working Groups";
require_once __DIR__ . '/header.php';

$stmt = $pdo->query("SELECT g.*, u.username, u.email, u.status as user_status,
    (SELECT COUNT(*) FROM food_requests WHERE group_id = g.id) as request_count
    FROM social_working_groups g 
    JOIN users u ON g.user_id = u.id 
    ORDER BY g.id DESC");
$groups = $stmt->fetchAll();
?>

<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Registered Social Working Groups</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
            NGOs, volunteer teams, and community welfare societies requesting food distribution.
        </p>
    </div>
</div>

<div class="table-wrap">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Group ID</th>
                    <th>Organization Name</th>
                    <th>Representative</th>
                    <th>Location</th>
                    <th>Organization Type</th>
                    <th>Darpan ID</th>
                    <th>Total Requests</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $g): ?>
                    <tr>
                        <td class="tabular-nums">#<?= $g['id'] ?></td>
                        <td>
                            <strong><?= escape($g['group_name']) ?></strong><br>
                            <span style="font-size:0.75rem; color:var(--color-text-muted);"><?= escape($g['email']) ?></span>
                        </td>
                        <td>
                            <?= escape($g['representative_name']) ?><br>
                            <span style="font-size:0.8rem; color:var(--color-text-muted);"><?= escape($g['phone']) ?></span>
                        </td>
                        <td style="font-size:0.85rem;">
                            <?= escape($g['city']) ?>, <?= escape($g['state']) ?><br>
                            <span style="font-size:0.75rem; color:var(--color-text-muted);"><?= escape($g['address']) ?></span>
                        </td>
                        <td style="font-size:0.85rem;">
                            <strong><?= escape($g['organization_type']) ?></strong>
                        </td>
                        <td>
                            <?php if (!empty($g['darpan_id'])): ?>
                                <span class="badge badge-accepted"><?= escape($g['darpan_id']) ?></span>
                            <?php else: ?>
                                <span style="font-size:0.75rem; color:var(--color-text-muted);">Not Provided</span>
                            <?php endif; ?>
                        </td>
                        <td class="tabular-nums">
                            <strong><?= $g['request_count'] ?></strong> requests
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
