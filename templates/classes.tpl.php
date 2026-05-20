<?php declare(strict_types = 1); ?>

<?php function drawClassList(array $classes) { ?>
  <section class="class-list-section">
    <div class="section-inner">
      <p class="section-label">01 &mdash; Programmes</p>
      <h2 class="section-heading">Our Classes</h2>
      <div class="class-list">
        <?php foreach ($classes as $i => $c) { ?>
          <div class="class-row">
            <span class="class-row-num"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
            <div>
              <p class="class-row-name"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></p>
              <p class="class-row-desc"><?= htmlspecialchars($c['desc'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <p class="class-row-meta"><?= htmlspecialchars($c['duration'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="class-row-meta"><?= htmlspecialchars($c['level'], ENT_QUOTES, 'UTF-8') ?></p>
            <a href="#schedule" class="class-row-link">Book &rarr;</a>
          </div>
        <?php } ?>
      </div>
    </div>
  </section>
<?php } ?>

<?php function drawSchedule(array $tabs, array $days) { ?>
  <section class="schedule-section" id="schedule">
    <div class="section-inner">
      <p class="section-label">02 &mdash; This Week</p>
      <h2 class="section-heading">Schedule</h2>

      <div class="schedule-tabs" role="tablist">
        <?php foreach ($tabs as $i => $tab) {
          $active = $i === 0 ? ' is-active' : '';
        ?>
          <span class="schedule-tab<?= $active ?>" role="tab"><?= htmlspecialchars($tab, ENT_QUOTES, 'UTF-8') ?></span>
        <?php } ?>
      </div>

      <div class="schedule-grid">
        <?php foreach ($days as $day) { ?>
          <div class="schedule-day">
            <p class="schedule-day-label"><?= htmlspecialchars($day['label'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php foreach ($day['slots'] as $slot) { ?>
              <div class="schedule-slot">
                <p class="schedule-time"><?= htmlspecialchars($slot['time'], ENT_QUOTES, 'UTF-8') ?></p>
                <p class="schedule-class"><?= htmlspecialchars($slot['class'], ENT_QUOTES, 'UTF-8') ?></p>
              </div>
            <?php } ?>
          </div>
        <?php } ?>
      </div>
    </div>
  </section>
<?php } ?>
