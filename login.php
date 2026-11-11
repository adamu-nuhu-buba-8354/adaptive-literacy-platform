<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$error = '';
$emailOld = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailOld = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$emailOld]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['ui_lang'] = $user['preferred_language'];
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }
    $error = 'That email and password combination was not found.';
}

$pageTitle = 'Log In';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
  <h1><?= t('login') ?></h1>
  <p>Welcome back — pick up right where you left off.</p>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="" novalidate id="loginForm">
    <div class="form-field">
      <label for="email">Email address</label>
      <input type="email" id="email" name="email" value="<?= e($emailOld) ?>" required autocomplete="email">
      <span class="form-error">Please enter a valid email address.</span>
    </div>
    <div class="form-field">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required autocomplete="current-password">
      <span class="form-error">Please enter your password.</span>
    </div>
    <button type="submit" class="btn btn-primary btn-block"><?= t('login') ?></button>
  </form>

  <p class="text-center" style="margin-top:20px;">New here? <a href="<?= BASE_URL ?>/register.php" style="color:var(--indigo-900); font-weight:600;">Create an account</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
