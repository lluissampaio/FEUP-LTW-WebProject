'use strict';

/* Behaviour for admin/trainer controls, kept out of inline on* attributes:
   - [data-confirm]    on a submit button asks for confirmation before its form submits.
   - [data-autosubmit] on a <select> submits its form when the value changes.
   Delegated from document so it covers content rendered on any page. */
(function () {
  document.addEventListener('submit', function (ev) {
    const trigger = ev.submitter || ev.target.querySelector('[data-confirm]');
    const message = trigger && trigger.dataset ? trigger.dataset.confirm : null;
    if (message && !window.confirm(message)) ev.preventDefault();
  });

  document.addEventListener('change', function (ev) {
    const el = ev.target;
    if (el.matches && el.matches('[data-autosubmit]') && el.form) {
      if (typeof el.form.requestSubmit === 'function') el.form.requestSubmit();
      else el.form.submit();
    }
  });

  // Dismiss a flash message when its close button is clicked (works for both the
  // server-rendered banner and JS-injected flashes, since this is delegated).
  document.addEventListener('click', function (ev) {
    const btn = ev.target.closest ? ev.target.closest('.message__close') : null;
    if (!btn) return;
    const msg = btn.closest('.message');
    if (msg) msg.remove();
  });

  // Make the flash-message region announce to assistive tech.
  document.addEventListener('DOMContentLoaded', function () {
    const region = document.getElementById('messages');
    if (region) {
      region.setAttribute('aria-live', 'polite');
      region.setAttribute('role', 'status');
    }
  });
})();
