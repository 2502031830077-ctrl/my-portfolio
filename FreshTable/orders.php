<?php
require_once '../config.php';
require_once '../includes/functions.php';
require_once 'includes/auth.php';

$page_title = 'Orders';
$valid_statuses = ['pending', 'preparing', 'ready', 'completed', 'cancelled'];

// ---- Handle inline status update ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    $order_id = (int) ($_POST['order_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';
    if (in_array($new_status, $valid_statuses, true)) {
        $stmt = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $new_status, $order_id);
        mysqli_stmt_execute($stmt);
    }
    redirect('orders.php' . (isset($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
}

// ---- Filter by status ----
$filter = isset($_GET['status']) && in_array($_GET['status'], $valid_statuses, true) ? $_GET['status'] : '';

if ($filter) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE status = ? ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, 's', $filter);
    mysqli_stmt_execute($stmt);
    $orders = mysqli_stmt_get_result($stmt);
} else {
    $orders = mysqli_query($conn, "SELECT * FROM orders ORDER BY created_at DESC");
}

require 'includes/admin_header.php';
?>

<p class="eyebrow">// admin/orders.php</p>
<h1 class="page-title">Orders</h1>

<div class="cat-tabs">
  <a href="orders.php" class="cat-tab <?= $filter === '' ? 'is-active' : '' ?>">All</a>
  <?php foreach ($valid_statuses as $s): ?>
    <a href="orders.php?status=<?= $s ?>" class="cat-tab <?= $filter === $s ? 'is-active' : '' ?>"><?= e(ucfirst($s)) ?></a>
  <?php endforeach; ?>
</div>

<?php if (mysqli_num_rows($orders) === 0): ?>
  <p class="empty-note">No orders match this filter.</p>
<?php else: ?>
  <table class="data-table data-table-wide">
    <thead>
      <tr><th>Order</th><th>Customer</th><th>Table</th><th>Total</th><th>Placed</th><th>Status</th><th></th></tr>
    </thead>
    <tbody>
      <?php while ($order = mysqli_fetch_assoc($orders)): ?>
        <tr>
          <td><a href="order_view.php?id=<?= (int) $order['id'] ?>">#<?= e($order['order_number']) ?></a></td>
          <td><?= e($order['customer_name']) ?></td>
          <td><?= e($order['table_number'] ?: '—') ?></td>
          <td><?= money($order['total']) ?></td>
          <td class="mono-cell"><?= date('M j, g:i a', strtotime($order['created_at'])) ?></td>
          <td><span class="status-pill status-<?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span></td>
          <td>
            <form method="post" action="orders.php<?= $filter ? '?status=' . $filter : '' ?>" class="inline-status-form">
              <input type="hidden" name="action" value="update_status">
              <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
              <select name="status" onchange="this.form.submit()">
                <?php foreach ($valid_statuses as $s): ?>
                  <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php require 'includes/admin_footer.php'; ?>
