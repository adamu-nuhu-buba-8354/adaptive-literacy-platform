<?php
/**
 * Global header: <head>, top language bar, and main navigation.
 * Expects (optionally) $pageTitle to be set by the including page.
 * Requires config/db.php + includes/functions.php to already be loaded.
 */
$pageTitle = $pageTitle ?? 'Adaptive Literacy Learning Platform';
$activeNav = $activeNav ?? '';
$lang = ui_lang();
$loggedInUser = is_logged_in() ? current_user($pdo) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> · Adaptive Literacy Platform</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<div class="topbar">
  <div class="topbar-inner">
    <span class="visually-hidden">Site language</span>
    <div class="lang-pill-group">
      <?php
      $langs = ['english' => 'English', 'yoruba' => 'Yorùbá', 'pidgin' => 'Pidgin'];
      $qs = $_SERVER['QUERY_STRING'] ?? '';
      parse_str($qs, $qsArr);
      foreach ($langs as $code => $label):
          $qsArr['ui_lang'] = $code;
          $href = '?' . http_build_query($qsArr);
      ?>
        <a class="lang-pill <?= $lang === $code ? 'active' : '' ?>" href="<?= e($href) ?>"><?= e($label) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<header class="navbar">
  <div class="navbar-inner">
    <a class="brand" href="<?= BASE_URL ?>/index.php">
      <span>Adaptive&nbsp;Literacy</span>
    </a>

    <nav class="nav-links">
      <a href="<?= BASE_URL ?>/index.php" class="<?= $activeNav === 'home' ? 'active' : '' ?>"><?= t('home') ?></a>
      <a href="<?= BASE_URL ?>/lessons.php" class="<?= $activeNav === 'lessons' ? 'active' : '' ?>"><?= t('lessons') ?></a>
      <?php if ($loggedInUser): ?>
        <a href="<?= BASE_URL ?>/dashboard.php" class="<?= $activeNav === 'dashboard' ? 'active' : '' ?>"><?= t('dashboard') ?></a>
        <a href="<?= BASE_URL ?>/progress.php" class="<?= $activeNav === 'progress' ? 'active' : '' ?>"><?= t('progress') ?></a>
        <a href="<?= BASE_URL ?>/report.php" class="<?= $activeNav === 'report' ? 'active' : '' ?>"><?= t('report') ?></a>
      <?php endif; ?>
    </nav>

    <div class="nav-cta">
      <?php if ($loggedInUser): ?>
        <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/logout.php"><?= t('logout') ?></a>
      <?php else: ?>
        <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/login.php"><?= t('login') ?></a>
        <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/register.php"><?= t('register') ?></a>
      <?php endif; ?>
    </div>
  </div>
</header>
<main>
