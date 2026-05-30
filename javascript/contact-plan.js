'use strict';

/* When the contact page is opened from a membership "Apply" link
   (contact.php?plan=Premium), pre-select that plan in the "Plan of interest"
   dropdown and focus the message box so the member just writes their note.
   Progressive enhancement: without JS the member can still pick the plan manually. */
(function () {
  var plan = new URLSearchParams(window.location.search).get('plan');
  if (!plan) return;

  var select = document.getElementById('plan');
  if (select) {
    for (var i = 0; i < select.options.length; i++) {
      var opt = select.options[i];
      if (opt.value === plan || opt.text === plan) { select.selectedIndex = i; break; }
    }
  }

  var message = document.getElementById('message');
  if (message) {
    message.placeholder = 'Tell us why you would like the ' + plan + ' plan…';
    message.focus();
  }
})();
