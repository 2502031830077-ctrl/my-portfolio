<?php
/**
 * FreshTable — admin auth guard
 * require_once this at the top of any page under /admin (after config.php
 * and functions.php) to force a login redirect for anonymous visitors.
 */
if (!is_admin_logged_in()) {
    header('Location: login.php');
    exit;
}
