'use strict';

/* Client-side form validation as progressive enhancement. Server-side checks in
   the matching actions/ handlers remain the real guard — this only gives instant
   feedback. Activated on any <form data-validate>. Error nodes are injected next
   to each field so templates need no extra markup. */
(function () {
  const forms = document.querySelectorAll('form[data-validate]');
  if (!forms.length) return;

  const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  function wrapOf(el) { return el.closest('.form-field') || el.parentElement; }

  function showError(el, msg) {
    const wrap = wrapOf(el);
    let err = wrap.querySelector('.field-error');
    if (!err) {
      err = document.createElement('span');
      err.className = 'field-error';
      err.setAttribute('role', 'alert');
      wrap.appendChild(err);
    }
    err.textContent = msg;
    err.hidden = false;
    el.setAttribute('aria-invalid', 'true');
  }

  function clearError(el) {
    const wrap = wrapOf(el);
    const err = wrap.querySelector('.field-error');
    if (err) err.hidden = true;
    el.removeAttribute('aria-invalid');
  }

  // Validate one field against its own attributes. Returns true if valid.
  function validateField(el) {
    const val = (el.value || '').trim();

    if (el.hasAttribute('required') && val === '') {
      showError(el, 'This field is required.');
      return false;
    }
    if (el.type === 'email' && val !== '' && !EMAIL_RE.test(val)) {
      showError(el, 'Enter a valid email address.');
      return false;
    }
    const min = parseInt(el.getAttribute('minlength') || '0', 10);
    if (el.type === 'password' && val !== '' && min > 0 && val.length < min) {
      showError(el, 'Must be at least ' + min + ' characters.');
      return false;
    }
    clearError(el);
    return true;
  }

  /* ---------- password strength meter ---------- */

  function strengthScore(v) {
    let s = 0;
    if (v.length >= 8) s++;
    if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
    if (/\d/.test(v)) s++;
    if (/[^A-Za-z0-9]/.test(v)) s++;
    return s; // 0..4
  }

  function setupStrength(el) {
    const wrap = wrapOf(el);
    const meter = document.createElement('div');
    meter.className = 'pw-meter';
    meter.innerHTML = '<span class="pw-meter__bar"></span><span class="pw-meter__label"></span>';
    wrap.appendChild(meter);
    const bar = meter.querySelector('.pw-meter__bar');
    const label = meter.querySelector('.pw-meter__label');
    const labels = ['Too weak', 'Weak', 'Fair', 'Good', 'Strong'];

    el.addEventListener('input', () => {
      const score = el.value ? strengthScore(el.value) : 0;
      meter.dataset.level = String(score);
      bar.style.width = (score / 4 * 100) + '%';
      label.textContent = el.value ? labels[score] : '';
    });
  }

  /* ---------- character counter ---------- */

  function setupCounter(el) {
    const max = parseInt(el.getAttribute('data-counter') || '0', 10);
    const wrap = wrapOf(el);
    const counter = document.createElement('span');
    counter.className = 'char-counter';
    wrap.appendChild(counter);
    const update = () => {
      const len = el.value.length;
      counter.textContent = max ? len + ' / ' + max : String(len);
      counter.classList.toggle('char-counter--over', max > 0 && len > max);
    };
    el.addEventListener('input', update);
    update();
  }

  /* ---------- wire each form ---------- */

  forms.forEach((form) => {
    // Turn off native bubble validation so our inline messages own the UX.
    // (Server-side validation in actions/ remains the real guard.)
    form.setAttribute('novalidate', 'novalidate');

    const fields = Array.from(form.querySelectorAll('input, select, textarea'))
      .filter((el) => el.type !== 'hidden' && el.type !== 'submit');

    // confirm-password pairing
    const confirmEl = form.querySelector('[name*=confirm]');
    const mainPw = Array.from(form.querySelectorAll('input[type=password]'))
      .find((el) => el !== confirmEl) || null;

    function checkMatch() {
      if (!confirmEl || !mainPw) return true;
      if (confirmEl.value === '' && mainPw.value === '') { clearError(confirmEl); return true; }
      if (confirmEl.value !== mainPw.value) {
        showError(confirmEl, 'Passwords do not match.');
        return false;
      }
      clearError(confirmEl);
      return true;
    }

    fields.forEach((el) => {
      if (el.hasAttribute('data-strength')) setupStrength(el);
      if (el.hasAttribute('data-counter')) setupCounter(el);
      // Re-validate on the fly once the user has interacted.
      el.addEventListener('input', () => { validateField(el); if (confirmEl) checkMatch(); });
      el.addEventListener('blur', () => { validateField(el); if (confirmEl) checkMatch(); });
    });

    form.addEventListener('submit', (ev) => {
      let ok = true;
      let firstBad = null;
      fields.forEach((el) => {
        if (!validateField(el)) { ok = false; firstBad = firstBad || el; }
      });
      if (!checkMatch()) { ok = false; firstBad = firstBad || confirmEl; }
      if (!ok) {
        ev.preventDefault();
        if (firstBad) firstBad.focus();
      }
    });
  });
})();
