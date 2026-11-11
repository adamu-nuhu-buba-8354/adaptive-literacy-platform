<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Learn to Read, Listen and Grow';
$activeNav = 'home';

$lessonCount = $pdo->query('SELECT COUNT(*) FROM lessons')->fetchColumn();
$learnerCount = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

// Fetch featured lessons for showcase
$featuredStmt = $pdo->prepare('SELECT id, title, description FROM lessons ORDER BY created_at DESC LIMIT 3');
$featuredStmt->execute();
$featuredLessons = $featuredStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section - Modern Design -->
<section class="hero-landing">
  <div class="hero-gradient"></div>
  <div class="hero-wrap">
    <div class="hero-content">
      <div class="hero-badge">
        <i class="fa-solid fa-circle-check"></i>
        <span>Free adult literacy programme</span>
      </div>
      <h1 class="hero-title">
        Reading opens doors.<br>
        <span class="hero-highlight">We'll help you walk through.</span>
      </h1>
      <p class="hero-description">
        Learn at your own pace with short audio and video lessons in <strong>English</strong>, 
        <strong>Yorùbá</strong> or <strong>Pidgin</strong>. Every lesson adapts to how well 
        you're doing — so you're never bored, and never lost.
      </p>
      <div class="hero-actions">
        <?php if (is_logged_in()): ?>
          <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-house"></i> <?= t('dashboard') ?>
          </a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-user-plus"></i> Start learning free
          </a>
          <a href="<?= BASE_URL ?>/lessons.php" class="btn btn-outline btn-lg">
            <i class="fa-solid fa-compass"></i> Browse lessons
          </a>
        <?php endif; ?>
      </div>
      <div class="hero-stats">
        <div class="stat-item">
          <span class="stat-number"><?= (int)$lessonCount ?>+</span>
          <span class="stat-label">Lessons ready to explore</span>
        </div>
        <div class="stat-item">
          <span class="stat-number">3</span>
          <span class="stat-label">Languages: English, Yorùbá, Pidgin</span>
        </div>
        <div class="stat-item">
          <span class="stat-number"><?= (int)$learnerCount ?></span>
          <span class="stat-label">Learners on the platform</span>
        </div>
      </div>
    </div>
    <div class="hero-visual">
      <div class="hero-image-wrapper">
        <img src="<?= BASE_URL ?>/assets/images/hero_learning.png"
             alt="An adult learner studying with a book and laptop"
             class="hero-image" width="1280" height="960" decoding="async">
        <div class="hero-image-overlay">
          <span class="hero-image-overlay-icon" aria-hidden="true">
            <i class="fa-solid fa-users"></i>
          </span>
          <div>
            <div class="hero-image-overlay-title">Learn together</div>
            <div class="hero-image-overlay-text">Join <?= (int)$learnerCount ?>+ learners</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Features Section -->
<section class="section features-section">
  <div class="container">
    <div class="section-header">
      <h2>Built for how adults actually learn</h2>
      <p>Six simple pieces that work together, so learning fits into a busy day.</p>
    </div>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon feature-icon-1">
          <i class="fa-solid fa-user"></i>
        </div>
        <div class="feature-content">
          <span class="feature-tag">Getting started</span>
          <h3>Simple sign-up</h3>
          <p>Register in under a minute — just a name, phone or email, and a password.</p>
          <a href="<?= BASE_URL ?>/register.php" class="feature-link">
            Start today <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>
      <div class="feature-card">
        <div class="feature-icon feature-icon-2">
          <i class="fa-solid fa-language"></i>
        </div>
        <div class="feature-content">
          <span class="feature-tag">Choose your language</span>
          <h3>3 languages</h3>
          <p>Learn in the language you're most comfortable in: English, Yorùbá or Pidgin.</p>
          <a href="<?= BASE_URL ?>/lessons.php" class="feature-link">
            Available now <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>
      <div class="feature-card">
        <div class="feature-icon feature-icon-3">
          <i class="fa-solid fa-pen-to-square"></i>
        </div>
        <div class="feature-content">
          <span class="feature-tag">Check your learning</span>
          <h3>Short quizzes</h3>
          <p>Every lesson ends with a quick quiz that checks what you've learned.</p>
          <a href="<?= BASE_URL ?>/lessons.php" class="feature-link">
            Take a quiz <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>
      <div class="feature-card">
        <div class="feature-icon feature-icon-4">
          <i class="fa-solid fa-headphones"></i>
        </div>
        <div class="feature-content">
          <span class="feature-tag">For every learning style</span>
          <h3>Audio & video</h3>
          <p>Listen along or watch a short clip — helpful if reading alone feels hard.</p>
          <a href="<?= BASE_URL ?>/lessons.php" class="feature-link">
            Watch & listen <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>
      <div class="feature-card">
        <div class="feature-icon feature-icon-5">
          <i class="fa-solid fa-chart-line"></i>
        </div>
        <div class="feature-content">
          <span class="feature-tag">See your growth</span>
          <h3>Progress tracking</h3>
          <p>See exactly which lessons you've completed and how you're improving.</p>
          <a href="<?= BASE_URL ?>/progress.php" class="feature-link">
            View progress <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>
      <div class="feature-card">
        <div class="feature-icon feature-icon-6">
          <i class="fa-solid fa-bullseye"></i>
        </div>
        <div class="feature-content">
          <span class="feature-tag">Personalised path</span>
          <h3>Adapts to you</h3>
          <p>Score well and we raise the difficulty. Struggle and we slow down with you.</p>
          <a href="<?= BASE_URL ?>/dashboard.php" class="feature-link">
            See how it works <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Featured Lessons Section -->
<?php if (!empty($featuredLessons)): ?>
<section class="section featured-section">
  <div class="container">
    <div class="section-header">
      <h2>Popular lessons</h2>
      <p>Start with these popular lessons chosen by learners like you.</p>
    </div>
    <div class="lessons-grid">
      <?php foreach ($featuredLessons as $lesson): ?>
        <div class="lesson-card">
          <div class="lesson-card-image">
            <div class="lesson-placeholder">
              <i class="fa-solid fa-book-open"></i>
            </div>
            <span class="lesson-duration">
              <i class="fa-regular fa-clock"></i> 15 min
            </span>
          </div>
          <div class="lesson-card-content">
            <h3><?= htmlspecialchars($lesson['title']) ?></h3>
            <p><?= htmlspecialchars(substr($lesson['description'] ?? '', 0, 100)) ?>...</p>
            <div class="lesson-meta">
              <span><i class="fa-regular fa-circle-check"></i> Beginner</span>
            </div>
            <a href="<?= BASE_URL ?>/lesson.php?id=<?= (int)$lesson['id'] ?>" class="btn btn-outline btn-sm">
              Start lesson <i class="fa-solid fa-arrow-right"></i>
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="section-cta">
      <a href="<?= BASE_URL ?>/lessons.php" class="btn btn-primary btn-lg">
        View all lessons <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CTA Section - Dribbble Style -->
<section class="section cta-section">
  <div class="container">
    <div class="cta-card">
      <div class="cta-content">
        <h2>Ready when you are.</h2>
        <p>No classroom, no pressure — just short lessons you can repeat as many times as you need.</p>
        <div class="cta-actions">
          <?php if (is_logged_in()): ?>
            <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-primary btn-lg">
              <i class="fa-solid fa-house"></i> Go to Dashboard
            </a>
          <?php else: ?>
            <a href="<?= BASE_URL ?>/register.php" class="btn btn-gold btn-lg">
              <i class="fa-solid fa-user-plus"></i> Create your free account
            </a>
            <a href="<?= BASE_URL ?>/lessons.php" class="btn btn-outline btn-lg">
              <i class="fa-solid fa-compass"></i> Browse lessons first
            </a>
          <?php endif; ?>
        </div>
      </div>
      <div class="cta-visual">
        <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" class="cta-illustration">
          <defs>
            <pattern id="adire-pattern" width="40" height="40" patternUnits="userSpaceOnUse">
              <circle cx="20" cy="20" r="13" fill="none" stroke="var(--gold-500)" stroke-width="1.5" opacity="0.4"/>
              <circle cx="0" cy="0" r="9" fill="none" stroke="var(--cream-050)" stroke-width="1" opacity="0.3"/>
              <circle cx="40" cy="40" r="9" fill="none" stroke="var(--cream-050)" stroke-width="1" opacity="0.3"/>
            </pattern>
          </defs>
          <rect width="200" height="200" fill="url(#adire-pattern)"/>
          <circle cx="100" cy="80" r="40" fill="var(--gold-500)" opacity="0.8"/>
          <path d="M60 140 Q100 110 140 140 L140 170 Q100 155 60 170 Z" fill="var(--clay-600)"/>
          <text x="100" y="85" text-anchor="middle" font-family="Fraunces, serif" font-size="32" font-weight="700" fill="var(--indigo-900)">Aa</text>
        </svg>
      </div>
    </div>
  </div>
</section>

<style>
  /* Hero Section */
  .hero-landing {
    position: relative;
    padding: 64px 24px 72px;
    overflow: hidden;
    background: linear-gradient(135deg, var(--cream-050) 0%, var(--indigo-100) 100%);
  }
  .hero-gradient {
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at 80% 15%, var(--gold-100) 0%, transparent 45%),
                radial-gradient(ellipse at 15% 85%, var(--indigo-100) 0%, transparent 45%);
    opacity: 0.6;
    pointer-events: none;
    z-index: 1;
  }
  /* Rigid two-column grid: the copy and the image always share the row
     evenly, so no stray gap can open up between them. */
  .hero-wrap {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
    align-items: stretch;
    gap: 48px;
    width: 100%;
    max-width: var(--max-width);
    margin: 0 auto;
  }
  /* Text block is vertically centred against the photo, which itself
     stretches to the full height of the row (no dead space either side). */
  .hero-content { min-width: 0; max-width: 560px; align-self: center; }
  .hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--white);
    color: var(--indigo-700);
    padding: 10px 20px;
    border-radius: 100px;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 24px;
    animation: fadeInUp 0.6s ease both;
  }
  .hero-badge i { color: var(--gold-500); }
  .hero-title {
    font-size: clamp(2.25rem, 4vw, 3rem);
    font-weight: 700;
    line-height: 1.1;
    color: var(--indigo-900);
    margin-bottom: 24px;
    animation: fadeInUp 0.6s ease 0.1s both;
  }
  .hero-highlight { color: var(--clay-600); }
  .hero-description {
    font-size: 1.125rem;
    color: var(--ink-600);
    line-height: 1.7;
    margin-bottom: 36px;
    max-width: 100%;
    animation: fadeInUp 0.6s ease 0.2s both;
  }
  .hero-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 40px;
    animation: fadeInUp 0.6s ease 0.3s both;
  }
  .hero-stats {
    display: flex;
    gap: 36px;
    flex-wrap: wrap;
    animation: fadeInUp 0.6s ease 0.4s both;
  }
  .stat-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  .stat-number {
    font-family: var(--font-display);
    font-size: 2rem;
    font-weight: 700;
    line-height: 1.1;
    color: var(--indigo-900);
  }
  .stat-label {
    font-size: 0.8125rem;
    line-height: 1.35;
    color: var(--ink-600);
    max-width: 160px;
  }
  .hero-visual { min-width: 0; align-self: stretch; }
  /* The photo frame owns the shape (radius + clip) and stretches to match
     the text column, so the picture is always flush with the copy. */
  .hero-image-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
    min-height: 360px;
    margin: 0;
    border-radius: var(--radius-lg);
    overflow: hidden;
    background: var(--indigo-100);
    box-shadow: 0 24px 48px rgba(30, 42, 71, 0.16);
    animation: fadeInUp 0.6s ease 0.4s both;
  }
  .hero-image {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center 35%;
  }
  
  
  
  
  
  
  /* Badge sits INSIDE the photo frame (bottom-left) so it can never overflow
     the picture and knock the hero out of alignment. */
  .hero-image-overlay {
    position: absolute;
    left: 20px;
    bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--white);
    padding: 14px 20px;
    border-radius: var(--radius-md);
    box-shadow: 0 10px 30px rgba(30, 42, 71, 0.18);
    max-width: calc(100% - 40px);
    animation: fadeInUp 0.6s ease 0.6s both;
  }
  .hero-image-overlay-icon {
    flex: 0 0 auto;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gold-100);
    color: var(--clay-600);
    font-size: 1.0625rem;
  }
  .hero-image-overlay-title { font-size: 0.875rem; font-weight: 700; color: var(--indigo-900); margin-bottom: 2px; }
  .hero-image-overlay-text { font-size: 0.75rem; color: var(--ink-600); }
  @keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-15px); }
  }
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .section-header {
    text-align: center;
    margin-bottom: 40px;
  }
  .section-header h2 {
    font-size: clamp(1.75rem, 3vw, 2.25rem);
    margin-bottom: 12px;
  }
  .section-header p {
    font-size: 1.0625rem;
    color: var(--ink-600);
    max-width: 500px;
    margin: 0 auto;
  }
  .features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 24px;
  }
  .feature-card {
    background: linear-gradient(135deg, var(--cream-050) 0%, var(--indigo-100) 100%);
    border-radius: var(--radius-md);
    padding: 28px;
    transition: all 0.3s ease;
    border: 1px solid transparent;
  }
  .feature-card:hover {
    background: var(--white);
    border-color: var(--line);
    box-shadow: 0 12px 32px rgba(30, 42, 71, 0.06);
    transform: translateY(-4px);
  }
  .feature-icon {
    width: 56px;
    height: 56px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 24px;
  }
  .feature-icon-1 { background: var(--white); color: var(--indigo-700); }
  .feature-icon-2 { background: var(--gold-100); color: #d4921f; }
  .feature-icon-3 { background: var(--leaf-100); color: var(--leaf-600); }
  .feature-icon-4 { background: var(--clay-100); color: var(--clay-600); }
  .feature-icon-5 { background: var(--white); color: var(--indigo-700); }
  .feature-icon-6 { background: var(--gold-100); color: #d4921f; }
  .feature-tag {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--ink-600);
    margin-bottom: 10px;
  }
  .feature-content h3 { font-size: 1.125rem; margin-bottom: 8px; color: var(--indigo-900); }
  .feature-content p { font-size: 0.9375rem; color: var(--ink-600); margin-bottom: 16px; }
  .feature-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--gold-500);
  }
  .lessons-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-bottom: 36px;
  }
  .lesson-card {
    background: var(--white);
    border-radius: var(--radius-md);
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(30, 42, 71, 0.04);
    transition: all 0.3s ease;
  }
  .lesson-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(30, 42, 71, 0.08);
  }
  .lesson-card-image {
    position: relative;
    height: 160px;
    background: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .lesson-card-image img { width: 100%; height: 100%; object-fit: cover; }
  .lesson-placeholder { 
    color: var(--indigo-700); 
    opacity: 0.8;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--indigo-100) 0%, var(--indigo-200) 100%);
    border-radius: var(--radius-sm);
  }
  .lesson-placeholder i { 
    font-size: 3.5rem;
    opacity: 1;
  }
  .lesson-duration {
    position: absolute;
    bottom: 12px;
    right: 12px;
    background: var(--white);
    padding: 4px 10px;
    border-radius: 100px;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--ink-600);
    display: flex;
    align-items: center;
    gap: 4px;
  }
  .lesson-card-content { padding: 24px; }
  .lesson-card-content h3 { font-size: 1.125rem; margin-bottom: 12px; color: var(--indigo-900); }
  .lesson-card-content p { font-size: 0.9375rem; color: var(--ink-600); margin-bottom: 16px; }
  .lesson-meta {
    display: flex;
    gap: 16px;
    font-size: 0.75rem;
    color: var(--ink-600);
    margin-bottom: 16px;
  }
  .lesson-meta span { display: flex; align-items: center; gap: 4px; }
  .lesson-meta i { color: var(--leaf-600); }
  .section-cta { text-align: center; margin-top: 32px; }
  .cta-section { padding: 80px 24px; }
  .cta-card {
    background: var(--indigo-900);
    border-radius: var(--radius-lg);
    padding: 64px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 48px;
    position: relative;
    overflow: hidden;
  }
  .cta-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, var(--gold-500) 0%, transparent 70%);
    opacity: 0.15;
    pointer-events: none;
  }
  .cta-content {
    position: relative;
    z-index: 1;
    flex: 1;
  }
  .cta-content h2 {
    font-size: clamp(1.75rem, 3vw, 2.25rem);
    color: var(--cream-050);
    margin-bottom: 12px;
  }
  .cta-content p {
    font-size: 1.0625rem;
    color: var(--cream-100);
    max-width: 420px;
    margin-bottom: 36px;
  }
  .cta-actions { display: flex; gap: 12px; flex-wrap: wrap; }
  .cta-visual {
    position: relative;
    z-index: 1;
    flex-shrink: 0;
  }
  .cta-illustration { width: 200px; height: 200px; }
  .btn-sm {
    padding: 10px 20px;
    font-size: 0.875rem;
  }
  @media (max-width: 900px) {
    .cta-card {
      flex-direction: column;
      text-align: center;
      padding: 48px 32px;
    }
    .cta-content p { margin: 0 auto 32px; }
    .cta-actions { justify-content: center; }
    .cta-visual { display: none; }
  }
  /* Collapse the hero to a single column so the image tucks under the copy
     instead of being squeezed beside it. */
  @media (max-width: 900px) {
    .hero-wrap {
      grid-template-columns: minmax(0, 1fr);
      gap: 44px;
      text-align: center;
    }
    .hero-visual { order: -1; width: 100%; max-height: 360px; }
    .hero-image-wrapper { height: 360px; max-width: 540px; margin: 0 auto; }
    .hero-actions { justify-content: center; }
    .hero-stats { justify-content: center; }
  }
  @media (max-width: 640px) {
    .hero-landing { padding: 44px 20px 56px; }
    .hero-title { font-size: clamp(2rem, 8.5vw, 2.5rem); }
    .hero-description { font-size: 1rem; margin-bottom: 28px; }
    .hero-actions { margin-bottom: 32px; }
    .hero-actions .btn { width: 100%; justify-content: center; }
    .hero-stats { gap: 22px; }
    .stat-item { flex: 1 1 120px; align-items: center; }
    .stat-label { max-width: 100%; }
    .hero-image-wrapper { height: 260px; min-height: 0; }
    .hero-image-overlay { left: 12px; bottom: 12px; padding: 12px 16px; gap: 10px; }
    .hero-image-overlay-icon { width: 36px; height: 36px; font-size: 0.9375rem; }
    .features-grid { grid-template-columns: 1fr; }
    .lessons-grid { grid-template-columns: 1fr; }
  }
</style>
<?php require_once __DIR__ . '/includes/footer.php'; ?>




