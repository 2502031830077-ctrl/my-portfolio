<?php
require_once '../config.php';
require_once '../includes/functions.php';

if (is_admin_logged_in()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare($conn, "SELECT * FROM admins WHERE username = ?");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        redirect('dashboard.php');
    } else {
        $error = 'Incorrect username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Login — FreshTable</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
</head>
<body class="admin-body">

<div class="login-wrap">
  <form method="post" action="login.php" class="login-card">
    <p class="eyebrow">// admin/login.php</p>
    <h1>FreshTable <span class="logo-accent">Staff</span></h1>
    <p class="login-sub">Sign in to manage orders, the menu, and stock.</p>

    <?php if ($error): ?>
      <p class="form-errors"><?= e($error) ?></p>
    <?php endif; ?>

    <label>
      <span>Username</span>
      <input type="text" name="username" autocomplete="username" required autofocus>
    </label>
    <label>
      <span>Password</span>
      <input type="password" name="password" autocomplete="current-password" required>
    </label>
    <button type="submit" class="btn-primary btn-full">Sign in</button>
    <p class="login-hint">Default seed login: <b>admin</b> / <b>admin123</b></p>
  </form>
</div>

</body>
</html>
