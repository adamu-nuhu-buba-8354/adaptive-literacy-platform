/**
 * Adaptive Literacy Learning Platform
 * Vanilla JS: form validation + small dynamic UI behaviours.
 * No frameworks — keeps the footprint tiny for low-bandwidth users.
 */

(function () {
  'use strict';

  /* -----------------------------------------------------
     Generic field validation helpers
  ----------------------------------------------------- */
  function setInvalid(field, message) {
    field.classList.add('invalid');
    const errorEl = field.querySelector('.form-error');
    if (errorEl && message) errorEl.textContent = message;
  }

  function setValid(field) {
    field.classList.remove('invalid');
  }

  function validateField(input) {
    const field = input.closest('.form-field');
    if (!field) return true;

    if (input.hasAttribute('required') && !input.value.trim()) {
      setInvalid(field, 'This field is required.');
      return false;
    }

    if (input.type === 'email' && input.value.trim()) {
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailPattern.test(input.value.trim())) {
        setInvalid(field, 'Please enter a valid email address.');
        return false;
      }
    }

    if (input.hasAttribute('minlength') && input.value.length < Number(input.getAttribute('minlength'))) {
      setInvalid(field, `Please enter at least ${input.getAttribute('minlength')} characters.`);
      return false;
    }

    setValid(field);
    return true;
  }

  function attachLiveValidation(form) {
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach((input) => {
      input.addEventListener('blur', () => validateField(input));
      input.addEventListener('input', () => {
        if (input.closest('.form-field')?.classList.contains('invalid')) {
          validateField(input);
        }
      });
    });
  }

  /* -----------------------------------------------------
     Registration form: extra password-match + full validation
  ----------------------------------------------------- */
  const registerForm = document.getElementById('registerForm');
  if (registerForm) {
    attachLiveValidation(registerForm);

    registerForm.addEventListener('submit', function (e) {
      let valid = true;
      registerForm.querySelectorAll('input[required], select[required]').forEach((input) => {
        if (!validateField(input)) valid = false;
      });

      const password = registerForm.querySelector('#password');
      const confirm = registerForm.querySelector('#confirm_password');
      if (password && confirm && password.value !== confirm.value) {
        setInvalid(confirm.closest('.form-field'), 'Passwords do not match.');
        valid = false;
      }

      if (!valid) e.preventDefault();
    });
  }

  /* -----------------------------------------------------
     Login form: basic validation
  ----------------------------------------------------- */
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    attachLiveValidation(loginForm);
    loginForm.addEventListener('submit', function (e) {
      let valid = true;
      loginForm.querySelectorAll('input[required]').forEach((input) => {
        if (!validateField(input)) valid = false;
      });
      if (!valid) e.preventDefault();
    });
  }

  /* -----------------------------------------------------
     Quiz form: make sure every question has an answer,
     and gently scroll to the first unanswered question.
  ----------------------------------------------------- */
  const quizForm = document.getElementById('quizForm');
  if (quizForm) {
    quizForm.addEventListener('submit', function (e) {
      const questions = quizForm.querySelectorAll('.quiz-question');
      let firstUnanswered = null;

      questions.forEach((q) => {
        const answered = q.querySelector('input[type="radio"]:checked');
        q.style.outline = '';
        if (!answered && !firstUnanswered) {
          firstUnanswered = q;
        }
      });

      if (firstUnanswered) {
        e.preventDefault();
        firstUnanswered.style.outline = '2px solid #B85331';
        firstUnanswered.style.borderRadius = '10px';
        firstUnanswered.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
  }

  /* -----------------------------------------------------
     Admin lesson form: toggle helper hints for file paths
  ----------------------------------------------------- */
  const adminLessonForm = document.getElementById('adminLessonForm');
  if (adminLessonForm) {
    attachLiveValidation(adminLessonForm);
    adminLessonForm.addEventListener('submit', function (e) {
      let valid = true;
      adminLessonForm.querySelectorAll('input[required], select[required], textarea[required]').forEach((input) => {
        if (!validateField(input)) valid = false;
      });
      if (!valid) e.preventDefault();
    });
  }

  /* -----------------------------------------------------
     Quiz builder (admin): let admin add/remove question rows
  ----------------------------------------------------- */
  const addQuestionBtn = document.getElementById('addQuestionBtn');
  const questionsWrap = document.getElementById('questionsWrap');
  if (addQuestionBtn && questionsWrap) {
    let qIndex = questionsWrap.querySelectorAll('.question-block').length;

    addQuestionBtn.addEventListener('click', function () {
      const block = document.createElement('div');
      block.className = 'card question-block';
      block.style.marginBottom = '16px';
      block.innerHTML = `
        <div class="flex justify-between items-center" style="margin-bottom:10px;">
          <strong>Question ${qIndex + 1}</strong>
          <button type="button" class="btn btn-outline btn-sm remove-question">Remove</button>
        </div>
        <div class="form-field">
          <label>Question text</label>
          <textarea name="questions[${qIndex}][question]" required rows="2"></textarea>
        </div>
        <div class="grid grid-2">
          <div class="form-field"><label>Option A</label><input type="text" name="questions[${qIndex}][option_a]" required></div>
          <div class="form-field"><label>Option B</label><input type="text" name="questions[${qIndex}][option_b]" required></div>
          <div class="form-field"><label>Option C</label><input type="text" name="questions[${qIndex}][option_c]" required></div>
          <div class="form-field"><label>Option D</label><input type="text" name="questions[${qIndex}][option_d]" required></div>
        </div>
        <div class="form-field">
          <label>Correct option</label>
          <select name="questions[${qIndex}][correct_option]" required>
            <option value="a">A</option><option value="b">B</option><option value="c">C</option><option value="d">D</option>
          </select>
        </div>`;
      questionsWrap.appendChild(block);
      qIndex++;
    });

    questionsWrap.addEventListener('click', function (e) {
      if (e.target.classList.contains('remove-question')) {
        e.target.closest('.question-block').remove();
      }
    });
  }

  /* -----------------------------------------------------
     Auto-dismiss alerts after a few seconds (non-error ones)
  ----------------------------------------------------- */
  document.querySelectorAll('.alert-success').forEach((alert) => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.4s ease';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 400);
    }, 6000);
  });

  /* -----------------------------------------------------
     Staggered entrance: apply animation-delay to
     dynamically-added quiz question blocks
   ----------------------------------------------------- */
  function staggerAnimate(containerSelector, itemSelector, baseDelay) {
    baseDelay = baseDelay || 50;
    var container = document.querySelector(containerSelector);
    if (!container) return;
    var items = container.querySelectorAll(itemSelector);
    for (var i = 0; i < items.length; i++) {
      if (!items[i].style.animationDelay) {
        items[i].style.animationDelay = (i * baseDelay) + 'ms';
      }
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    staggerAnimate('#questionsWrap', '.question-block', 60);
  });

  /* -----------------------------------------------------
     Re-run stagger when new question block added
   ----------------------------------------------------- */
  var questionsWrap = document.getElementById('questionsWrap');
  if (questionsWrap) {
    var origAppend = questionsWrap.appendChild.bind(questionsWrap);
    questionsWrap.appendChild = function (node) {
      origAppend(node);
      if (node.classList && node.classList.contains('question-block')) {
        node.style.animation = 'none';
        node.offsetHeight;
        node.style.animation = '';
        var blocks = questionsWrap.querySelectorAll('.question-block');
        for (var i = 0; i < blocks.length; i++) {
          blocks[i].style.animationDelay = (i * 60) + 'ms';
        }
      }
    };
  }

  /* -----------------------------------------------------
     Smooth hover for back-to-lessons links
   ----------------------------------------------------- */
  var backLinks = document.querySelectorAll('a[href*="lessons.php"]');
  for (var i = 0; i < backLinks.length; i++) {
    (function (link) {
      var txt = link.textContent.trim();
      if (txt === '← Back to lessons' || txt === '← Back to lesson') {
        link.style.transition = 'color 0.15s ease, transform 0.15s ease';
        link.addEventListener('mouseenter', function () {
          link.style.color = 'var(--gold-500)';
          link.style.transform = 'translateX(-3px)';
        });
        link.addEventListener('mouseleave', function () {
          link.style.color = '';
          link.style.transform = '';
        });
      }
    })(backLinks[i]);
  }
})();
