'use strict';

/* Equipment reservation form — progressive enhancement.
   Shows a live "chosen slot" summary as the member picks equipment, date,
   time and duration, and disables the submit button while the POST is in
   flight so the form cannot be double-submitted. With JS off the form still
   submits normally and the PHP page validates everything server-side. */
(function () {
  const form = document.getElementById('reservation-form');
  if (!form) return;

  const equipmentSel = form.querySelector('#r-equipment');
  const dateInput = form.querySelector('#r-date');
  const timeSel = form.querySelector('#r-time');
  const durationSel = form.querySelector('#r-duration');
  const summary = form.querySelector('#reservation-summary');
  const submit = form.querySelector('#reservation-submit');

  function selectedText(el) {
    if (!el || el.selectedIndex < 0) return '';
    return el.options[el.selectedIndex].text.trim();
  }

  function updateSummary() {
    if (!summary) return;
    if (!equipmentSel || !equipmentSel.value) {
      summary.textContent = '';
      return;
    }
    const equip = selectedText(equipmentSel);
    const date = dateInput && dateInput.value ? dateInput.value : '';
    const time = timeSel ? timeSel.value : '';
    const duration = durationSel ? durationSel.value : '';
    if (!date || !time) {
      summary.textContent = '';
      return;
    }
    summary.textContent =
      'Reserving ' + equip + ' on ' + date + ' at ' + time + ' for ' + duration + ' min.';
  }

  [equipmentSel, dateInput, timeSel, durationSel].forEach((el) => {
    if (!el) return;
    el.addEventListener('change', updateSummary);
    el.addEventListener('input', updateSummary);
  });

  form.addEventListener('submit', () => {
    if (submit) {
      submit.disabled = true;
      submit.textContent = 'Reserving…';
    }
  });

  updateSummary();
})();
