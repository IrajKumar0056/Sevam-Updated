<?php
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/auth.php';

check_auth(['provider']);

$provider = get_current_profile($pdo);
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? escape($pageTitle) . ' - Provider Dashboard - Sevam' : 'Food Provider - Sevam' ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-container">
            <a href="/" class="brand-logo">
                <span class="brand-mark">S</span>
                <span>SEVAM <span style="font-size:0.75rem; font-weight:500; color:var(--color-primary-accent);">Provider</span></span>
            </a>

            <div class="nav-actions">
                <span style="font-size:0.875rem; color:var(--color-text-muted); display:inline-flex; align-items:center; gap:0.35rem;">
                    <span><?= escape($provider['business_name'] ?? $_SESSION['username']) ?></span>
                    <?php if (($provider['fssai_status'] ?? '') === 'Certified'): ?>
                        <span style="background:#dcfce7; color:#15803d; border:1px solid #86efac; border-radius:4px; font-size:11px; padding:1px 5px; font-weight:700;">FSSAI</span>
                    <?php endif; ?>
                </span>
                <a href="/logout.php" class="btn btn-secondary btn-sm">Logout</a>
            </div>
        </div>
    </header>

    <div class="dashboard-layout">
        <!-- Sidebar Navigation -->
        <aside class="dashboard-sidebar">
            <div class="sidebar-user">
                <div class="sidebar-user-name"><?= escape($provider['business_name'] ?? 'Kitchen Partner') ?></div>
                <div class="sidebar-user-role"><?= escape($provider['business_type'] ?? 'Food Provider') ?></div>
            </div>

            <ul class="sidebar-nav">
                <li>
                    <a href="/provider/dashboard.php" class="sidebar-link <?= ($currentPage === 'dashboard.php') ? 'active' : '' ?>">
                        📊 Dashboard
                    </a>
                </li>
                <li>
                    <a href="/provider/add-food.php" class="sidebar-link <?= ($currentPage === 'add-food.php') ? 'active' : '' ?>">
                        ➕ Add Surplus Food
                    </a>
                </li>
                <li>
                    <a href="/provider/my-food.php" class="sidebar-link <?= ($currentPage === 'my-food.php') ? 'active' : '' ?>">
                        🍲 My Food Listings
                    </a>
                </li>
                <li>
                    <a href="/provider/requests.php" class="sidebar-link <?= ($currentPage === 'requests.php') ? 'active' : '' ?>">
                        📩 Received Requests
                    </a>
                </li>
                <li>
                    <a href="/provider/profile.php" class="sidebar-link <?= ($currentPage === 'profile.php') ? 'active' : '' ?>">
                        🏢 Kitchen Profile
                    </a>
                </li>
                <li style="margin-top: auto; padding-top: 1.5rem; border-top: 1px solid var(--color-border);">
                    <a href="/logout.php" class="sidebar-link" style="color: var(--color-danger);">
                        🚪 Logout
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="dashboard-content">
