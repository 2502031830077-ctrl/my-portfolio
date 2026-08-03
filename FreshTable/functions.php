<?php
/**
 * FreshTable — shared helper functions
 * Included after config.php on every page.
 */

/** Escape a string for safe HTML output. */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/** Format a number as rupees, e.g. 240.00 -> "₹240.00" */
function money($amount) {
    return '₹' . number_format((float) $amount, 2);
}

/** Redirect and stop execution. */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/** Generate a short, human-readable order number like FT-7K3D2A. */
function generate_order_number() {
    return 'FT-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
}

/** The cart lives in the session as [menu_item_id => quantity]. */
function &cart_session() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    return $_SESSION['cart'];
}

/** Total number of items (sum of quantities) currently in the cart. */
function cart_item_count() {
    $cart = cart_session();
    return array_sum($cart);
}

/** Is the current visitor a logged-in admin? */
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}
