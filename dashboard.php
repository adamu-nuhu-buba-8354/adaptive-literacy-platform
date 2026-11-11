<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$user = current_user($pdo);
if (!$user) {
  // Stale session or user record removed — clear session and require fresh login
  if (isset($_SESSION['user_id'])) {
    unset($_SESSION['user_id']);
  }
  header('Location: ' . BASE_URL . '/login.php');
  exit;
}

$userId = $user['id'];

$totalLessons = (int)$pdo->query('SELECT COUNT(*) FROM lessons')->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM user_progress WHERE user_id = ? AND status = "completed"');
$stmt->execute([$userId]);
$completedCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT AVG(best_score) FROM user_progress WHERE user_id = ? AND attempts > 0');
$stmt->execute([$userId]);
$avgScore = (int)round($stmt->fetchColumn() ?? 0);

$stmt = $pdo->prepare('
    SELECT l.*, up.status, up.best_score
    FROM lessons l
    LEFT JOIN user_progress up ON up.lesson_id = l.id AND up.user_id = ?
    WHERE l.level = ? OR l.language = ?
    ORDER BY (up.status IS NULL) DESC, up.status = "in_progress" DESC
    LIMIT 3
');
$stmt->execute([$userId, $user['literacy_level'], $user['preferred_language']]);
$recommended = $stmt->fetchAll();

$stmt = $pdo->prepare('
    SELECT l.title, l.language, l.level, up.status, up.best_score, up.last_attempt_at
    FROM user_progress up JOIN lessons l ON l.id = up.lesson_id
    WHERE up.user_id = ? ORDER BY up.last_attempt_at DESC LIMIT 5
');
$stmt->execute([$userId]);
$recentActivity = $stmt->fetchAll();

$overallPercent = $totalLessons > 0 ? (int)round(($completedCount / $totalLessons) * 100) : 0;

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container">

    <?php if (isset($_GET['welcome'])): ?>
      <div class="alert alert-success">Welcome to Adaptive Literacy, <?= e($user['full_name']) ?>! Let's find your first lesson.</div>
    <?php endif; ?>

    <div class="dash-hero">
      <div>
        <h2><?= t('welcome') ?>, <?= e(explode(' ', $user['full_name'])[0]) ?> <i class="fa-solid fa-hand-sparkles" style="color:var(--gold-500);"></i></h2>
        <p>Level: <strong style="color:var(--gold-500); text-transform:capitalize;"><?= e($user['literacy_level']) ?></strong> &middot; Language: <strong style="color:var(--gold-500); text-transform:capitalize;"><?= e($user['preferred_language']) ?></strong></p>
      </div>
      <div class="dash-stat-grid">
        <div class="dash-stat"><strong><?= $completedCount ?>/<?= $totalLessons ?></strong><span>Lessons completed</span></div>
        <div class="dash-stat"><strong><?= $avgScore ?>%</strong><span>Average quiz score</span></div>
        <div class="dash-stat"><strong><?= $overallPercent ?>%</strong><span>Overall progress</span></div>
      </div>
    </div>

    <div class="grid grid-2" style="margin-bottom:36px;">
      <div class="card flex" style="align-items:center; justify-content:center; gap:20px;">
        <div style="width:140px; height:140px;">
          <canvas id="progressChart"></canvas>
        </div>
        <div>
          <h3>Lesson Progress</h3>
          <p class="mb-0" style="font-size:0.9rem;">You have completed <strong><?= $completedCount ?></strong> out of <strong><?= $totalLessons ?></strong> available lessons.</p>
        </div>
      </div>
      <div class="card" style="display:flex; flex-direction:column; justify-content:center; gap:16px;">
        <div class="stat-card">
          <strong><?= $avgScore ?>%</strong>
          <span>Average score across quizzes</span>
        </div>
        <div class="stat-card">
          <strong><i class="fa-solid fa-layer-group" style="color:var(--gold-500); margin-right:8px;"></i><?= e(ucfirst($user['literacy_level'])) ?></strong>
          <span>Current adaptive level</span>
        </div>
      </div>
    </div>

    <div class="section-head">
      <div>
        <h2 class="mb-0">Recommended for you</h2>
        <p>Picked based on your level and language.</p>
      </div>
      <a href="<?= BASE_URL ?>/lessons.php" class="btn btn-outline btn-sm">See all lessons</a>
    </div>

    <?php if (!$recommended): ?>
      <div class="card empty-state"><p class="mb-0">No lessons available yet — check back soon.</p></div>
    <?php else: ?>
      <div class="grid grid-3" style="margin-bottom:40px;">
        <?php foreach ($recommended as $lesson): ?>
          <div class="lesson-card">
            <div class="lesson-top">
              <span class="badge badge-<?= e($lesson['level']) ?>"><?= e(ucfirst($lesson['level'])) ?></span>
              <div class="lesson-media-icons">
                <?php if ($lesson['audio_path']): ?><span class="icon-chip"><i class="fa-solid fa-headphones"></i></span><?php endif; ?>
                <?php if ($lesson['video_path']): ?><span class="icon-chip"><i class="fa-solid fa-clapperboard"></i></span><?php endif; ?>
              </div>
            </div>
            <h3 class="mb-0"><?= e($lesson['title']) ?></h3>
            <p><?= e($lesson['description']) ?></p>
            <div class="lesson-footer">
              <span style="font-size:0.8rem; color:var(--ink-300);"><?= e(ucfirst($lesson['language'])) ?></span>
              <a href="<?= BASE_URL ?>/lesson.php?id=<?= (int)$lesson['id'] ?>" class="btn btn-primary btn-sm"><?= $lesson['status'] ? t('continue') : t('start_lesson') ?></a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="section-head">
      <div>
        <h2 class="mb-0">Recent activity</h2>
      </div>
    </div>
    <div class="card">
      <?php if (!$recentActivity): ?>
        <p class="mb-0">You haven't started a lesson yet — pick one above to begin.</p>
      <?php else: ?>
        <table class="data-table">
          <thead>
            <tr><th>Lesson</th><th>Language</th><th>Status</th><th>Best score</th><th>Last activity</th></tr>
          </thead>
          <tbody>
            <?php foreach ($recentActivity as $row): ?>
              <tr>
                <td><?= e($row['title']) ?></td>
                <td><?= e(ucfirst($row['language'])) ?></td>
                <td><?= $row['status'] === 'completed' ? '<span style="color:var(--leaf-600);"><i class="fa-solid fa-circle-check"></i> Completed</span>' : '<span style="color:var(--clay-600);"><i class="fa-solid fa-circle-half-stroke"></i> In progress</span>' ?></td>
                <td><?= (int)$row['best_score'] ?>%</td>
                <td><?= e(date('d M, H:i', strtotime($row['last_attempt_at']))) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

  </div>
</section>

<!-- Chat Widget UI -->
<button class="chat-toggle-btn" id="chatToggle" aria-label="Open Chat">
  <i class="fa-solid fa-comment-dots"></i>
</button>

<div class="chat-widget" id="chatWidget">
  <div class="chat-header" id="chatHeader">
    <h3><i class="fa-solid fa-headset" style="margin-right:8px;"></i> Learning Assistant</h3>
    <i class="fa-solid fa-chevron-down"></i>
  </div>
  <div class="chat-messages" id="chatMessages">
    <!-- Messages injected via JS -->
  </div>
  <div class="chat-input">
    <input type="text" id="chatInput" placeholder="Ask a question..." aria-label="Chat input">
    <button id="chatSend" aria-label="Send message"><i class="fa-solid fa-paper-plane"></i></button>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart.js Progress Doughnut
    const ctx = document.getElementById('progressChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Remaining'],
                datasets: [{
                    data: [<?= $completedCount ?>, <?= max($totalLessons - $completedCount, 0) ?>],
                    backgroundColor: ['#E3A22B', '#E7EBF3'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: { cutout: '75%', plugins: { legend: { display: false }, tooltip: { enabled: true } } }
        });
    }

    // Chat Widget Logic
    const chatWidget = document.getElementById('chatWidget');
    const chatToggle = document.getElementById('chatToggle');
    const chatHeader = document.getElementById('chatHeader');
    const chatInput = document.getElementById('chatInput');
    const chatSend = document.getElementById('chatSend');
    const chatMessages = document.getElementById('chatMessages');

    function toggleChat() { chatWidget.classList.toggle('active'); if (chatWidget.classList.contains('active')) loadMessages(); }
    chatToggle.addEventListener('click', toggleChat);
    chatHeader.addEventListener('click', toggleChat);

    function appendMessage(msg, type) {
        const div = document.createElement('div');
        div.className = `chat-msg ${type}`;
        div.textContent = msg;
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    async function loadMessages() {
        try {
            const res = await fetch('<?= BASE_URL ?>/chat_api.php');
            const data = await res.json();
            if (data.status === 'success') {
                chatMessages.innerHTML = '';
                data.messages.forEach(m => appendMessage(m.message, m.is_admin_reply == 1 ? 'admin' : 'user'));
            }
        } catch (e) {
            console.error('Failed to load chat');
        }
    }

    async function sendMessage() {
        const text = chatInput.value.trim();
        if (!text) return;
        
        chatInput.value = '';
        appendMessage(text, 'user');
        
        try {
            await fetch('<?= BASE_URL ?>/chat_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            });
            // Simulate admin response
            setTimeout(() => {
                appendMessage("Thanks for your question! An instructor will respond shortly.", 'admin');
                const scrollOpts = { top: chatMessages.scrollHeight, behavior: 'smooth'};
                chatMessages.scrollTo(scrollOpts);
            }, 1000);
            
        } catch (e) {
            console.error('Failed to send message');
        }
    }

    chatSend.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessage(); });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
