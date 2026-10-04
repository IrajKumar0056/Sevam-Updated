<?php
require_once __DIR__ . '/php/db.php';
$pageTitle = "About Sevam";
require_once __DIR__ . '/php/header.php';
?>

<div class="container" style="padding-top: 3.5rem; padding-bottom: 5rem;">
    <div style="max-width: 820px; margin: 0 auto;">
        <span class="section-kicker">Our Mission & Purpose</span>
        <h1 style="font-size: 2.5rem; margin-bottom: 1.5rem;">Connecting Food Providers with Social Working Groups</h1>
        
        <p style="font-size: 1.15rem; color: var(--color-text-muted); line-height: 1.7; margin-bottom: 2.5rem;">
            In urban centers across the country, tons of freshly prepared, untouched surplus food from banquets, hotel kitchens, restaurants, and catering services are discarded daily. At the exact same time, grassroots social working groups, community volunteer networks, and local shelters struggle to source nutritious meals for vulnerable families.
        </p>

        <div style="margin-bottom: 3rem; border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--color-border); box-shadow: var(--shadow-card);">
            <img src="/images/hero_food_share.jpg" alt="Sevam Community Food Rescue" style="width: 100%; aspect-ratio: 16/9; object-fit: cover;" referrerpolicy="no-referrer">
        </div>

        <div class="card" style="margin-bottom: 2.5rem; background: #ffffff;">
            <h2 style="font-size: 1.5rem; margin-bottom: 1rem; color: var(--color-primary);">Why Sevam Exists</h2>
            <p style="margin-bottom: 1rem;">
                Traditional charity donation models often rely on informal contacts or uncoordinated volunteers arriving at unpredictable hours. For commercial kitchens, this introduces operational chaos, potential hygiene risks, and complete financial write-offs.
            </p>
            <p>
                <strong>Sevam introduces a sustainable alternative:</strong> Food providers list surplus dishes with specific preparation times, safe temperature windows, and reasonable cost-recovery fees. Social working groups can plan their food drives, select the exact portion quantities needed, and collect the food smoothly within designated pickup windows.
            </p>
        </div>

        <div class="cards-grid-2" style="margin-bottom: 3rem;">
            <div class="card">
                <h3 style="font-size: 1.2rem; margin-bottom: 0.5rem; color: var(--color-text-main);">For Food Providers</h3>
                <ul style="padding-left: 1.25rem; color: var(--color-text-muted); display:flex; flex-direction:column; gap:0.5rem; font-size:0.95rem;">
                    <li>Recover part of your ingredient and utility expenditure</li>
                    <li>Avoid throwing away safe, usable food</li>
                    <li>Establish a verified record of community impact</li>
                    <li>Set clear pickup rules and time constraints</li>
                    <li>Display your FSSAI food safety status with pride</li>
                </ul>
            </div>

            <div class="card">
                <h3 style="font-size: 1.2rem; margin-bottom: 0.5rem; color: var(--color-text-main);">For Social Working Groups</h3>
                <ul style="padding-left: 1.25rem; color: var(--color-text-muted); display:flex; flex-direction:column; gap:0.5rem; font-size:0.95rem;">
                    <li>Save hours of tedious manual kitchen cooking</li>
                    <li>Access restaurant-grade, hygienic meals at fractional cost</li>
                    <li>Pre-book food lots with real-time available quantities</li>
                    <li>Obtain verified food provider location and contact points</li>
                    <li>Maximize the number of families served per rupee spent</li>
                </ul>
            </div>
        </div>

        <div class="card" style="background-color: var(--color-primary-subtle); border-color: var(--color-primary-border); padding: 2rem;">
            <h3 style="font-size: 1.3rem; margin-bottom: 0.75rem; color: var(--color-primary);">Transparency & Safety First</h3>
            <p style="color: var(--color-text-main); font-size: 0.95rem; margin-bottom: 1rem;">
                Sevam requires all Food Providers to state their FSSAI licensing and verification details. Likewise, Social Working Groups provide their NGO registration and Darpan IDs, ensuring both sides operate with trust, dignity, and accountability.
            </p>
            <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                <a href="/register.php" class="btn btn-primary btn-sm">Join the Network Today</a>
                <a href="/contact.php" class="btn btn-secondary btn-sm">Contact Our Team</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/php/footer.php'; ?>
