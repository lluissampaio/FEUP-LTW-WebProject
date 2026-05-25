'use strict';

(function () {
  const list = document.querySelector('.schedule-cards');
  if (!list) return;
  const csrf = list.dataset.csrf;
  if (!csrf) return;

  async function send(action, scheduleId) {
    const res = await fetch('../api/api_enroll.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ csrf_token: csrf, action: action, schedule_id: scheduleId }),
    });
    let data = null;
    try { data = await res.json(); } catch (_) { /* ignore */ }
    if (!res.ok || !data || !data.ok) {
      const msg = (data && data.error) ? data.error : 'Something went wrong.';
      flash('error', msg);
      return null;
    }
    return data;
  }

  function flash(type, text) {
    let region = document.getElementById('messages');
    if (!region) {
      region = document.createElement('section');
      region.id = 'messages';
      region.setAttribute('aria-label', 'System messages');
      document.querySelector('main')?.before(region);
    }
    const article = document.createElement('article');
    article.className = 'message message--' + type;
    article.textContent = text;
    region.appendChild(article);
    setTimeout(() => article.remove(), 4000);
  }

  function rerenderCard(card, data) {
    const cap = card.querySelector('.schedule-card-capacity');
    if (cap && data.capacity != null) {
      const spots = Math.max(0, data.capacity - (data.enrolled_count || 0));
      cap.innerHTML = data.full
        ? '<span class="status-tag status-tag--full">Full</span>'
        : '<span class="status-tag status-tag--open">' + spots + ' spot' + (spots === 1 ? '' : 's') + ' left</span>';
    }
    const actions = card.querySelector('.schedule-card-actions');
    if (!actions) return;
    const scheduleId = card.dataset.scheduleId;

    const buildForm = (action, label, btnClass, statusTag) => {
      const form = document.createElement('form');
      form.method = 'post';
      form.dataset.ajax = action;
      form.innerHTML =
        '<input type="hidden" name="csrf_token" value="' + csrf + '">' +
        '<input type="hidden" name="schedule_id" value="' + scheduleId + '">' +
        (statusTag ? '<span class="enrolled-tag">' + statusTag + '</span>' : '') +
        '<button type="submit" class="' + btnClass + '">' + label + '</button>';
      return form;
    };

    actions.innerHTML = '';
    if (data.state === 'enrolled') {
      actions.appendChild(buildForm('cancel', 'Cancel', 'btn-outline', 'Enrolled'));
    } else if (data.state === 'waiting') {
      actions.appendChild(buildForm('waitlist_leave', 'Leave waitlist', 'btn-outline', 'On waitlist'));
    } else if (data.full) {
      actions.appendChild(buildForm('waitlist_join', 'Join waitlist', 'btn-outline', null));
    } else {
      actions.appendChild(buildForm('enrol', 'Enrol', 'btn-primary', null));
    }
  }

  list.addEventListener('submit', async (ev) => {
    const form = ev.target.closest('form[data-ajax], .js-enroll-form, .js-cancel-form');
    if (!form) return;
    ev.preventDefault();
    const card = form.closest('.schedule-card');
    if (!card) return;

    let action = form.dataset.ajax;
    if (!action) {
      if (form.classList.contains('js-enroll-form')) action = 'enrol';
      else if (form.classList.contains('js-cancel-form')) action = 'cancel';
    }
    if (!action) return;

    const scheduleId = card.dataset.scheduleId;
    const btn = form.querySelector('button[type=submit]');
    if (btn) btn.disabled = true;

    const data = await send(action, scheduleId);
    if (btn) btn.disabled = false;
    if (!data) return;

    rerenderCard(card, data);
    const msg = action === 'enrol'         ? 'Booking confirmed.'
              : action === 'cancel'        ? 'Booking cancelled.'
              : action === 'waitlist_join' ? 'Added to the waitlist.'
              :                              'You left the waitlist.';
    flash('success', msg);
  });
})();
