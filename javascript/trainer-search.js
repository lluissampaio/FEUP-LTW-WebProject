'use strict';

/* Instant client-side filtering of the trainer directory by name + specialty.
   The full grid is already server-rendered; this just shows/hides cards, so it
   works offline-fast and degrades to the complete list with JS off. */
(function () {
  const controls = document.getElementById('trainer-search');
  const grid = document.querySelector('.trainer-grid');
  const emptyEl = document.getElementById('trainer-empty');
  if (!controls || !grid) return;

  const qInput = document.getElementById('trainer-q');
  const specSel = document.getElementById('trainer-spec');
  const cards = Array.from(grid.querySelectorAll('.trainer-card'));

  function apply() {
    const q = (qInput.value || '').trim().toLowerCase();
    const spec = specSel.value || '';
    let visible = 0;

    cards.forEach((card) => {
      const matchName = !q || (card.dataset.name || '').includes(q);
      const matchSpec = !spec || card.dataset.specialty === spec;
      const show = matchName && matchSpec;
      card.hidden = !show;
      if (show) visible++;
    });

    if (emptyEl) emptyEl.hidden = visible !== 0;
  }

  // Debounce keystrokes so we filter at most ~every 150ms.
  let timer = null;
  function debounced() {
    clearTimeout(timer);
    timer = setTimeout(apply, 150);
  }

  qInput.addEventListener('input', debounced);
  specSel.addEventListener('change', apply);
})();
