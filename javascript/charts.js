'use strict';

/* Hand-rolled canvas charts for the member progress page. No charting library.
   Two chart types are driven entirely by data embedded in the page:
     - data-chart="workout-history"  -> bar OR line chart of recent workouts.
     - data-chart="goal-progress"    -> horizontal progress bars per goal.
   Each <canvas> reads its dataset from a sibling/related
   <script type="application/json"> element referenced by data-source="#id".
   Charts are responsive (sized to their container width) and redraw on resize. */
(function () {
  // Pull design-token colours from CSS custom properties so charts match the
  // site palette without us hard-coding hex values (falls back if missing).
  function cssVar(name, fallback) {
    var v = getComputedStyle(document.documentElement).getPropertyValue(name);
    v = (v || '').trim();
    return v || fallback;
  }

  var COLORS = {
    accent:  cssVar('--color-accent', '#c2410c'),
    accent2: cssVar('--color-accent-soft', '#f97316'),
    text:    cssVar('--color-text', '#1f2937'),
    muted:   cssVar('--color-text-muted', '#6b7280'),
    grid:    'rgba(0, 0, 0, 0.08)',
    track:   'rgba(0, 0, 0, 0.10)',
    achieved:'#16a34a'
  };

  function readData(canvas) {
    var sel = canvas.getAttribute('data-source');
    if (!sel) return null;
    var node = document.querySelector(sel);
    if (!node) return null;
    try {
      return JSON.parse(node.textContent || 'null');
    } catch (e) {
      return null;
    }
  }

  // Prepare a high-DPI backing store sized to the canvas's CSS box and return
  // the 2D context plus logical (CSS-pixel) width/height.
  function setup(canvas) {
    var rect = canvas.getBoundingClientRect();
    var cssW = Math.max(1, Math.floor(rect.width));
    var cssH = Math.max(1, Math.floor(canvas.clientHeight || rect.height || 220));
    var dpr  = window.devicePixelRatio || 1;
    canvas.width  = Math.floor(cssW * dpr);
    canvas.height = Math.floor(cssH * dpr);
    var ctx = canvas.getContext('2d');
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, cssW, cssH);
    return { ctx: ctx, w: cssW, h: cssH };
  }

  function drawEmpty(ctx, w, h, msg) {
    ctx.fillStyle = COLORS.muted;
    ctx.font = '14px system-ui, sans-serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(msg || 'No data to chart yet.', w / 2, h / 2);
  }

  // ---- Workout history (bars or line) -------------------------------------
  function drawWorkoutHistory(canvas) {
    var s = setup(canvas);
    var ctx = s.ctx, w = s.w, h = s.h;
    var data = readData(canvas);
    var points = (data && Array.isArray(data.points)) ? data.points : [];
    var type = (data && data.type) || 'bar';
    var valueLabel = (data && data.valueLabel) || 'value';

    if (!points.length) { drawEmpty(ctx, w, h); return; }

    var padL = 40, padR = 12, padT = 16, padB = 34;
    var plotW = Math.max(1, w - padL - padR);
    var plotH = Math.max(1, h - padT - padB);

    var max = 0;
    points.forEach(function (p) { if (+p.value > max) max = +p.value; });
    if (max <= 0) max = 1;
    // Round the axis maximum up to something tidy.
    var niceMax = niceCeil(max);

    // Axis + gridlines (4 horizontal lines).
    ctx.strokeStyle = COLORS.grid;
    ctx.fillStyle = COLORS.muted;
    ctx.lineWidth = 1;
    ctx.font = '11px system-ui, sans-serif';
    ctx.textAlign = 'right';
    ctx.textBaseline = 'middle';
    var steps = 4;
    for (var i = 0; i <= steps; i++) {
      var gy = padT + plotH - (plotH * i / steps);
      ctx.beginPath();
      ctx.moveTo(padL, gy);
      ctx.lineTo(w - padR, gy);
      ctx.stroke();
      ctx.fillText(String(Math.round(niceMax * i / steps)), padL - 6, gy);
    }

    var n = points.length;
    var slot = plotW / n;

    if (type === 'line') {
      // Line chart with filled dots.
      ctx.strokeStyle = COLORS.accent;
      ctx.lineWidth = 2;
      ctx.beginPath();
      points.forEach(function (p, idx) {
        var x = padL + slot * idx + slot / 2;
        var y = padT + plotH - (plotH * (+p.value / niceMax));
        if (idx === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
      });
      ctx.stroke();
      points.forEach(function (p, idx) {
        var x = padL + slot * idx + slot / 2;
        var y = padT + plotH - (plotH * (+p.value / niceMax));
        ctx.fillStyle = COLORS.accent;
        ctx.beginPath();
        ctx.arc(x, y, 3, 0, Math.PI * 2);
        ctx.fill();
      });
    } else {
      // Bar chart.
      var barW = Math.max(4, Math.min(46, slot * 0.6));
      points.forEach(function (p, idx) {
        var bh = plotH * (+p.value / niceMax);
        var x = padL + slot * idx + (slot - barW) / 2;
        var y = padT + plotH - bh;
        ctx.fillStyle = COLORS.accent;
        ctx.fillRect(x, y, barW, bh);
      });
    }

    // X labels (skip some when crowded to avoid overlap).
    ctx.fillStyle = COLORS.muted;
    ctx.font = '10px system-ui, sans-serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'top';
    var labelEvery = Math.ceil(n / Math.max(1, Math.floor(plotW / 56)));
    points.forEach(function (p, idx) {
      if (idx % labelEvery !== 0 && idx !== n - 1) return;
      var x = padL + slot * idx + slot / 2;
      ctx.fillText(String(p.label || ''), x, padT + plotH + 6);
    });

    // Accessible summary on the canvas element.
    canvas.setAttribute('role', 'img');
    canvas.setAttribute('aria-label',
      'Workout history chart of ' + valueLabel + ' across ' + n + ' recent sessions.');
  }

  // ---- Goal progress (horizontal bars) ------------------------------------
  function drawGoalProgress(canvas) {
    var s = setup(canvas);
    var ctx = s.ctx, w = s.w, h = s.h;
    var data = readData(canvas);
    var goals = (data && Array.isArray(data.goals)) ? data.goals : [];

    if (!goals.length) { drawEmpty(ctx, w, h, 'No goals to chart yet.'); return; }

    var padX = 12, padT = 8;
    var rowGap = 10;
    var rowH = Math.max(24, Math.min(40, (h - padT * 2 - rowGap * (goals.length - 1)) / goals.length));
    var labelH = 16;
    var trackH = rowH - labelH;
    var trackW = w - padX * 2;

    ctx.textBaseline = 'alphabetic';
    goals.forEach(function (g, idx) {
      var top = padT + idx * (rowH + rowGap);
      var pct = Math.max(0, Math.min(1, +g.progress || 0));
      var done = g.status === 'achieved' || pct >= 1;

      // Label line.
      ctx.fillStyle = COLORS.text;
      ctx.font = '12px system-ui, sans-serif';
      ctx.textAlign = 'left';
      ctx.fillText(String(g.title || 'Goal'), padX, top + 12);
      ctx.fillStyle = COLORS.muted;
      ctx.textAlign = 'right';
      ctx.fillText(Math.round(pct * 100) + '%', padX + trackW, top + 12);

      // Track.
      var ty = top + labelH;
      roundRect(ctx, padX, ty, trackW, trackH, trackH / 2);
      ctx.fillStyle = COLORS.track;
      ctx.fill();

      // Fill.
      var fw = Math.max(0, trackW * pct);
      if (fw > 0) {
        roundRect(ctx, padX, ty, fw, trackH, trackH / 2);
        ctx.fillStyle = done ? COLORS.achieved : COLORS.accent;
        ctx.fill();
      }
    });

    canvas.setAttribute('role', 'img');
    canvas.setAttribute('aria-label', 'Progress chart for ' + goals.length + ' fitness goals.');
  }

  function roundRect(ctx, x, y, width, height, r) {
    r = Math.min(r, width / 2, height / 2);
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.arcTo(x + width, y, x + width, y + height, r);
    ctx.arcTo(x + width, y + height, x, y + height, r);
    ctx.arcTo(x, y + height, x, y, r);
    ctx.arcTo(x, y, x + width, y, r);
    ctx.closePath();
  }

  function niceCeil(v) {
    if (v <= 0) return 1;
    var pow = Math.pow(10, Math.floor(Math.log10(v)));
    var n = v / pow;
    var step = n <= 1 ? 1 : n <= 2 ? 2 : n <= 5 ? 5 : 10;
    return step * pow;
  }

  function renderOne(canvas) {
    var kind = canvas.getAttribute('data-chart');
    if (kind === 'workout-history') drawWorkoutHistory(canvas);
    else if (kind === 'goal-progress') drawGoalProgress(canvas);
  }

  function renderAll() {
    var canvases = document.querySelectorAll('canvas[data-chart]');
    for (var i = 0; i < canvases.length; i++) renderOne(canvases[i]);
  }

  function init() {
    if (!document.querySelector('canvas[data-chart]')) return;
    renderAll();
    // Debounced redraw on resize so charts stay sharp / responsive.
    var t;
    window.addEventListener('resize', function () {
      clearTimeout(t);
      t = setTimeout(renderAll, 120);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
