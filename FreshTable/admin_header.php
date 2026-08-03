<?php
/**
 * FreshTable — admin header
 * Expects config.php, functions.php, and includes/auth.php already
 * required by the caller, plus $page_title set.
 */
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? e($page_title) . ' — FreshTable Admin' : 'FreshTable Admin' ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
</head>
<body class="admin-body">

<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="admin-logo">Fresh<span class="logo-accent">Table</span></div>
    <nav class="admin-nav">
      <a href="dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'is-active' : '' ?>">Dashboard</a>
      <a href="orders.php" class="<?= $current_page === 'orders.php' || $current_page === 'order_view.php' ? 'is-active' : '' ?>">Orders</a>
      <a href="menu.php" class="<?= $current_page === 'menu.php' ? 'is-active' : '' ?>">Menu</a>
      <a href="inventory.php" class="<?= $current_page === 'inventory.php' ? 'is-active' : '' ?>">Inventory</a>
    </nav>
    <div class="admin-sidebar-foot">
      <p>Signed in as <b><?= e($_SESSION['admin_username'] ?? 'admin') ?></b></p>
      <a href="logout.php" class="admin-logout">Log out</a>
      <a href="../index.php" class="admin-view-site">View site ↗</a>
    </div>
  </aside>

  <main class="admin-main">
