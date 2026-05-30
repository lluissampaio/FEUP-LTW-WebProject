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
})();
