<?php
require_once __DIR__ . '/../php/db.php';
$pageTitle = "Food Categories";
require_once __DIR__ . '/header.php';

// Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO food_categories (name, description, status) VALUES (?, ?, 'active')");
        $stmt->execute([$name, $description]);
        $catId = $pdo->lastInsertId();
        set_flash('admin_success', "Category '{$name}' created.");
        require_once __DIR__ . '/../php/supabase.php';
        supabase_record_action('category_created', ['category_id' => $catId, 'name' => $name], 'category', $catId);
        header("Location: /admin/categories.php");
        exit;
    }
}

// Toggle status or delete
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $catId = intval($_GET['id']);
    require_once __DIR__ . '/../php/supabase.php';

    if ($action === 'toggle') {
        $curr = $pdo->query("SELECT status FROM food_categories WHERE id = {$catId}")->fetchColumn();
        $newStatus = ($curr === 'active') ? 'inactive' : 'active';
        $pdo->prepare("UPDATE food_categories SET status = ? WHERE id = ?")->execute([$newStatus, $catId]);
        set_flash('admin_success', "Category status updated.");
        supabase_record_action('category_status_toggled', ['category_id' => $catId, 'status' => $newStatus], 'category', $catId);
    } elseif ($action === 'delete') {
        // Check if category is used
        $count = $pdo->query("SELECT COUNT(*) FROM food_listings WHERE category_id = {$catId}")->fetchColumn();
        if ($count > 0) {
            set_flash('admin_error', "Cannot delete category: {$count} food listings are currently using it. Please deactivate it instead.");
        } else {
            $pdo->prepare("DELETE FROM food_categories WHERE id = ?")->execute([$catId]);
            set_flash('admin_success', "Category deleted.");
            supabase_record_action('category_deleted', ['category_id' => $catId], 'category', $catId);
        }
    }
    header("Location: /admin/categories.php");
    exit;
}

$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM food_listings WHERE category_id = c.id) as item_count 
    FROM food_categories c ORDER BY c.id ASC")->fetchAll();
?>

<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Food Categories Management</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
            Configure food classification categories available when providers list surplus food.
        </p>
    </div>
</div>

<?php render_flash('admin_success'); ?>
<?php render_flash('admin_error'); ?>

<div class="cards-grid-2" style="align-items: start; margin-bottom: 2rem;">
    <!-- Add Category Form -->
    <div class="form-card">
        <h3 style="font-size: 1.15rem; margin-bottom: 1rem;">Add Food Category</h3>
        <form action="/admin/categories.php" method="POST">
            <input type="hidden" name="action" value="add">

            <div class="form-group">
                <label class="form-label" for="name">Category Name <span class="req">*</span></label>
                <input type="text" id="name" name="name" class="form-input" placeholder="e.g. Cooked Meals, Rice & Breads..." required>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Short Description</label>
                <textarea id="description" name="description" class="form-textarea" rows="2" placeholder="Describe the types of food included in this group..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                Save Category
            </button>
        </form>
    </div>

    <!-- Overview info -->
    <div class="card" style="background-color: var(--color-surface-subtle);">
        <h4 style="font-size: 1rem; margin-bottom: 0.5rem;">Provider Integration</h4>
        <p style="font-size: 0.875rem; margin-bottom: 0.75rem;">
            When food providers add a surplus dish, they choose from these active categories. Social Working Groups can also filter available listings using these categories.
        </p>
        <p style="font-size: 0.85rem; color: var(--color-text-muted);">
            Active categories: <strong><?= count(array_filter($categories, fn($c) => $c['status'] === 'active')) ?></strong>
        </p>
    </div>
</div>

<div class="table-wrap">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th>Associated Listings</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td class="tabular-nums">#<?= $cat['id'] ?></td>
                        <td><strong><?= escape($cat['name']) ?></strong></td>
                        <td style="font-size: 0.85rem; color: var(--color-text-muted);"><?= escape($cat['description']) ?></td>
                        <td class="tabular-nums">
                            <strong><?= $cat['item_count'] ?></strong> items
                        </td>
                        <td>
                            <span class="badge badge-<?= ($cat['status'] === 'active') ? 'accepted' : 'rejected' ?>">
                                <?= escape($cat['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div style="display:flex; gap:0.4rem;">
                                <a href="/admin/categories.php?action=toggle&id=<?= $cat['id'] ?>" class="btn btn-sm btn-secondary">
                                    <?= ($cat['status'] === 'active') ? 'Deactivate' : 'Activate' ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
