'use strict';

/* Live equipment availability filter.
   Progressive enhancement over the plain GET form: when JS is on we fetch
   api/api_equipment.php and rebuild the results without a full page reload.
   With JS off, the form still submits and the PHP page renders the same view. */
(function () {
  const form = document.getElementById('equipment-filter-form');
  const results = document.getElementById('equipment-results');
  const countEl = document.getElementById('equipment-count');
  if (!form || !results) return;

  const locationSel = form.querySelector('[name=location]');
  const typeSel = form.querySelector('[name=type]');

  function esc(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function row(e) {
    const statusClass = 'status-tag status-tag--' + esc(String(e.status).replace(/_/g, '-'));
    const statusLabel = esc(String(e.status).replace(/_/g, ' '));
    return '<tr>' +
      '<td>' + esc(e.name) + '</td>' +
      '<td>' + esc(e.type) + '</td>' +
      '<td>' + esc(e.location) + '</td>' +
      '<td>' + (e.available | 0) + ' / ' + (e.quantity | 0) + '</td>' +
      '<td><span class="' + statusClass + '">' + statusLabel + '</span></td>' +
      '</tr>';
  }

  function render(list) {
    if (!list.length) {
      results.innerHTML = '<p class="empty-state">No equipment matches your filters.</p>';
    } else {
      results.innerHTML =
        '<table class="data-table"><thead><tr>' +
        '<th scope="col">Name</th><th scope="col">Type</th><th scope="col">Location</th>' +
        '<th scope="col">Available</th><th scope="col">Status</th>' +
        '</tr></thead><tbody>' + list.map(row).join('') + '</tbody></table>';
    }
    if (countEl) countEl.textContent = String(list.length);
  }

  async function update() {
    const params = new URLSearchParams();
    if (locationSel && locationSel.value) params.set('location', locationSel.value);
    if (typeSel && typeSel.value) params.set('type', typeSel.value);

    results.classList.add('is-updating');
    try {
      const res = await fetch('../api/api_equipment.php?' + params.toString());
      const data = await res.json();
      if (!res.ok || !data || !data.ok) throw new Error((data && data.error) || 'Request failed');
      render(data.equipment || []);
      // Keep the URL shareable / reload-safe (the PHP page reads the same params).
      const qs = params.toString();
      history.replaceState(null, '', qs ? 'equipment.php?' + qs : 'equipment.php');
    } catch (err) {
      // On failure, fall back to a normal navigation so the user still gets results.
      form.submit();
    } finally {
      results.classList.remove('is-updating');
    }
  }

  form.addEventListener('submit', (ev) => { ev.preventDefault(); update(); });
  if (locationSel) locationSel.addEventListener('change', update);
  if (typeSel) typeSel.addEventListener('change', update);
})();
