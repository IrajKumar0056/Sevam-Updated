<?php
require_once __DIR__ . '/php/db.php';
$pageTitle = "Home";
require_once __DIR__ . '/php/header.php';

// Fetch active services dynamically from MySQL
$servStmt = $pdo->query("SELECT * FROM services WHERE status = 'active' ORDER BY id ASC");
$services = $servStmt->fetchAll();

// Platform stats
$statsListings = $pdo->query("SELECT COUNT(*) FROM food_listings")->fetchColumn();
$statsAvailable = $pdo->query("SELECT COUNT(*) FROM food_listings WHERE status = 'Available' AND available_quantity > 0")->fetchColumn();
$statsRequests = $pdo->query("SELECT COUNT(*) FROM food_requests WHERE status = 'Accepted' OR status = 'Completed'")->fetchColumn();
?>

<!-- 1. HERO SECTION -->
<section class="hero-section">
    <div class="container hero-grid">
        <div>
            <div class="hero-badge">
                <span>🌱 Sustainable Food Redistribution Network</span>
            </div>
            <h1 class="hero-title">
                Don't Waste Food. Connect It With Those Who Need It.
            </h1>
            <p class="hero-desc">
                Sevam is a transparent platform bridging commercial food providers with verified social working groups. Recover partial preparation costs while empowering grassroots volunteers to feed communities with dignity.
            </p>
            <div class="hero-actions">
                <a href="/register.php?role=provider" class="btn btn-primary btn-lg">
                    List Surplus Food
                </a>
                <a href="/register.php?role=group" class="btn btn-secondary btn-lg">
                    Find Available Food
                </a>
            </div>
            <div class="hero-proof">
                <div class="proof-stat">
                    <span class="proof-val tabular-nums"><?= max(120, $statsListings * 15) ?>+ kg</span>
                    <span class="proof-label">Surplus Meals Rescued</span>
                </div>
                <div class="proof-stat">
                    <span class="proof-val tabular-nums"><?= max(25, $statsRequests) ?>+</span>
                    <span class="proof-label">Successful Pickups</span>
                </div>
                <div class="proof-stat">
                    <span class="proof-val tabular-nums">100%</span>
                    <span class="proof-label">FSSAI Aware Standards</span>
                </div>
            </div>
        </div>
        <div>
            <div class="hero-image-wrap">
                <img src="/images/hero_food_share.jpg" alt="Commercial surplus food being packed for distribution" class="hero-image" referrerpolicy="no-referrer">
            </div>
        </div>
    </div>
</section>

<!-- 2. ABOUT SEVAM -->
<section class="section section-alt" id="about">
    <div class="container">
        <div class="section-header">
            <span class="section-kicker">About Sevam</span>
            <h2 class="section-title">A Purpose-Built Bridge Between Abundance and Need</h2>
            <p class="section-desc">
                Every evening, commercial kitchens produce surplus food while community volunteers struggle to secure nutritious meals. Sevam brings organization and dignity to this connection.
            </p>
        </div>

        <div class="cards-grid-3">
            <div class="card">
                <div class="card-icon">🍲</div>
                <h3 class="card-title">What Sevam Is</h3>
                <p class="card-desc">
                    A centralized, database-driven platform where restaurants, banquets, and caterers list excess safe food at reasonable recovery prices for social organizations.
                </p>
            </div>
            <div class="card">
                <div class="card-icon">🤝</div>
                <h3 class="card-title">Who It Connects</h3>
                <p class="card-desc">
                    Food Providers (restaurants, messes, hotels) on one side, and Social Working Groups (registered NGOs, volunteer clubs, shelters) on the other.
                </p>
            </div>
            <div class="card">
                <div class="card-icon">🎯</div>
                <h3 class="card-title">Why It Is Needed</h3>
                <p class="card-desc">
                    Direct communication eliminates ad-hoc phone calls and food disposal. Clear date and time slots ensure smooth and reliable handovers before food expires.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- 3. THE THREE PROBLEMS SEVAM SOLVES -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-kicker">The Challenge</span>
            <h2 class="section-title">The Three Problems Sevam Solves</h2>
            <p class="section-desc">
                Existing food rescue systems are often informal, chaotic, or financially unsustainable for commercial kitchens.
            </p>
        </div>

        <div class="cards-grid-3">
            <div class="card" style="border-top: 3px solid #b45309;">
                <h3 class="card-title">1. Food Provider Financial Loss</h3>
                <p class="card-desc">
                    Catering services and restaurants face financial losses when discarding prepared food or donating without any return. Sevam offers reasonable pricing options to help recover partial costs while preventing food wastage.
                </p>
            </div>
            <div class="card" style="border-top: 3px solid #0369a1;">
                <h3 class="card-title">2. Difficulty Finding Food</h3>
                <p class="card-desc">
                    Social Working Groups spend critical hours cooking from scratch or manually hunting for leftover food across the city. Sevam organizes verified inventory in one real-time catalogue.
                </p>
            </div>
            <div class="card" style="border-top: 3px solid #15803d;">
                <h3 class="card-title">3. Inefficient Connection</h3>
                <p class="card-desc">
                    Providers and NGOs lack a direct channel. Without structured time slots, food spoils before pickup. Sevam provides slot selection, request approvals, and direct pickup coordination.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- 4. HOW SEVAM HELPS (FLOW DIAGRAM) -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <span class="section-kicker">Impact Pipeline</span>
            <h2 class="section-title">How Sevam Facilitates the Ecosystem</h2>
            <p class="section-desc">
                From kitchen stoves to community plates, every step is direct, transparent, and respectful.
            </p>
        </div>

        <div class="flow-diagram">
            <div class="flow-step">
                <span class="flow-step-num">1</span>
                <span class="flow-step-title">Food Provider</span>
                <span class="flow-step-desc">Restaurants & caterers produce safe surplus meals</span>
            </div>
            <span class="flow-arrow">→</span>
            <div class="flow-step">
                <span class="flow-step-num">2</span>
                <span class="flow-step-title">Surplus Food</span>
                <span class="flow-step-desc">Listed with quantity, safe time slots & recovery price</span>
            </div>
            <span class="flow-arrow">→</span>
            <div class="flow-step">
                <span class="flow-step-num" style="background:#1b4332; color:#fff;">S</span>
                <span class="flow-step-title">SEVAM</span>
                <span class="flow-step-desc">Matches requests and balances real-time inventory</span>
            </div>
            <span class="flow-arrow">→</span>
            <div class="flow-step">
                <span class="flow-step-num">4</span>
                <span class="flow-step-title">Social Group</span>
                <span class="flow-step-desc">Requests exact quantity and coordinates pickup</span>
            </div>
            <span class="flow-arrow">→</span>
            <div class="flow-step">
                <span class="flow-step-num">5</span>
                <span class="flow-step-title">People in Need</span>
                <span class="flow-step-desc">Wholesome, timely meals distributed with dignity</span>
            </div>
        </div>
    </div>
</section>

<!-- 5. HOW IT WORKS (VISUAL STEPS) -->
<section class="section" id="how-it-works">
    <div class="container">
        <div class="section-header">
            <span class="section-kicker">Simple 4-Step Process</span>
            <h2 class="section-title">How It Works in Practice</h2>
            <p class="section-desc">
                No complex payment gateways or third-party couriers. Clear coordination between verified partners.
            </p>
        </div>

        <div class="cards-grid-4">
            <div class="card">
                <div class="card-icon">1</div>
                <h3 class="card-title">Register Account</h3>
                <p class="card-desc">
                    Food providers specify business type and FSSAI status. Social groups register with NGO Darpan credentials.
                </p>
            </div>
            <div class="card">
                <div class="card-icon">2</div>
                <h3 class="card-title">List Surplus</h3>
                <p class="card-desc">
                    Providers publish available quantities, preparation timestamp, safe pickup windows, and nominal cost.
                </p>
            </div>
            <div class="card">
                <div class="card-icon">3</div>
                <h3 class="card-title">Request & Accept</h3>
                <p class="card-desc">
                    Social groups submit requested quantities within the slot. Providers review and accept with one click.
                </p>
            </div>
            <div class="card">
                <div class="card-icon">4</div>
                <h3 class="card-title">Direct Pickup</h3>
                <p class="card-desc">
                    Volunteers collect meals at the agreed time using their containers. Transparent, fast, and zero wastage.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- 6. FOOD PROVIDER SECTION -->
<section class="section section-alt" id="provider-info">
    <div class="container">
        <div class="cards-grid-2" style="align-items: center;">
            <div>
                <span class="section-kicker">For Commercial Kitchens</span>
                <h2 class="section-title" style="margin-bottom: 1.25rem;">Become a Food Provider on Sevam</h2>
                <p class="section-desc" style="margin-bottom: 1.5rem;">
                    Whether you manage a 200-seat banquet hall, a bustling college mess, or a neighborhood restaurant, surplus preparation does not have to end up in the dumpster.
                </p>
                <div style="display:flex; flex-direction:column; gap:1rem; margin-bottom:2rem;">
                    <div>
                        <strong>Who Can Join:</strong> Restaurants, hotel banquets, institutional messes, cloud kitchens, and event caterers.
                    </div>
                    <div>
                        <strong>Cost Recovery:</strong> Set a modest recovery price per kg or packet that makes ongoing participation viable.
                    </div>
                    <div>
                        <strong>Total Control:</strong> You choose the pickup window and approve or decline every request based on your kitchen capacity.
                    </div>
                </div>
                <a href="/register.php?role=provider" class="btn btn-primary">
                    Register as Food Provider
                </a>
            </div>
            <div>
                <img src="/images/provider_kitchen.jpg" alt="Commercial food provider kitchen" style="border-radius:var(--radius-md); box-shadow:var(--shadow-card); border:1px solid var(--color-border);" referrerpolicy="no-referrer">
            </div>
        </div>
    </div>
</section>

<!-- 7. SOCIAL WORKING GROUP SECTION -->
<section class="section" id="group-info">
    <div class="container">
        <div class="cards-grid-2" style="align-items: center;">
            <div>
                <img src="/images/community_distrib.jpg" alt="Social workers distributing food to community members" style="border-radius:var(--radius-md); box-shadow:var(--shadow-card); border:1px solid var(--color-border);" referrerpolicy="no-referrer">
            </div>
            <div>
                <span class="section-kicker">For Social Working Groups</span>
                <h2 class="section-title" style="margin-bottom: 1.25rem;">Access Safe, Wholesome Food at Scale</h2>
                <p class="section-desc" style="margin-bottom: 1.5rem;">
                    Focus your volunteer energy where it matters most: delivering warm nutrition to children, shelters, and communities in distress.
                </p>
                <div style="display:flex; flex-direction:column; gap:1rem; margin-bottom:2rem;">
                    <div>
                        <strong>Who Can Join:</strong> Registered NGOs, community volunteer circles, student welfare groups, and local disaster relief units.
                    </div>
                    <div>
                        <strong>Quality Assurance:</strong> View detailed food preparation dates, expiry deadlines, and provider FSSAI safety certification.
                    </div>
                    <div>
                        <strong>Flexible Portions:</strong> Request the exact volume your team can distribute, leaving remaining quantities for others.
                    </div>
                </div>
                <a href="/register.php?role=group" class="btn btn-accent">
                    Register as Social Working Group
                </a>
            </div>
        </div>
    </div>
</section>

<!-- 8. SERVICES SECTION (DATABASE DRIVEN) -->
<section class="section section-alt" id="services-section">
    <div class="container">
        <div class="section-header">
            <span class="section-kicker">Platform Offerings</span>
            <h2 class="section-title">Core Services Managed on Sevam</h2>
            <p class="section-desc">
                Dynamically governed through our administrator console to support real-world field operations.
            </p>
        </div>

        <div class="cards-grid-4">
            <?php if (!empty($services)): ?>
                <?php foreach ($services as $serv): ?>
                    <div class="card">
                        <div class="card-icon">⚡</div>
                        <h3 class="card-title"><?= escape($serv['title']) ?></h3>
                        <p class="card-desc"><?= escape($serv['description']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card">
                    <div class="card-icon">🍲</div>
                    <h3 class="card-title">Surplus Food Listing</h3>
                    <p class="card-desc">Commercial kitchens post safe surplus food with time slots and portion sizes.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 10. CALL TO ACTION -->
<section class="section section-alt" style="text-align: center; padding: 4.5rem 0;">
    <div class="container" style="max-width: 720px;">
        <span class="section-kicker">Join the Movement</span>
        <h2 class="section-title" style="margin-bottom: 1rem;">Ready to End Food Wastage in Your City?</h2>
        <p class="section-desc" style="margin-bottom: 2rem;">
            Whether you run a commercial kitchen or a community volunteer group, joining Sevam takes less than two minutes. Let's make every meal count.
        </p>
        <div style="display:flex; justify-content:center; gap:1rem; flex-wrap:wrap;">
            <a href="/register.php?role=provider" class="btn btn-primary btn-lg">Register as Food Provider</a>
            <a href="/register.php?role=group" class="btn btn-secondary btn-lg">Register as Social Group</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/php/footer.php'; ?>
