<?php
require_once '../config.php';
require_once '../includes/functions.php';
require_once 'includes/auth.php';

$page_title = 'Inventory';

// ---------------------------------------------------------
// Handle stock restock/adjustment
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'restock') {
    $id = (int) ($_POST['id'] ?? 0);
    $set_to = $_POST['set_to'] ?? '';

    if ($set_to !== '' && is_numeric($set_to) && (int) $set_to >= 0) {
        $new_stock = (int) $set_to;
        $stmt = mysqli_prepare($conn, "UPDATE menu_items SET stock = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $new_stock, $id);
        mysqli_stmt_execute($stmt);
    }
    redirect('inventory.php');
}

$items = mysqli_query($conn, "SELECT * FROM menu_items ORDER BY stock ASC, name ASC");

require 'includes/admin_header.php';
?>

<p class="eyebrow">// admin/inventory.php</p>
<h1 class="page-title">Inventory</h1>
<p class="page-sub">Stock lives directly on each menu item — update it here as deliveries come in or the kitchen runs a count.</p>

<table class="data-table data-table-wide">
  <thead>
    <tr><th>Item</th><th>Category</th><th>Current stock</th><th>Update to</th></tr>
  </thead>
  <tbody>
    <?php while ($item = mysqli_fetch_assoc($items)): ?>
      <?php
        $flag = $item['stock'] == 0 ? 'out' : ($item['stock'] <= 5 ? 'low' : '');
      ?>
      <tr>
        <td><?= e($item['name']) ?></td>
        <td><?= e($item['category']) ?></td>
        <td><span class="stock-flag <?= $flag ?>"><?= (int) $item['stock'] ?></span></td>
        <td>
          <form method="post" action="inventory.php" class="inline-form restock-form">
            <input type="hidden" name="action" value="restock">
            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
            <input type="number" min="0" name="set_to" value="<?= (int) $item['stock'] ?>">
            <button type="submit" class="btn-ghost btn-sm">Update</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<?php require 'includes/admin_footer.php'; ?>
