<?php
/**
 * Admin area header. Requires config/db.php + includes/functions.php loaded,
 * and require_admin() already called by the including page.
 */
$pageTitle = $pageTitle ?? 'Admin';
$activeAdminNav = $activeAdminNav ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> · Admin · Adaptive Literacy</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<header class="navbar" style="background:var(--indigo-900);">
  <div class="navbar-inner">
    <a class="brand" href="<?= BASE_URL ?>/admin/dashboard.php" style="color:var(--cream-050);">
      <span class="brand-mark">AL</span>
      <span>Admin</span>
    </a>
    <nav class="nav-links">
      <a href="<?= BASE_URL ?>/admin/dashboard.php" style="color:var(--cream-050); <?= $activeAdminNav === 'dashboard' ? 'background:rgba(255,255,255,0.12);' : '' ?>">Dashboard</a>
      <a href="<?= BASE_URL ?>/admin/lessons.php" style="color:var(--cream-050); <?= $activeAdminNav === 'lessons' ? 'background:rgba(255,255,255,0.12);' : '' ?>">Lessons</a>
      <a href="<?= BASE_URL ?>/admin/users.php" style="color:var(--cream-050); <?= $activeAdminNav === 'users' ? 'background:rgba(255,255,255,0.12);' : '' ?>">Learners</a>
    </nav>
    <div class="nav-cta">
      <a class="btn btn-outline btn-sm" style="border-color:var(--cream-050); color:var(--cream-050);" href="<?= BASE_URL ?>/index.php">View site</a>
      <a class="btn btn-gold btn-sm" href="<?= BASE_URL ?>/admin/logout.php">Log out</a>
    </div>
  </div>
</header>
<main>
  <div class="container" style="padding-top:32px;">
