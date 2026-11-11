<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$errors = [];
$old = ['full_name' => '', 'email' => '', 'phone' => '', 'preferred_language' => 'english'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['phone'] = trim($_POST['phone'] ?? '');
    $old['preferred_language'] = $_POST['preferred_language'] ?? 'english';
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($old['full_name'] === '' || strlen($old['full_name']) < 2) {
        $errors['full_name'] = 'Please enter your full name.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if (!in_array($old['preferred_language'], ['english', 'yoruba', 'pidgin'], true)) {
        $errors['preferred_language'] = 'Please choose a language.';
    }
    if (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$old['email']]);
        if ($stmt->fetch()) {
            $errors['email'] = 'An account with this email already exists.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (full_name, email, phone, password_hash, preferred_language) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$old['full_name'], $old['email'], $old['phone'], $hash, $old['preferred_language']]);
        $_SESSION['user_id'] = (int)$pdo->lastInsertId();
        $_SESSION['ui_lang'] = $old['preferred_language'];
        header('Location: ' . BASE_URL . '/dashboard.php?welcome=1');
        exit;
    }
}

$pageTitle = 'Create Your Account';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
  <h1>Create your free account</h1>
  <p>It takes less than a minute. You can change your language anytime.</p>

  <?php if ($errors): ?>
    <div class="alert alert-error">Please fix the highlighted fields below.</div>
  <?php endif; ?>

  <form method="POST" action="" novalidate id="registerForm">
    <div class="form-field <?= isset($errors['full_name']) ? 'invalid' : '' ?>">
      <label for="full_name">Full name</label>
      <input type="text" id="full_name" name="full_name" value="<?= e($old['full_name']) ?>" required minlength="2" autocomplete="name">
      <span class="form-error"><?= e($errors['full_name'] ?? 'Please enter your full name.') ?></span>
    </div>

    <div class="form-field <?= isset($errors['email']) ? 'invalid' : '' ?>">
      <label for="email">Email address</label>
      <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" required autocomplete="email">
      <span class="form-error"><?= e($errors['email'] ?? 'Please enter a valid email address.') ?></span>
    </div>

    <div class="form-field">
      <label for="phone">Phone number <span style="font-weight:400;color:var(--ink-300);">(optional)</span></label>
      <input type="tel" id="phone" name="phone" value="<?= e($old['phone']) ?>" autocomplete="tel">
    </div>

    <div class="form-field <?= isset($errors['preferred_language']) ? 'invalid' : '' ?>">
      <label for="preferred_language">Preferred language</label>
      <select id="preferred_language" name="preferred_language" required>
        <option value="english" <?= $old['preferred_language'] === 'english' ? 'selected' : '' ?>>English</option>
        <option value="yoruba" <?= $old['preferred_language'] === 'yoruba' ? 'selected' : '' ?>>Yorùbá</option>
        <option value="pidgin" <?= $old['preferred_language'] === 'pidgin' ? 'selected' : '' ?>>Pidgin</option>
      </select>
      <span class="form-error">Please choose a language.</span>
    </div>

    <div class="form-field <?= isset($errors['password']) ? 'invalid' : '' ?>">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required minlength="6" autocomplete="new-password">
      <span class="form-hint">At least 6 characters.</span>
      <span class="form-error"><?= e($errors['password'] ?? 'Password must be at least 6 characters.') ?></span>
    </div>

    <div class="form-field <?= isset($errors['confirm_password']) ? 'invalid' : '' ?>">
      <label for="confirm_password">Confirm password</label>
      <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
      <span class="form-error"><?= e($errors['confirm_password'] ?? 'Passwords do not match.') ?></span>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Create account</button>
  </form>

  <p class="text-center" style="margin-top:20px;">Already have an account? <a href="<?= BASE_URL ?>/login.php" style="color:var(--indigo-900); font-weight:600;">Log in</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
