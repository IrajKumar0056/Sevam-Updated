<?php
require_once __DIR__ . '/../php/db.php';
$pageTitle = "Contact Messages";
require_once __DIR__ . '/header.php';

// Handle deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $msgId = intval($_GET['id']);
    $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$msgId]);
    set_flash('admin_success', "Message #{$msgId} deleted.");
    header("Location: /admin/messages.php");
    exit;
}

$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY id DESC")->fetchAll();
?>

<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Public Inquiries & Messages</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
            Messages submitted via the public contact form by restaurants, NGOs, or community members.
        </p>
    </div>
</div>

<?php render_flash('admin_success'); ?>

<?php if (!empty($messages)): ?>
    <div style="display:flex; flex-direction:column; gap:1.25rem;">
        <?php foreach ($messages as $m): ?>
            <div class="card" style="padding: 1.5rem;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem; flex-wrap:wrap; gap:0.5rem;">
                    <div>
                        <span style="font-size:0.8rem; color:var(--color-text-muted);">
                            Received on <?= date('d M Y, h:i A', strtotime($m['created_at'])) ?> · Message #<?= $m['id'] ?>
                        </span>
                        <h3 style="font-size:1.15rem; margin-top:0.2rem;"><?= escape($m['subject']) ?></h3>
                        <div style="font-size:0.875rem; color:var(--color-text-muted); margin-top:0.15rem;">
                            From: <strong style="color:var(--color-text-main);"><?= escape($m['name']) ?></strong> 
                            (<?= escape($m['email']) ?><?= !empty($m['phone']) ? ' · Ph: ' . escape($m['phone']) : '' ?>)
                        </div>
                    </div>

                    <div>
                        <a href="mailto:<?= escape($m['email']) ?>?subject=Re: <?= urlencode($m['subject']) ?>" class="btn btn-sm btn-primary">
                            Reply by Email
                        </a>
                    </div>
                </div>

                <div style="background:var(--color-surface-subtle); padding:1rem; border-radius:var(--radius-sm); font-size:0.9rem; line-height:1.6; color:var(--color-text-main);">
                    <?= nl2br(escape($m['message'])) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">💬</div>
        <h3 class="empty-state-title">No contact messages received yet.</h3>
        <p class="empty-state-desc">When visitors submit inquiries through the contact page, they will appear here.</p>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
