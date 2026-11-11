<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$lessonId = (int)($_GET['lesson_id'] ?? $_POST['lesson_id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM lessons WHERE id = ?');
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch();
if (!$lesson) {
    header('Location: ' . BASE_URL . '/admin/lessons.php');
    exit;
}

$message = '';

// Add new questions submitted from the builder form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['questions'])) {
    $stmt = $pdo->prepare('
        INSERT INTO quiz_questions (lesson_id, question, option_a, option_b, option_c, option_d, correct_option)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');
    foreach ($_POST['questions'] as $q) {
        if (trim($q['question'] ?? '') === '') continue;
        $stmt->execute([
            $lessonId, trim($q['question']),
            trim($q['option_a']), trim($q['option_b']), trim($q['option_c']), trim($q['option_d']),
            in_array($q['correct_option'], ['a', 'b', 'c', 'd'], true) ? $q['correct_option'] : 'a',
        ]);
    }
    $message = 'Questions saved successfully!';
}

// Delete a single question
if (isset($_GET['delete_question'])) {
    $stmt = $pdo->prepare('DELETE FROM quiz_questions WHERE id = ? AND lesson_id = ?');
    $stmt->execute([(int)$_GET['delete_question'], $lessonId]);
    header('Location: ' . BASE_URL . '/admin/quiz_builder.php?lesson_id=' . $lessonId . '&removed=1');
    exit;
}

$existing = $pdo->prepare('SELECT * FROM quiz_questions WHERE lesson_id = ? ORDER BY id');
$existing->execute([$lessonId]);
$existingQuestions = $existing->fetchAll();

$hasContent = strlen(trim($lesson['content_text'] ?? '')) >= 20;

$pageTitle = 'Quiz Builder';
$activeAdminNav = 'lessons';
require_once __DIR__ . '/_header.php';
?>

<style>
/* ── Quiz Builder Enhancements ─────────────────────────────── */
.generate-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: var(--gold-500);
  color: var(--indigo-900);
  border: none;
  border-radius: 999px;
  padding: 10px 22px;
  font-size: 0.92rem;
  font-weight: 600;
  cursor: pointer;
  transition: box-shadow .2s, transform .15s;
  box-shadow: 0 4px 14px rgba(227, 162, 43, .35);
  font-family: var(--font-body);
}
.generate-btn:hover:not(:disabled) { box-shadow: 0 6px 20px rgba(227, 162, 43, .5); transform: translateY(-1px); }
.generate-btn:disabled { opacity: .55; cursor: not-allowed; transform: none; }
.generate-btn .spinner {
  width: 16px; height: 16px;
  border: 2px solid rgba(30, 42, 71, .3);
  border-top-color: var(--indigo-900);
  border-radius: 50%;
  animation: spin .7s linear infinite;
  display: none;
}
.generate-btn.loading .spinner { display: inline-block; }
@keyframes spin { to { transform: rotate(360deg); } }

#generateAlert {
  display: none;
  border-radius: 10px;
  padding: 12px 16px;
  margin-bottom: 16px;
  font-size: 0.9rem;
  font-weight: 500;
}
#generateAlert.error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
#generateAlert.success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }

.ai-badge {
  display: inline-flex; align-items: center; gap: 5px;
  background: var(--gold-100);
  color: var(--clay-600);
  font-size: 0.72rem; font-weight: 700; letter-spacing: .04em;
  padding: 2px 8px; border-radius: 20px;
  vertical-align: middle; margin-left: 8px;
}

.question-block {
  background: var(--cream-050, #fafaf9);
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 16px;
  position: relative;
  transition: box-shadow .2s;
}
.question-block:hover { box-shadow: 0 4px 16px rgba(0,0,0,.07); }
.question-block .remove-q {
  position: absolute; top: 12px; right: 12px;
  background: none; border: none; cursor: pointer;
  color: #9ca3af; font-size: 1.1rem; line-height: 1;
  transition: color .15s;
}
.question-block .remove-q:hover { color: #ef4444; }
.question-block label { font-weight: 600; font-size: 0.85rem; color: #374151; margin-bottom: 4px; display: block; }
.question-block input[type="text"],
.question-block select {
  width: 100%; box-sizing: border-box;
  border: 1px solid #d1d5db; border-radius: 8px;
  padding: 8px 12px; font-size: 0.9rem;
  transition: border-color .2s;
  background: #fff;
}
.question-block input[type="text"]:focus,
.question-block select:focus { border-color: var(--indigo-700); outline: none; box-shadow: 0 0 0 3px rgba(30,42,71,.1); }
.question-block .options-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px; }
@media (max-width: 600px) { .question-block .options-grid { grid-template-columns: 1fr; } }

.ai-preview-tag {
  font-size: 0.7rem; font-weight: 700; letter-spacing: .05em;
  background: var(--gold-100);
  color: var(--clay-600); padding: 1px 7px; border-radius: 20px;
  margin-left: 6px; vertical-align: middle;
}
</style>

<a href="<?= BASE_URL ?>/admin/lessons.php" style="font-size:0.9rem; color:var(--ink-600);">← Back to lessons</a>
<div class="section-head" style="margin-top:14px;">
  <div>
    <h1 class="mb-0">Quiz: <?= e($lesson['title']) ?></h1>
    <p>Add multiple-choice questions learners will answer after this lesson.</p>
  </div>
</div>

<?php if ($message): ?>
  <div class="alert alert-success"><?= e($message) ?></div>
<?php endif; ?>
<?php if (isset($_GET['removed'])): ?>
  <div class="alert alert-success">Question removed.</div>
<?php endif; ?>

<!-- ── Existing questions ───────────────────────────────────── -->
<div class="card" style="margin-bottom:28px;">
  <h3>Existing questions (<?= count($existingQuestions) ?>)</h3>
  <?php if (!$existingQuestions): ?>
    <p class="mb-0">No questions yet — add some below or use AI generation.</p>
  <?php else: ?>
    <?php foreach ($existingQuestions as $i => $q): ?>
      <div class="quiz-question">
        <div class="flex justify-between items-center">
          <h3 style="margin-bottom:8px;">Q<?= $i + 1 ?>. <?= e($q['question']) ?></h3>
          <a href="?lesson_id=<?= $lessonId ?>&delete_question=<?= (int)$q['id'] ?>"
             class="btn btn-outline btn-sm"
             style="border-color:var(--clay-600); color:var(--clay-600);"
             onclick="return confirm('Remove this question?');">Remove</a>
        </div>
        <div class="quiz-options">
          <?php foreach (['a', 'b', 'c', 'd'] as $opt): ?>
            <div class="quiz-option <?= $opt === $q['correct_option'] ? 'correct' : '' ?>">
              <span><?= strtoupper($opt) ?>. <?= e($q['option_' . $opt]) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- ── Add / Generate questions ───────────────────────────── -->
<div class="card">
  <div class="flex justify-between items-center" style="margin-bottom:16px; flex-wrap:wrap; gap:10px;">
    <h3 class="mb-0">Add new questions</h3>
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">

      <?php if ($hasContent): ?>
      <button type="button" id="generateBtn" class="generate-btn" title="Generate questions from the lesson text using AI">
          <span class="spinner"></span>
          <span id="generateBtnLabel">Generate from lesson text</span>
        </button>
      <?php else: ?>
        <span style="font-size:0.82rem; color:#9ca3af; font-style:italic;">Add lesson content text to enable AI generation</span>
      <?php endif; ?>

      <button type="button" id="addQuestionBtn" class="btn btn-outline btn-sm">+ Add question</button>
    </div>
  </div>

  <div id="generateAlert"></div>

  <form method="POST" action="?lesson_id=<?= $lessonId ?>" id="quizForm">
    <input type="hidden" name="lesson_id" value="<?= $lessonId ?>">
    <div id="questionsWrap"></div>
    <button type="submit" class="btn btn-primary" id="saveBtn" style="display:none;">Save questions</button>
  </form>
</div>

<script>
const lessonId = <?= (int)$lessonId ?>;
const apiBase  = '<?= BASE_URL ?>';
let   questionCount = 0;

/* ── Build one editable question row ─────────────────────── */
function addQuestionRow(prefill = {}, isAI = false) {
  questionCount++;
  const idx = questionCount;
  const wrap = document.getElementById('questionsWrap');
  const block = document.createElement('div');
  block.className = 'question-block';
  block.dataset.idx = idx;

  const aiTag = isAI ? '<span class="ai-preview-tag">AI</span>' : '';

  const opts = ['a','b','c','d'];
  const optionsHtml = opts.map(o => `
    <div>
      <label>Option ${o.toUpperCase()}</label>
      <input type="text" name="questions[${idx}][option_${o}]"
             value="${esc(prefill['option_' + o] ?? '')}" required placeholder="Option ${o.toUpperCase()}">
    </div>
  `).join('');

  const correctOptions = opts.map(o =>
    `<option value="${o}" ${(prefill.correct_option||'a')===o?'selected':''}>${o.toUpperCase()}</option>`
  ).join('');

  block.innerHTML = `
    <button type="button" class="remove-q" title="Remove this question"><i class="fa-solid fa-xmark" style="font-size:0.85rem;"></i></button>
    <label>Question ${aiTag}</label>
    <input type="text" name="questions[${idx}][question]"
           value="${esc(prefill.question ?? '')}" required
           placeholder="Enter the question here…" style="margin-bottom:10px;">
    <div class="options-grid">${optionsHtml}</div>
    <div style="margin-top:12px;">
      <label>Correct answer</label>
      <select name="questions[${idx}][correct_option]">${correctOptions}</select>
    </div>
  `;

  block.querySelector('.remove-q').addEventListener('click', () => {
    block.remove();
    togglSaveBtn();
  });

  wrap.appendChild(block);
  togglSaveBtn();
  block.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function togglSaveBtn() {
  document.getElementById('saveBtn').style.display =
    document.querySelectorAll('.question-block').length ? 'inline-flex' : 'none';
}

function esc(str) {
  return String(str)
    .replace(/&/g,'&amp;').replace(/"/g,'&quot;')
    .replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

/* ── Manual add─button ─────────────────────────────────────── */
document.getElementById('addQuestionBtn').addEventListener('click', () => {
  addQuestionRow();
});

/* ── AI Generate button ────────────────────────────────────── */
<?php if ($hasContent): ?>
const generateBtn   = document.getElementById('generateBtn');
const generateAlert = document.getElementById('generateAlert');
const generateLabel = document.getElementById('generateBtnLabel');

function showAlert(type, msg) {
  generateAlert.className = type;
  generateAlert.textContent = msg;
  generateAlert.style.display = 'block';
}
function hideAlert() {
  generateAlert.style.display = 'none';
  generateAlert.className = '';
}

generateBtn.addEventListener('click', async () => {
  hideAlert();
  generateBtn.disabled = true;
  generateBtn.classList.add('loading');
  generateLabel.textContent = 'Generating…';

  try {
    const res = await fetch(`${apiBase}/admin/generate_quiz_api.php`, {
      method : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body   : JSON.stringify({ lesson_id: lessonId })
    });

    const data = await res.json();

    if (!res.ok || data.error) {
      showAlert('error', '<i class="fa-solid fa-triangle-exclamation" style="color:var(--clay-600);"></i> ' + (data.error || `Server error (HTTP ${res.status})`));
      return;
    }

    if (!Array.isArray(data.questions) || data.questions.length === 0) {
      showAlert('error', '<i class="fa-solid fa-triangle-exclamation" style="color:var(--clay-600);"></i> The AI returned no usable questions. Try again.');
      return;
    }

    data.questions.forEach(q => addQuestionRow(q, true));
    showAlert('success', `<i class="fa-solid fa-circle-check" style="color:var(--leaf-600);"></i> ${data.questions.length} questions generated — review them below, then click Save.`);

  } catch (err) {
    showAlert('error', '<i class="fa-solid fa-triangle-exclamation" style="color:var(--clay-600);"></i> Network error: ' + err.message);
  } finally {
    generateBtn.disabled = false;
    generateBtn.classList.remove('loading');
    generateLabel.textContent = 'Generate from lesson text';
  }
});
<?php endif; ?>

/* ── Auto-show one blank row on first load ─────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  <?php if (!$existingQuestions): ?>
  addQuestionRow();
  <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/_footer.php'; ?>
