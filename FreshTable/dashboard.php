<?php
require_once '../config.php';
require_once '../includes/functions.php';
require_once 'includes/auth.php';

$page_title = 'Dashboard';

// ---- Today's sales + order count ----
$today = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS order_count, COALESCE(SUM(total), 0) AS revenue
     FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'"
));

// ---- Order status breakdown (today) ----
$pending_today = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS c FROM orders WHERE DATE(created_at) = CURDATE() AND status = 'pending'"
))['c'];

// ---- Low stock items (5 or fewer) ----
$low_stock = mysqli_query($conn,
    "SELECT * FROM menu_items WHERE stock <= 5 AND is_available = 1 ORDER BY stock ASC"
);

// ---- Recent orders ----
$recent_orders = mysqli_query($conn,
    "SELECT * FROM orders ORDER BY created_at DESC LIMIT 8"
);

require 'includes/admin_header.php';
?>

<p class="eyebrow">// admin/dashboard.php</p>
<h1 class="page-title">Today at a glance</h1>

<div class="stat-cards">
  <div class="stat-card">
    <span class="stat-card-label">Orders today</span>
    <span class="stat-card-value"><?= (int) $today['order_count'] ?></span>
  </div>
  <div class="stat-card">
    <span class="stat-card-label">Revenue today</span>
    <span class="stat-card-value"><?= money($today['revenue']) ?></span>
  </div>
  <div class="stat-card">
    <span class="stat-card-label">Pending orders</span>
    <span class="stat-card-value"><?= (int) $pending_today ?></span>
  </div>
</div>

<div class="dash-grid">

  <section class="dash-panel">
    <h2>Low stock alerts</h2>
    <?php if (mysqli_num_rows($low_stock) === 0): ?>
      <p class="empty-note">Everything is well stocked.</p>
    <?php else: ?>
      <table class="data-table">
        <thead><tr><th>Item</th><th>Category</th><th>Stock</th></tr></thead>
        <tbody>
          <?php while ($item = mysqli_fetch_assoc($low_stock)): ?>
            <tr>
              <td><?= e($item['name']) ?></td>
              <td><?= e($item['category']) ?></td>
              <td><span class="stock-flag <?= $item['stock'] == 0 ? 'out' : 'low' ?>"><?= (int) $item['stock'] ?></span></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php endif; ?>
    <a href="inventory.php" class="panel-link">Manage inventory →</a>
  </section>

  <section class="dash-panel">
    <h2>Recent orders</h2>
    <?php if (mysqli_num_rows($recent_orders) === 0): ?>
      <p class="empty-note">No orders yet.</p>
    <?php else: ?>
      <table class="data-table">
        <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
          <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>
            <tr>
              <td><a href="order_view.php?id=<?= (int) $order['id'] ?>">#<?= e($order['order_number']) ?></a></td>
              <td><?= e($order['customer_name']) ?></td>
              <td><?= money($order['total']) ?></td>
              <td><span class="status-pill status-<?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php endif; ?>
    <a href="orders.php" class="panel-link">View all orders →</a>
  </section>

</div>

<?php require 'includes/admin_footer.php'; ?>
