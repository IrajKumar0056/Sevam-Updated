<?php
/**
 * SQLite Database Initializer for Sevam
 * Automatically creates and seeds database/sevam.sqlite if MySQL is unavailable.
 */

$dbPath = __DIR__ . '/sevam.sqlite';

$needsInit = !file_exists($dbPath) || filesize($dbPath) < 1024;

$pdo = new PDO("sqlite:{$dbPath}", null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$pdo->exec("PRAGMA journal_mode = WAL;");
$pdo->exec("PRAGMA foreign_keys = ON;");

if ($needsInit) {
    echo "Creating SQLite tables and seeding initial data...\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS contact_messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            phone TEXT DEFAULT NULL,
            subject TEXT NOT NULL,
            message TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS food_categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT DEFAULT NULL,
            status TEXT DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            role TEXT NOT NULL,
            status TEXT DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS food_providers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            business_name TEXT NOT NULL,
            owner_name TEXT NOT NULL,
            phone TEXT NOT NULL,
            address TEXT NOT NULL,
            city TEXT NOT NULL,
            state TEXT NOT NULL,
            business_type TEXT NOT NULL,
            fssai_status TEXT DEFAULT 'Not Certified',
            fssai_number TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS social_working_groups (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            group_name TEXT NOT NULL,
            representative_name TEXT NOT NULL,
            phone TEXT NOT NULL,
            address TEXT NOT NULL,
            city TEXT NOT NULL,
            state TEXT NOT NULL,
            organization_type TEXT NOT NULL,
            ngo_status TEXT DEFAULT 'Registered',
            darpan_id TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS food_listings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            provider_id INTEGER NOT NULL,
            category_id INTEGER NOT NULL,
            food_name TEXT NOT NULL,
            food_type TEXT DEFAULT 'Veg',
            quantity REAL NOT NULL,
            available_quantity REAL NOT NULL,
            quantity_unit TEXT NOT NULL,
            price REAL NOT NULL,
            prep_date TEXT NOT NULL,
            available_date TEXT NOT NULL,
            available_start_time TEXT NOT NULL,
            available_end_time TEXT NOT NULL,
            expiry_date TEXT NOT NULL,
            expiry_time TEXT NOT NULL,
            food_description TEXT DEFAULT NULL,
            image_url TEXT DEFAULT NULL,
            pickup_info TEXT NOT NULL,
            status TEXT DEFAULT 'Available',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES food_categories (id),
            FOREIGN KEY (provider_id) REFERENCES food_providers (id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS food_requests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            food_id INTEGER NOT NULL,
            group_id INTEGER NOT NULL,
            requested_quantity REAL NOT NULL,
            requested_date TEXT NOT NULL,
            requested_time TEXT NOT NULL,
            message TEXT DEFAULT NULL,
            status TEXT DEFAULT 'Pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (food_id) REFERENCES food_listings (id) ON DELETE CASCADE,
            FOREIGN KEY (group_id) REFERENCES social_working_groups (id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS services (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT NOT NULL,
            icon TEXT DEFAULT 'food',
            status TEXT DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // Seed contact messages
    $pdo->exec("
        INSERT OR IGNORE INTO contact_messages (id, name, email, phone, subject, message, created_at) VALUES
        (1, 'Sunil Joshi', 'sunil.joshi@mumbaicaterers.com', '+91 99887 76655', 'Interested in joining as hotel partner', 'We operate a 50-room hotel with a daily banquet hall. We would like to register 3 of our kitchens under Sevam to reduce food waste.', '2026-09-27 16:04:21');
    ");

    // Seed categories
    $pdo->exec("
        INSERT OR IGNORE INTO food_categories (id, name, description, status, created_at) VALUES
        (1, 'Cooked Meals', 'Freshly prepared hot wholesome meals including rice, dal, and gravies', 'active', '2026-09-27 16:04:21'),
        (2, 'Rice & Biryani', 'Surplus steamed rice, jeera rice, pulao, and vegetarian or mild biryanis', 'active', '2026-09-27 16:04:21'),
        (3, 'Rotis & Breads', 'Surplus freshly made chapatis, rotis, parathas, and naans', 'active', '2026-09-27 16:04:21'),
        (4, 'Vegetable Curries', 'Dry and gravy vegetable curries, paneer dishes, and mixed lentils', 'active', '2026-09-27 16:04:21'),
        (5, 'Bakery & Packed Goods', 'Fresh breads, buns, sandwiches, and sealed packaged dry foods', 'active', '2026-09-27 16:04:21'),
        (6, 'Catering & Event Surplus', 'High-volume banquet surplus food packed hygienically after functions', 'active', '2026-09-27 16:04:21');
    ");

    // Seed users
    $pdo->exec("
        INSERT OR IGNORE INTO users (id, username, email, password, role, status, created_at) VALUES
        (1, 'admin', 'admin@sevam.org', '$2y$10\$lUuKXDPVotPJoLWFzXYWduUXFMAR3NM6Mz0oRhqc0lL.VjOyrQCaW', 'admin', 'active', '2026-09-27 16:04:21'),
        (2, 'annapurna_kitchen', 'provider@annapurna.com', '$2y$10\$jcoxcFsTXuuPxESzgU/LnOPsT6/axlct.xPKT9Db.iyQOWav33/b6', 'provider', 'active', '2026-09-27 16:04:21'),
        (3, 'royal_caterers', 'info@royalcaterers.in', '$2y$10\$jcoxcFsTXuuPxESzgU/LnOPsT6/axlct.xPKT9Db.iyQOWav33/b6', 'provider', 'active', '2026-09-27 16:04:21'),
        (4, 'hope_foundation', 'contact@hopefoundation.org', '$2y$10\$K1UJQVG39Vd61v2VIPJY0e20YEDEebZckE1.gx1mhHXdZK3ENJEQW', 'group', 'active', '2026-09-27 16:04:21'),
        (5, 'seva_youth_circle', 'action@sevayouth.org', '$2y$10\$K1UJQVG39Vd61v2VIPJY0e20YEDEebZckE1.gx1mhHXdZK3ENJEQW', 'group', 'active', '2026-09-27 16:04:21'),
        (6, 'greenleaf_kitchen', 'anand@greenleaf.com', '$2y$10\$lYAXskJomtyPIuR7TfHw6eQ0xP89Pi9v4PSm6VLGr.ZR4lcMZlJg6', 'provider', 'active', '2026-09-27 16:13:27');
    ");

    // Seed food providers
    $pdo->exec("
        INSERT OR IGNORE INTO food_providers (id, user_id, business_name, owner_name, phone, address, city, state, business_type, fssai_status, fssai_number, created_at) VALUES
        (1, 2, 'Annapurna Commercial Kitchen', 'Ramesh Sharma', '+91 98201 12345', 'Shop 14, Lotus Grand, Link Road, Andheri West', 'Mumbai', 'Maharashtra', 'Restaurant & Catering', 'Certified', '10018022007892', '2026-09-27 16:04:21'),
        (2, 3, 'Royal Feast Banquets', 'Vikramaditya Mehta', '+91 98110 54321', 'Plot 42, Civil Lines Road', 'New Delhi', 'Delhi', 'Banquet & Events', 'Certified', '10020011004318', '2026-09-27 16:04:21'),
        (3, 6, 'Green Leaf Kitchen', 'Anand Verma', '919876543210', '12 MG Road', 'Pune', 'Maharashtra', 'Restaurant', 'Certified', '10019022001122', '2026-09-27 16:13:27');
    ");

    // Seed social working groups
    $pdo->exec("
        INSERT OR IGNORE INTO social_working_groups (id, user_id, group_name, representative_name, phone, address, city, state, organization_type, ngo_status, darpan_id, created_at) VALUES
        (1, 4, 'Hope Welfare Foundation', 'Priya Deshmukh', '+91 98331 88765', 'Building 4, Sector 7, Vashi', 'Navi Mumbai', 'Maharashtra', 'Registered NGO', 'Registered', 'MH/2021/0291823', '2026-09-27 16:04:21'),
        (2, 5, 'Seva Youth Volunteer Circle', 'Amitabh Verma', '+91 98102 44321', '12 Community Centre, Hauz Khas', 'New Delhi', 'Delhi', 'Community Volunteer Group', 'Community Volunteer Group', 'DL/2022/0119834', '2026-09-27 16:04:21');
    ");

    // Seed food listings
    $pdo->exec("
        INSERT OR IGNORE INTO food_listings (id, provider_id, category_id, food_name, food_type, quantity, available_quantity, quantity_unit, price, prep_date, available_date, available_start_time, available_end_time, expiry_date, expiry_time, food_description, image_url, pickup_info, status, created_at) VALUES
        (1, 1, 2, 'Steamed Basmati Rice & Dal Makhani', 'Veg', 25.00, 15.00, 'kg', 600.00, '2026-09-27', '2026-09-27', '17:00:00', '21:30:00', '2026-09-28', '02:00:00', 'Surplus from our lunch batch, maintained in heated commercial food warmers. Clean, rich basmati rice and fresh slow-cooked black dal makhani.', 'images/hero_food_share.jpg', 'Pickup directly from rear service entrance of Annapurna Kitchen. Bring food grade containers/vessels.', 'Available', '2026-09-27 16:04:21'),
        (2, 1, 3, 'Fresh Tawa Chapatis (Soft Wheat Rotis)', 'Veg', 150.00, 150.00, 'pieces', 300.00, '2026-09-27', '2026-09-27', '18:00:00', '22:00:00', '2026-09-28', '09:00:00', '150 freshly baked soft whole wheat chapatis lightly coated with pure ghee. Wrapped in aluminium foil packs of 25 each.', 'images/provider_kitchen.jpg', 'Collect from kitchen counter with your sanitized thermal bags.', 'Available', '2026-09-27 16:04:21'),
        (3, 2, 4, 'Paneer Butter Masala & Mixed Vegetable Subzi', 'Veg', 18.00, 18.00, 'kg', 850.00, '2026-09-27', '2026-09-27', '19:00:00', '23:00:00', '2026-09-28', '03:00:00', 'Fresh surplus from an afternoon banquet. Premium cottage cheese in tomato cashew gravy and seasonal mixed vegetables.', 'images/community_distrib.jpg', 'Royal Feast loading bay gate 2. Contact supervisor Mr. Manoj upon arrival.', 'Available', '2026-09-27 16:04:21'),
        (4, 1, 1, 'Vegetable Pulao', 'Veg', 12.00, 7.00, 'kg', 350.00, '2026-09-27', '2026-09-27', '18:00:00', '21:00:00', '2026-09-28', '02:00:00', 'Fragrant basmati rice cooked with fresh seasonal vegetables and roasted spices', 'images/provider_kitchen.jpg', 'Annapurna kitchen rear door', 'Available', '2026-09-27 16:12:31');
    ");

    // Seed food requests
    $pdo->exec("
        INSERT OR IGNORE INTO food_requests (id, food_id, group_id, requested_quantity, requested_date, requested_time, message, status, created_at, updated_at) VALUES
        (1, 1, 1, 10.00, '2026-09-27', '18:30:00', 'We are organizing an evening food drive for families residing near the station shelter. We can arrive with clean steel containers by 6:30 PM.', 'Accepted', '2026-09-27 16:04:21', '2026-09-27 16:04:21'),
        (2, 4, 1, 5.00, '2026-09-27', '19:00:00', 'Hope Foundation evening shelter distribution', 'Accepted', '2026-09-27 16:12:48', '2026-09-27 16:13:07');
    ");

    // Seed services
    $pdo->exec("
        INSERT OR IGNORE INTO services (id, title, description, icon, status, created_at) VALUES
        (1, 'Surplus Food Listing', 'Commercial kitchens, caterers, and restaurants list surplus food with real-time quantities, units, and clear time slots.', 'list', 'active', '2026-09-27 16:04:21'),
        (2, 'Verified Partner Network', 'Transparent profiles showcasing FSSAI food safety status, business details, and NGO Darpan registration info.', 'shield', 'active', '2026-09-27 16:04:21'),
        (3, 'Direct Coordination', 'Transparent request workflows with automatic quantity balancing, eliminating middleman cuts and unnecessary delays.', 'handshake', 'active', '2026-09-27 16:04:21'),
        (4, 'Waste Prevention Tracking', 'Real-time dashboard metrics tracking rescued meals, active food slots, and completed community handovers.', 'chart', 'active', '2026-09-27 16:04:21');
    ");

    echo "SQLite database successfully created and seeded.\n";
} else {
    echo "SQLite database already initialized.\n";
}
