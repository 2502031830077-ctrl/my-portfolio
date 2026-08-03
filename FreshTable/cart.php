<?php
require_once 'config.php';
require_once 'includes/functions.php';

$page_title = 'Your Cart';

// ---------------------------------------------------------
// Handle cart actions (add / increase / decrease / remove / clear)
// The cart itself is just [menu_item_id => quantity] in the session.
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $cart = &cart_session();

    if ($action === 'add' || $action === 'increase') {
        $item_id = (int) ($_POST['item_id'] ?? 0);
        $stmt = mysqli_prepare($conn, "SELECT stock, is_available FROM menu_items WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $item_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $item = mysqli_fetch_assoc($result);

        if ($item && $item['is_available']) {
            $current_qty = $cart[$item_id] ?? 0;
            if ($current_qty < (int) $item['stock']) {
                $cart[$item_id] = $current_qty + 1;
            }
        }
    } elseif ($action === 'decrease') {
        $item_id = (int) ($_POST['item_id'] ?? 0);
        if (isset($cart[$item_id])) {
            $cart[$item_id]--;
            if ($cart[$item_id] <= 0) unset($cart[$item_id]);
        }
    } elseif ($action === 'remove') {
        $item_id = (int) ($_POST['item_id'] ?? 0);
        unset($cart[$item_id]);
    } elseif ($action === 'clear') {
        $cart = [];
    }

    // Adding from the menu page should return the diner to the menu,
    // not jump them over to the cart mid-browse.
    if ($action === 'add' && !empty($_POST['stay_on']) && $_POST['stay_on'] === 'menu') {
        $ref = $_SERVER['HTTP_REFERER'] ?? 'index.php';
        redirect($ref);
    }
    redirect('cart.php');
}

// ---------------------------------------------------------
// Build the cart display: join session quantities with live
// menu data so prices and availability are always current.
// ---------------------------------------------------------
$cart = cart_session();
$cart_lines = [];
$subtotal = 0;

if (!empty($cart)) {
    $ids = array_map('intval', array_keys($cart));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $stmt = mysqli_prepare($conn, "SELECT * FROM menu_items WHERE id IN ($placeholders)");
    mysqli_stmt_bind_param($stmt, $types, ...$ids);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($item = mysqli_fetch_assoc($result)) {
        $qty = $cart[$item['id']];
        $line_total = $item['price'] * $qty;
        $subtotal += $line_total;
        $cart_lines[] = [
            'item' => $item,
            'qty' => $qty,
            'line_total' => $line_total,
        ];
    }
}

require 'includes/header.php';
?>

<main class="page page-narrow">
  <p class="eyebrow">// cart.php</p>
  <h1 class="page-title">Your order so far</h1>

  <?php if (empty($cart_lines)): ?>
    <div class="empty-note-block">
      <p>Your cart is empty.</p>
      <a href="index.php" class="btn-primary">Browse the menu</a>
    </div>
  <?php else: ?>

    <div class="cart-table">
      <?php foreach ($cart_lines as $line): ?>
        <?php $item = $line['item']; ?>
        <div class="cart-line">
          <div class="cart-line-info">
            <h3><?= e($item['name']) ?></h3>
            <span class="cart-line-price"><?= money($item['price']) ?> each</span>
          </div>
          <div class="cart-line-qty">
            <form method="post" action="cart.php">
              <input type="hidden" name="action" value="decrease">
              <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
              <button type="submit" class="qty-btn" aria-label="Decrease quantity">−</button>
            </form>
            <span class="qty-val"><?= (int) $line['qty'] ?></span>
            <form method="post" action="cart.php">
              <input type="hidden" name="action" value="increase">
              <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
              <button type="submit" class="qty-btn" <?= $line['qty'] >= $item['stock'] ? 'disabled' : '' ?> aria-label="Increase quantity">+</button>
            </form>
          </div>
          <div class="cart-line-total"><?= money($line['line_total']) ?></div>
          <form method="post" action="cart.php">
            <input type="hidden" name="action" value="remove">
            <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
            <button type="submit" class="remove-link">Remove</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="cart-summary">
      <div class="cart-summary-row">
        <span>Subtotal</span>
        <span class="cart-summary-total"><?= money($subtotal) ?></span>
      </div>
      <p class="cart-summary-note">Taxes calculated at checkout, if applicable.</p>
      <div class="cart-actions">
        <a href="checkout.php" class="btn-primary btn-full">Proceed to checkout</a>
        <form method="post" action="cart.php">
          <input type="hidden" name="action" value="clear">
          <button type="submit" class="btn-ghost">Clear cart</button>
        </form>
      </div>
    </div>

  <?php endif; ?>
</main>

<?php require 'includes/footer.php'; ?>
