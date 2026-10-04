<?php
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/auth.php';

check_auth(['admin']);

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? escape($pageTitle) . ' - Admin Console - Sevam' : 'Admin Console - Sevam' ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-container">
            <a href="/" class="brand-logo">
                <span class="brand-mark" style="background:#0f172a;">S</span>
                <span>SEVAM <span style="font-size:0.75rem; font-weight:600; color:var(--color-primary-light);">Admin</span></span>
            </a>

            <div class="nav-actions">
                <span style="font-size:0.875rem; color:var(--color-text-muted);">
                    Logged in as <strong><?= escape($_SESSION['username'] ?? 'Admin') ?></strong>
                </span>
                <a href="/logout.php" class="btn btn-secondary btn-sm">Logout</a>
            </div>
        </div>
    </header>

    <div class="dashboard-layout">
        <!-- Admin Sidebar Navigation -->
        <aside class="dashboard-sidebar">
            <div class="sidebar-user" style="background:#0f172a; color:#fff;">
                <div class="sidebar-user-name" style="color:#fff;">System Administrator</div>
                <div class="sidebar-user-role" style="color:#94a3b8;">Full Platform Access</div>
            </div>

            <ul class="sidebar-nav">
                <li>
                    <a href="/admin/dashboard.php" class="sidebar-link <?= ($currentPage === 'dashboard.php') ? 'active' : '' ?>">
                        📊 Dashboard Overview
                    </a>
                </li>
                <li>
                    <a href="/admin/users.php" class="sidebar-link <?= ($currentPage === 'users.php') ? 'active' : '' ?>">
                        👥 User Management
                    </a>
                </li>
                <li>
                    <a href="/admin/providers.php" class="sidebar-link <?= ($currentPage === 'providers.php') ? 'active' : '' ?>">
                        🏢 Food Providers
                    </a>
                </li>
                <li>
                    <a href="/admin/groups.php" class="sidebar-link <?= ($currentPage === 'groups.php') ? 'active' : '' ?>">
                        🤝 Social Working Groups
                    </a>
                </li>
                <li>
                    <a href="/admin/foods.php" class="sidebar-link <?= ($currentPage === 'foods.php') ? 'active' : '' ?>">
                        🍲 Food Listings
                    </a>
                </li>
                <li>
                    <a href="/admin/requests.php" class="sidebar-link <?= ($currentPage === 'requests.php') ? 'active' : '' ?>">
                        📋 Food Requests
                    </a>
                </li>
                <li>
                    <a href="/admin/categories.php" class="sidebar-link <?= ($currentPage === 'categories.php') ? 'active' : '' ?>">
                        🏷 Food Categories
                    </a>
                </li>
                <li>
                    <a href="/admin/messages.php" class="sidebar-link <?= ($currentPage === 'messages.php') ? 'active' : '' ?>">
                        💬 Contact Messages
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
