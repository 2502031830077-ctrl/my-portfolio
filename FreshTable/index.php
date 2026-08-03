<?php
require_once 'config.php';
require_once 'includes/functions.php';

$page_title = 'Menu';

// ---- Read selected category from the URL (defaults to "All") ----
$selected_category = isset($_GET['category']) ? trim($_GET['category']) : 'All';

// ---- Pull distinct categories for the filter tabs ----
$categories = ['All'];
$cat_result = mysqli_query($conn, "SELECT DISTINCT category FROM menu_items ORDER BY category ASC");
while ($row = mysqli_fetch_assoc($cat_result)) {
    $categories[] = $row['category'];
}

// ---- Pull menu items, optionally filtered by category ----
if ($selected_category !== 'All') {
    $stmt = mysqli_prepare($conn, "SELECT * FROM menu_items WHERE category = ? ORDER BY name ASC");
    mysqli_stmt_bind_param($stmt, 's', $selected_category);
    mysqli_stmt_execute($stmt);
    $items_result = mysqli_stmt_get_result($stmt);
} else {
    $items_result = mysqli_query($conn, "SELECT * FROM menu_items ORDER BY category ASC, name ASC");
}

$items = [];
while ($row = mysqli_fetch_assoc($items_result)) {
    $items[] = $row;
}

require 'includes/header.php';
?>

<section class="menu-hero">
  <div class="menu-hero-inner">
    <p class="eyebrow">// today's menu</p>
    <h1>Order straight from your table.</h1>
    <p class="menu-hero-sub">Pick your dishes, review your order, and send it to the kitchen — no waiting on a server to take your order.</p>
  </div>
</section>

<main class="page">

  <div class="cat-tabs">
    <?php foreach ($categories as $cat): ?>
      <a href="index.php?category=<?= urlencode($cat) ?>"
         class="cat-tab <?= $cat === $selected_category ? 'is-active' : '' ?>">
        <?= e($cat) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($items)): ?>
    <p class="empty-note">No dishes in this category right now.</p>
  <?php else: ?>
    <div class="menu-grid">
      <?php foreach ($items as $item): ?>
        <?php
          $out_of_stock = ((int) $item['stock'] <= 0) || !$item['is_available'];
          $low_stock = !$out_of_stock && (int) $item['stock'] <= 3;
        ?>
        <article class="dish-card <?= $out_of_stock ? 'is-unavailable' : '' ?>">
          <div class="dish-top">
            <h3><?= e($item['name']) ?></h3>
            <span class="dish-price"><?= money($item['price']) ?></span>
          </div>
          <p class="dish-desc"><?= e($item['description']) ?></p>
          <div class="dish-bottom">
            <span class="dish-tag"><?= e($item['category']) ?></span>
            <?php if ($out_of_stock): ?>
              <span class="dish-status out">Sold out today</span>
            <?php elseif ($low_stock): ?>
              <span class="dish-status low"><?= (int) $item['stock'] ?> left</span>
            <?php endif; ?>
          </div>
          <form method="post" action="cart.php" class="add-form">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
            <input type="hidden" name="stay_on" value="menu">
            <button type="submit" class="add-btn" <?= $out_of_stock ? 'disabled' : '' ?>>
              <?= $out_of_stock ? 'Unavailable' : 'Add to order' ?>
            </button>
          </form>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</main>

<?php require 'includes/footer.php'; ?>
