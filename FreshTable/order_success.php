<?php
require_once 'config.php';
require_once 'includes/functions.php';

$page_title = 'Order Confirmed';
$order_number = $_GET['order'] ?? '';

$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE order_number = ?");
mysqli_stmt_bind_param($stmt, 's', $order_number);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    redirect('index.php');
}

$stmt = mysqli_prepare($conn, "SELECT * FROM order_items WHERE order_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $order['id']);
mysqli_stmt_execute($stmt);
$items = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

require 'includes/header.php';
?>

<main class="page page-narrow">
  <div class="ticket">
    <div class="ticket-head">
      <span class="ticket-check">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
      </span>
      <h1>Order sent to the kitchen</h1>
      <p class="ticket-sub">Keep this order number handy if you need to ask about it.</p>
    </div>

    <div class="ticket-body">
      <div class="ticket-row ticket-order-no">
        <span>Order</span>
        <span>#<?= e($order['order_number']) ?></span>
      </div>
      <div class="ticket-row">
        <span>Name</span>
        <span><?= e($order['customer_name']) ?></span>
      </div>
      <?php if (!empty($order['table_number'])): ?>
        <div class="ticket-row">
          <span>Table</span>
          <span><?= e($order['table_number']) ?></span>
        </div>
      <?php endif; ?>
      <div class="ticket-row">
        <span>Status</span>
        <span class="status-pill status-<?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span>
      </div>

      <div class="ticket-divider"></div>

      <?php foreach ($items as $item): ?>
        <div class="ticket-row ticket-item">
          <span><?= (int) $item['quantity'] ?>× <?= e($item['item_name']) ?></span>
          <span><?= money($item['subtotal']) ?></span>
        </div>
      <?php endforeach; ?>

      <div class="ticket-divider"></div>

      <div class="ticket-row ticket-total">
        <span>Total</span>
        <span><?= money($order['total']) ?></span>
      </div>
    </div>

    <a href="index.php" class="btn-primary btn-full">Back to menu</a>
  </div>
</main>

<?php require 'includes/footer.php'; ?>
