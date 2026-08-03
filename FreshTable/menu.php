<?php
require_once '../config.php';
require_once '../includes/functions.php';
require_once 'includes/auth.php';

$page_title = 'Menu';

// ---------------------------------------------------------
// Handle form submissions
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $stock = (int) ($_POST['stock'] ?? 0);
        $is_available = isset($_POST['is_available']) ? 1 : 0;

        if ($name !== '' && $category !== '' && $price >= 0) {
            if ($action === 'create') {
                $stmt = mysqli_prepare($conn,
                    "INSERT INTO menu_items (name, description, category, price, stock, is_available)
                     VALUES (?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, 'sssdii', $name, $description, $category, $price, $stock, $is_available);
                mysqli_stmt_execute($stmt);
            } else {
                $id = (int) ($_POST['id'] ?? 0);
                $stmt = mysqli_prepare($conn,
                    "UPDATE menu_items SET name=?, description=?, category=?, price=?, stock=?, is_available=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, 'sssdiii', $name, $description, $category, $price, $stock, $is_available, $id);
                mysqli_stmt_execute($stmt);
            }
        }
        redirect('menu.php');
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = mysqli_prepare($conn, "DELETE FROM menu_items WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        redirect('menu.php');
    }

    if ($action === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        mysqli_query($conn, "UPDATE menu_items SET is_available = 1 - is_available WHERE id = " . $id);
        redirect('menu.php');
    }
}

// ---------------------------------------------------------
// If ?edit=ID is set, load that item to prefill the form
// ---------------------------------------------------------
$editing_item = null;
if (isset($_GET['edit'])) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM menu_items WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', (int) $_GET['edit']);
    mysqli_stmt_execute($stmt);
    $editing_item = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$all_items = mysqli_query($conn, "SELECT * FROM menu_items ORDER BY category ASC, name ASC");

require 'includes/admin_header.php';
?>

<p class="eyebrow">// admin/menu.php</p>
<h1 class="page-title">Menu items</h1>

<section class="dash-panel form-panel">
  <h2><?= $editing_item ? 'Edit "' . e($editing_item['name']) . '"' : 'Add a new item' ?></h2>
  <form method="post" action="menu.php" class="item-form">
    <input type="hidden" name="action" value="<?= $editing_item ? 'update' : 'create' ?>">
    <?php if ($editing_item): ?>
      <input type="hidden" name="id" value="<?= (int) $editing_item['id'] ?>">
    <?php endif; ?>

    <div class="form-grid">
      <label>
        <span>Name</span>
        <input type="text" name="name" required value="<?= e($editing_item['name'] ?? '') ?>">
      </label>
      <label>
        <span>Category</span>
        <input type="text" name="category" required placeholder="e.g. Main Course" value="<?= e($editing_item['category'] ?? '') ?>">
      </label>
      <label>
        <span>Price (₹)</span>
        <input type="number" step="0.01" min="0" name="price" required value="<?= e($editing_item['price'] ?? '') ?>">
      </label>
      <label>
        <span>Stock</span>
        <input type="number" min="0" name="stock" required value="<?= e($editing_item['stock'] ?? 0) ?>">
      </label>
    </div>
    <label>
      <span>Description</span>
      <input type="text" name="description" value="<?= e($editing_item['description'] ?? '') ?>">
    </label>
    <label class="checkbox-label">
      <input type="checkbox" name="is_available" <?= (!$editing_item || $editing_item['is_available']) ? 'checked' : '' ?>>
      <span>Available on the menu</span>
    </label>

    <div class="form-actions">
      <button type="submit" class="btn-primary"><?= $editing_item ? 'Save changes' : 'Add item' ?></button>
      <?php if ($editing_item): ?>
        <a href="menu.php" class="btn-ghost">Cancel</a>
      <?php endif; ?>
    </div>
  </form>
</section>

<table class="data-table data-table-wide">
  <thead>
    <tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr>
  </thead>
  <tbody>
    <?php while ($item = mysqli_fetch_assoc($all_items)): ?>
      <tr>
        <td><?= e($item['name']) ?></td>
        <td><?= e($item['category']) ?></td>
        <td><?= money($item['price']) ?></td>
        <td><?= (int) $item['stock'] ?></td>
        <td>
          <form method="post" action="menu.php" class="inline-form">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
            <button type="submit" class="status-pill status-toggle <?= $item['is_available'] ? 'status-completed' : 'status-cancelled' ?>">
              <?= $item['is_available'] ? 'Available' : 'Hidden' ?>
            </button>
          </form>
        </td>
        <td class="row-actions">
          <a href="menu.php?edit=<?= (int) $item['id'] ?>">Edit</a>
          <form method="post" action="menu.php" class="inline-form" onsubmit="return confirm('Delete this item permanently?');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
            <button type="submit" class="delete-link">Delete</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<?php require 'includes/admin_footer.php'; ?>
