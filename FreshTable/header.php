<?php
/**
 * FreshTable — customer-facing header
 * Expects config.php + functions.php already included by the caller.
 */
$cart_count = cart_item_count();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? e($page_title) . ' — FreshTable' : 'FreshTable — table-side ordering' ?></title>
<meta name="description" content="FreshTable — browse the menu, order from your table, and track it through the kitchen.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@500;600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= isset($asset_path) ? $asset_path : '' ?>assets/style.css">
</head>
<body>

<header class="site-nav">
  <div class="site-nav-inner">
    <a href="index.php" class="logo">Fresh<span class="logo-accent">Table</span></a>
    <nav class="site-links">
      <a href="index.php" class="<?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'is-active' : '' ?>">Menu</a>
      <a href="cart.php" class="<?= (basename($_SERVER['PHP_SELF']) === 'cart.php') ? 'is-active' : '' ?>">Cart</a>
    </nav>
    <a href="cart.php" class="cart-pill">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
      <span><?= (int) $cart_count ?></span>
    </a>
  </div>
</header>
