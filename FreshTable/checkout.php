<?php
require_once 'config.php';
require_once 'includes/functions.php';

$page_title = 'Checkout';
$cart = cart_session();

if (empty($cart)) {
    redirect('cart.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $table_number  = trim($_POST['table_number'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');

    if ($customer_name === '') {
        $errors[] = 'Please enter your name so the kitchen knows who this order is for.';
    }

    if (empty($errors)) {
        // Re-check stock right before committing — someone else may have
        // ordered the last portion since the cart page was loaded.
        mysqli_begin_transaction($conn);
        $ok = true;
        $line_items = [];
        $total = 0;

        foreach ($cart as $item_id => $qty) {
            $stmt = mysqli_prepare($conn, "SELECT * FROM menu_items WHERE id = ? FOR UPDATE");
            mysqli_stmt_bind_param($stmt, 'i', $item_id);
            mysqli_stmt_execute($stmt);
            $item = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            if (!$item || !$item['is_available'] || $item['stock'] < $qty) {
                $ok = false;
                $errors[] = 'Sorry — "' . e($item['name'] ?? 'an item') . '" no longer has enough stock. Please update your cart.';
                break;
            }

            $line_total = $item['price'] * $qty;
            $total += $line_total;
            $line_items[] = [
                'menu_item_id' => $item['id'],
                'name' => $item['name'],
                'price' => $item['price'],
                'qty' => $qty,
                'line_total' => $line_total,
            ];
        }

        if ($ok) {
            $order_number = generate_order_number();
            $stmt = mysqli_prepare($conn,
                "INSERT INTO orders (order_number, customer_name, table_number, phone, status, total)
                 VALUES (?, ?, ?, ?, 'pending', ?)");
            mysqli_stmt_bind_param($stmt, 'ssssd', $order_number, $customer_name, $table_number, $phone, $total);
            mysqli_stmt_execute($stmt);
            $order_id = mysqli_insert_id($conn);

            foreach ($line_items as $line) {
                $stmt = mysqli_prepare($conn,
                    "INSERT INTO order_items (order_id, menu_item_id, item_name, unit_price, quantity, subtotal)
                     VALUES (?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, 'iisdid',
                    $order_id, $line['menu_item_id'], $line['name'], $line['price'], $line['qty'], $line['line_total']);
                mysqli_stmt_execute($stmt);

                $stmt = mysqli_prepare($conn, "UPDATE menu_items SET stock = stock - ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'ii', $line['qty'], $line['menu_item_id']);
                mysqli_stmt_execute($stmt);
            }

            mysqli_commit($conn);
            $cart_ref = &cart_session();
            $cart_ref = [];
            redirect('order_success.php?order=' . urlencode($order_number));
        } else {
            mysqli_rollback($conn);
        }
    }
}

// ---- Build cart summary for display ----
$ids = array_map('intval', array_keys($cart));
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));
$stmt = mysqli_prepare($conn, "SELECT * FROM menu_items WHERE id IN ($placeholders)");
mysqli_stmt_bind_param($stmt, $types, ...$ids);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$lines = [];
$subtotal = 0;
while ($item = mysqli_fetch_assoc($result)) {
    $qty = $cart[$item['id']];
    $line_total = $item['price'] * $qty;
    $subtotal += $line_total;
    $lines[] = ['item' => $item, 'qty' => $qty, 'line_total' => $line_total];
}

require 'includes/header.php';
?>

<main class="page page-narrow">
  <p class="eyebrow">// checkout.php</p>
  <h1 class="page-title">Confirm your order</h1>

  <?php if (!empty($errors)): ?>
    <div class="form-errors">
      <?php foreach ($errors as $err): ?>
        <p><?= e($err) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="checkout-layout">
    <form method="post" action="checkout.php" class="checkout-form">
      <label>
        <span>Your name</span>
        <input type="text" name="customer_name" placeholder="e.g. Rajan" required value="<?= e($_POST['customer_name'] ?? '') ?>">
      </label>
      <label>
        <span>Table number <em>(optional)</em></span>
        <input type="text" name="table_number" placeholder="e.g. 12" value="<?= e($_POST['table_number'] ?? '') ?>">
      </label>
      <label>
        <span>Phone <em>(optional)</em></span>
        <input type="tel" name="phone" placeholder="e.g. 9313874256" value="<?= e($_POST['phone'] ?? '') ?>">
      </label>
      <button type="submit" class="btn-primary btn-full">Place order</button>
    </form>

    <div class="checkout-summary">
      <h3>Order summary</h3>
      <?php foreach ($lines as $line): ?>
        <div class="summary-line">
          <span><?= (int) $line['qty'] ?>× <?= e($line['item']['name']) ?></span>
          <span><?= money($line['line_total']) ?></span>
        </div>
      <?php endforeach; ?>
      <div class="summary-line summary-total">
        <span>Total</span>
        <span><?= money($subtotal) ?></span>
      </div>
    </div>
  </div>
</main>

<?php require 'includes/footer.php'; ?>
