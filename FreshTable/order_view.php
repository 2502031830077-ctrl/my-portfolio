<?php
require_once '../config.php';
require_once '../includes/functions.php';
require_once 'includes/auth.php';

$page_title = 'Order Detail';
$order_id = (int) ($_GET['id'] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    redirect('orders.php');
}

$stmt = mysqli_prepare($conn, "SELECT * FROM order_items WHERE order_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$items = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

require 'includes/admin_header.php';
?>

<a href="orders.php" class="back-link">← Back to orders</a>
<p class="eyebrow">// admin/order_view.php</p>
<h1 class="page-title">Order #<?= e($order['order_number']) ?></h1>

<div class="order-detail-grid">
  <section class="dash-panel">
    <h2>Details</h2>
    <div class="detail-row"><span>Customer</span><span><?= e($order['customer_name']) ?></span></div>
    <div class="detail-row"><span>Table</span><span><?= e($order['table_number'] ?: '—') ?></span></div>
    <div class="detail-row"><span>Phone</span><span><?= e($order['phone'] ?: '—') ?></span></div>
    <div class="detail-row"><span>Placed</span><span><?= date('M j, Y g:i a', strtotime($order['created_at'])) ?></span></div>
    <div class="detail-row"><span>Status</span><span class="status-pill status-<?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span></div>
  </section>

  <section class="dash-panel">
    <h2>Items</h2>
    <table class="data-table">
      <thead><tr><th>Item</th><th>Qty</th><th>Unit</th><th>Subtotal</th></tr></thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td><?= e($item['item_name']) ?></td>
            <td><?= (int) $item['quantity'] ?></td>
            <td><?= money($item['unit_price']) ?></td>
            <td><?= money($item['subtotal']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><td colspan="3">Total</td><td><?= money($order['total']) ?></td></tr>
      </tfoot>
    </table>
  </section>
</div>

<?php require 'includes/admin_footer.php'; ?>
