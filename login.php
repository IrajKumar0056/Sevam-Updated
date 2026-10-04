<?php
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth.php';

// If already logged in, redirect to respective dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'provider') {
        header("Location: /provider/dashboard.php");
        exit;
    } elseif ($_SESSION['role'] === 'group') {
        header("Location: /group/dashboard.php");
        exit;
    } elseif ($_SESSION['role'] === 'admin') {
        header("Location: /admin/dashboard.php");
        exit;
    }
}

$pageTitle = "Login to Sevam";
require_once __DIR__ . '/php/header.php';
?>

<div class="container" style="padding-top: 4rem; padding-bottom: 5rem;">
    <div style="max-width: 480px; margin: 0 auto;">
        
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 44px; height: 44px; background: var(--color-primary); color: #fff; border-radius: var(--radius-sm); font-size: 1.4rem; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                S
            </div>
            <h1 style="font-size: 1.85rem; margin-bottom: 0.5rem;">Sign In to Sevam</h1>
            <p style="font-size: 0.95rem; color: var(--color-text-muted);">
                Enter your credentials to access your organization dashboard.
            </p>
        </div>

        <?php render_flash('login_error'); ?>
        <?php render_flash('login_success'); ?>
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'unauthorized'): ?>
            <div class="alert alert-warning">
                <span>Please log in to access that protected page.</span>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'forbidden'): ?>
            <div class="alert alert-danger">
                <span>You do not have permission to view that section.</span>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form action="/php/login-process.php" method="POST">
                <div class="form-group">
                    <label class="form-label" for="identity">Username or Email Address</label>
                    <input type="text" id="identity" name="identity" class="form-input" placeholder="e.g. annapurna_kitchen or user@example.com" required autofocus>
                </div>

                <div class="form-group">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.35rem;">
                        <label class="form-label" for="password" style="margin-bottom:0;">Password</label>
                    </div>
                    <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1.5rem;">
                    Sign In
                </button>
            </form>
        </div>

        <div style="text-align: center; margin-top: 1.75rem; font-size: 0.9rem; color: var(--color-text-muted);">
            Don't have an account yet? <a href="/register.php" style="font-weight: 600; color: var(--color-primary);">Register here</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/php/footer.php'; ?>
