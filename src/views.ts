import { db } from './store.ts';
import type { FoodListing, User } from './store.ts';

export function renderLayout(title: string, user: User | null, content: string, activeNav = ''): string {
  let navLinks = '';
  if (!user) {
    navLinks = `
      <a href="/" class="nav-link ${activeNav === 'home' ? 'active' : ''}">Home</a>
      <a href="/about" class="nav-link ${activeNav === 'about' ? 'active' : ''}">About</a>
      <a href="/contact" class="nav-link ${activeNav === 'contact' ? 'active' : ''}">Contact</a>
      <a href="/login" class="btn btn-secondary btn-sm">Sign In</a>
      <a href="/register" class="btn btn-primary btn-sm">Register</a>
    `;
  } else if (user.role === 'provider') {
    navLinks = `
      <a href="/provider/dashboard" class="nav-link ${activeNav === 'dashboard' ? 'active' : ''}">Dashboard</a>
      <a href="/provider/add-food" class="nav-link ${activeNav === 'add-food' ? 'active' : ''}">+ Add Food</a>
      <a href="/provider/my-food" class="nav-link ${activeNav === 'my-food' ? 'active' : ''}">My Listings</a>
      <a href="/provider/requests" class="nav-link ${activeNav === 'requests' ? 'active' : ''}">Requests</a>
      <a href="/provider/profile" class="nav-link ${activeNav === 'profile' ? 'active' : ''}">Profile</a>
      <span style="font-size:0.85rem; color:var(--color-text-muted); margin-left:0.5rem;">(${user.username})</span>
      <a href="/logout" class="btn btn-secondary btn-sm" style="margin-left:0.5rem;">Logout</a>
    `;
  } else if (user.role === 'group') {
    navLinks = `
      <a href="/group/dashboard" class="nav-link ${activeNav === 'dashboard' ? 'active' : ''}">Dashboard</a>
      <a href="/group/food-availability" class="nav-link ${activeNav === 'availability' ? 'active' : ''}">Available Food</a>
      <a href="/group/my-requests" class="nav-link ${activeNav === 'requests' ? 'active' : ''}">My Claims</a>
      <a href="/group/profile" class="nav-link ${activeNav === 'profile' ? 'active' : ''}">Profile</a>
      <span style="font-size:0.85rem; color:var(--color-text-muted); margin-left:0.5rem;">(${user.username})</span>
      <a href="/logout" class="btn btn-secondary btn-sm" style="margin-left:0.5rem;">Logout</a>
    `;
  } else if (user.role === 'admin') {
    navLinks = `
      <a href="/admin/dashboard" class="nav-link ${activeNav === 'dashboard' ? 'active' : ''}">Dashboard</a>
      <a href="/admin/users" class="nav-link ${activeNav === 'users' ? 'active' : ''}">Users</a>
      <a href="/admin/foods" class="nav-link ${activeNav === 'foods' ? 'active' : ''}">Foods</a>
      <a href="/admin/requests" class="nav-link ${activeNav === 'requests' ? 'active' : ''}">Requests</a>
      <a href="/admin/categories" class="nav-link ${activeNav === 'categories' ? 'active' : ''}">Categories</a>
      <a href="/admin/messages" class="nav-link ${activeNav === 'messages' ? 'active' : ''}">Messages</a>
      <span style="font-size:0.85rem; color:var(--color-text-muted); margin-left:0.5rem;">(${user.username})</span>
      <a href="/logout" class="btn btn-secondary btn-sm" style="margin-left:0.5rem;">Logout</a>
    `;
  }

  return `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${title} - Sevam</title>
    <meta name="description" content="Sevam connects food providers with social working groups to reduce food wastage and distribute meals to people in need.">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-container">
            <a href="/" class="brand-logo">
                <span class="brand-mark">S</span>
                <span>SEVAM</span>
            </a>
            <nav class="nav-actions">
                ${navLinks}
            </nav>
        </div>
    </header>
    <main>
        ${content}
    </main>
    <footer class="site-footer">
        <div class="container footer-content">
            <div class="footer-brand">
                <div class="brand-logo" style="margin-bottom:0.5rem;">
                    <span class="brand-mark" style="background:#fff; color:var(--color-primary);">S</span>
                    <span style="color:#fff;">SEVAM</span>
                </div>
                <p style="color:rgba(255,255,255,0.7); font-size:0.9rem; max-width:320px;">
                    Bridging surplus food with communities in need across urban centers.
                </p>
            </div>
            <div class="footer-links">
                <a href="/">Home</a>
                <a href="/about">About Us</a>
                <a href="/contact">Contact</a>
                <a href="/login">Portal Login</a>
            </div>
        </div>
        <div class="container" style="margin-top:2rem; border-top:1px solid rgba(255,255,255,0.1); padding-top:1rem; font-size:0.8rem; color:rgba(255,255,255,0.5); text-align:center;">
            &copy; 2026 Sevam Redistribution Platform. All rights reserved.
        </div>
    </footer>
    <script src="/js/script.js"></script>
</body>
</html>`;
}
