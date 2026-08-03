<?php
/**
 * FreshTable — database configuration
 *
 * XAMPP defaults are used below (root user, no password,
 * localhost). Change these if your MySQL setup differs.
 */

define('DB_HOST', 'sql106.infinityfree.com');
define('DB_USER', 'if0_42565770');
define('DB_PASS', 'KZ4SQco2T9vhd');
define('DB_NAME', 'if0_42565770_freshtable');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    // Fail loudly during development so a missing database is obvious.
    die('Database connection failed: ' . mysqli_connect_error() .
        ' — have you imported database.sql yet?');
}

mysqli_set_charset($conn, 'utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
