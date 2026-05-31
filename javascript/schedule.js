'use strict';

/* Classes page client logic, progressive enhancement over the server-rendered page:
   1. Live filtering   — the filter form updates the schedule via api/api_schedule.php
                         (JSON) with no page reload.
   2. Enrol / cancel / waitlist — submitted via api/api_enroll.php and re-rendered in place.
   With JS off, the GET filter form and the POST action forms still work on their own. */
(function () {
  const list = document.querySelector('.schedule-cards');
  if (!list) return;
  const csrf = list.dataset.csrf;
  if (!csrf) return;
  const loggedIn = list.dataset.loggedIn === '1';

  const filterForm = document.getElementById('class-filter-form');
  const countEl = document.getElementById('schedule-count');
  const emptyEl = document.getElementById('schedule-empty');

  /* ---------- shared helpers ---------- */

  function esc(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function flash(type, text) {
    let region = document.getElementById('messages');
    if (!region) {
      region = document.createElement('section');
      region.id = 'messages';
      region.setAttribute('aria-label', 'System messages');
      region.setAttribute('aria-live', 'polite');
      region.setAttribute('role', 'status');
      document.querySelector('main')?.before(region);
    }
    const article = document.createElement('article');
    article.className = 'message message-' + type;
    const span = document.createElement('span');
    span.textContent = text;
    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'message-close';
    close.setAttribute('aria-label', 'Dismiss message');
    close.innerHTML = '&times;';
    article.appendChild(span);
    article.appendChild(close);
    region.appendChild(article);
    setTimeout(() => article.remove(), 6000);
  }

  // Build one enrol/cancel/waitlist form. `action` matches api_enroll.php's action param.
  function buildForm(action, scheduleId, label, btnClass, statusTag) {
    return '<form method="post" data-ajax="' + action + '">' +
      '<input type="hidden" name="csrf_token" value="' + esc(csrf) + '">' +
      '<input type="hidden" name="schedule_id" value="' + (scheduleId | 0) + '">' +
      (statusTag ? '<span class="enrolled-tag">' + statusTag + '</span>' : '') +
      '<button type="submit" class="' + btnClass + '">' + label + '</button>' +
      '</form>';
  }

  function capacityHtml(s) {
    if (s.full) return '<span class="status-tag status-tag-full">Full</span>';
    const spots = Math.max(0, (s.capacity || 0) - (s.enrolled || 0));
    return '<span class="status-tag status-tag-open">' + spots + ' spot' + (spots === 1 ? '' : 's') + ' left</span>';
  }

  function actionsHtml(s) {
    if (!loggedIn) return '<a href="login.php" class="btn-outline">Sign in to book</a>';
    if (s.my_state === 'enrolled') return buildForm('cancel', s.schedule_id, 'Cancel', 'btn-outline', 'Enrolled');
    if (s.my_state === 'waiting')  return buildForm('waitlist_leave', s.schedule_id, 'Leave waitlist', 'btn-outline', 'On waitlist');
    if (s.full)                    return buildForm('waitlist_join', s.schedule_id, 'Join waitlist', 'btn-outline', null);
    return buildForm('enrol', s.schedule_id, 'Enrol', 'btn-primary', null);
  }

  // Full <li> card, mirroring drawScheduleList() in templates/classes.tpl.php.
  function cardHtml(s) {
    return '<li class="schedule-card" data-schedule-id="' + (s.schedule_id | 0) + '">' +
      '<header class="schedule-card-head">' +
        '<p class="schedule-card-day">' + esc(s.day) + ' &middot; ' + esc(s.start_time) + '</p>' +
        '<p class="schedule-card-meta">' + (s.duration_min | 0) + ' min &middot; ' + esc(s.location) + '</p>' +
      '</header>' +
      '<h3 class="schedule-card-title"><a href="class.php?id=' + (s.class_id | 0) + '">' + esc(s.class) + '</a></h3>' +
      '<p class="schedule-card-type">' + esc(s.type) + '</p>' +
      '<p class="schedule-card-coach">with <a href="trainer.php?id=' + (s.trainer_id | 0) + '">' + esc(s.trainer) + '</a></p>' +
      '<p class="schedule-card-capacity">' + capacityHtml(s) + '</p>' +
      '<div class="schedule-card-actions">' + actionsHtml(s) + '</div>' +
    '</li>';
  }

  /* ---------- live filtering ---------- */

  async function applyFilters() {
    const params = new URLSearchParams(new FormData(filterForm));
    // Drop empty values so the URL stays clean.
    for (const [k, v] of [...params.entries()]) if (v === '') params.delete(k);

    list.classList.add('is-updating');
    list.setAttribute('aria-busy', 'true');
    try {
      const res = await fetch('../api/api_schedule.php?' + params.toString());
      const data = await res.json();
      if (!res.ok || !data || !data.ok) throw new Error((data && data.error) || 'Request failed');

      list.innerHTML = (data.schedule || []).map(cardHtml).join('');
      if (countEl) countEl.textContent = String(data.count || 0);
      if (emptyEl) emptyEl.hidden = (data.count || 0) !== 0 ? true : false;

      const qs = params.toString();
      history.replaceState(null, '', qs ? 'classes.php?' + qs : 'classes.php');
    } catch (err) {
      filterForm.submit(); // fall back to a normal navigation
    } finally {
      list.classList.remove('is-updating');
      list.removeAttribute('aria-busy');
    }
  }

  if (filterForm) {
    filterForm.addEventListener('submit', (ev) => { ev.preventDefault(); applyFilters(); });
    filterForm.querySelectorAll('select').forEach((sel) => sel.addEventListener('change', applyFilters));
  }

  /* ---------- enrol / cancel / waitlist actions ---------- */

  async function send(action, scheduleId) {
    const res = await fetch('../api/api_enroll.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ csrf_token: csrf, action: action, schedule_id: scheduleId }),
    });
    let data = null;
    try { data = await res.json(); } catch (_) { /* ignore */ }
    if (!res.ok || !data || !data.ok) {
      flash('error', (data && data.error) ? data.error : 'Something went wrong.');
      return null;
    }
    return data;
  }

  // Re-render a single card in place after an action, reusing the same builders.
  function rerenderCard(card, data) {
    const cap = card.querySelector('.schedule-card-capacity');
    if (cap && data.capacity != null) {
      cap.innerHTML = capacityHtml({ full: data.full, capacity: data.capacity, enrolled: data.enrolled_count });
    }
    const actions = card.querySelector('.schedule-card-actions');
    if (!actions) return;
    const scheduleId = card.dataset.scheduleId;
    const state = data.state === 'enrolled' ? 'enrolled' : data.state === 'waiting' ? 'waiting' : null;
    actions.innerHTML = actionsHtml({ my_state: state, full: data.full, schedule_id: scheduleId });
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
    flash('success',
      action === 'enrol'         ? 'Booking confirmed.'
      : action === 'cancel'        ? 'Booking cancelled.'
      : action === 'waitlist_join' ? 'Added to the waitlist.'
      :                              'You left the waitlist.');
  });
})();
