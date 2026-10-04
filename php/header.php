<?php
require_once __DIR__ . '/db.php';
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? '';
$dashboardUrl = '/login.php';
if ($userRole === 'provider') $dashboardUrl = '/provider/dashboard.php';
elseif ($userRole === 'group') $dashboardUrl = '/group/dashboard.php';
elseif ($userRole === 'admin') $dashboardUrl = '/admin/dashboard.php';

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? escape($pageTitle) . ' - Sevam' : 'SEVAM - Connecting Surplus Food with Those in Need' ?></title>
    <meta name="description" content="Sevam connects food providers with social working groups to reduce food wastage and distribute meals to people in need.">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-container">
            <!-- Zone 1: Wordmark -->
            <a href="/" class="brand-logo">
                <span class="brand-mark">S</span>
                <span>SEVAM</span>
            </a>

            <!-- Zone 2: Nav Links -->
            <nav class="nav-menu">
                <a href="/" class="nav-link <?= ($currentPage === 'index.php') ? 'active' : '' ?>">Home</a>
                <a href="/about.php" class="nav-link <?= ($currentPage === 'about.php') ? 'active' : '' ?>">About</a>
                <a href="/#how-it-works" class="nav-link">How It Works</a>
                <a href="/contact.php" class="nav-link <?= ($currentPage === 'contact.php') ? 'active' : '' ?>">Contact</a>
            </nav>

            <!-- Zone 3: Primary Actions -->
            <div class="nav-actions">
                <?php if ($isLoggedIn): ?>
                    <a href="<?= $dashboardUrl ?>" class="btn btn-secondary btn-sm">My Dashboard</a>
                    <a href="/logout.php" class="btn btn-primary btn-sm">Logout</a>
                <?php else: ?>
                    <a href="/login.php" class="btn btn-secondary btn-sm">Login</a>
                    <a href="/register.php" class="btn btn-primary btn-sm">Register</a>
                <?php endif; ?>
            </div>

            <!-- Mobile Hamburger Button -->
            <button class="mobile-menu-btn" aria-label="Toggle navigation menu">
                ☰
            </button>
        </div>
    </header>
    <main class="site-main">
