<?php
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/auth.php';

check_auth(['group']);

$group = get_current_profile($pdo);
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? escape($pageTitle) . ' - Group Dashboard - Sevam' : 'Social Working Group - Sevam' ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-container">
            <a href="/" class="brand-logo">
                <span class="brand-mark" style="background:var(--color-primary-accent);">S</span>
                <span>SEVAM <span style="font-size:0.75rem; font-weight:500; color:var(--color-primary-accent);">Social Group</span></span>
            </a>

            <div class="nav-actions">
                <span style="font-size:0.875rem; color:var(--color-text-muted); display:inline-flex; align-items:center; gap:0.35rem;">
                    <span><?= escape($group['group_name'] ?? $_SESSION['username']) ?></span>
                    <?php if (!empty($group['darpan_id'])): ?>
                        <span style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; border-radius:4px; font-size:11px; padding:1px 5px; font-weight:700;">NGO</span>
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
                <div class="sidebar-user-name"><?= escape($group['group_name'] ?? 'Social Working Group') ?></div>
                <div class="sidebar-user-role"><?= escape($group['organization_type'] ?? 'Social Partner') ?></div>
            </div>

            <ul class="sidebar-nav">
                <li>
                    <a href="/group/dashboard.php" class="sidebar-link <?= ($currentPage === 'dashboard.php') ? 'active' : '' ?>">
                        📊 Dashboard
                    </a>
                </li>
                <li>
                    <a href="/group/food-availability.php" class="sidebar-link <?= ($currentPage === 'food-availability.php') ? 'active' : '' ?>">
                        🍲 Food Availability
                    </a>
                </li>
                <li>
                    <a href="/group/my-requests.php" class="sidebar-link <?= ($currentPage === 'my-requests.php') ? 'active' : '' ?>">
                        📋 My Requests
                    </a>
                </li>
                <li>
                    <a href="/group/profile.php" class="sidebar-link <?= ($currentPage === 'profile.php') ? 'active' : '' ?>">
                        🏛 Organization Profile
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
