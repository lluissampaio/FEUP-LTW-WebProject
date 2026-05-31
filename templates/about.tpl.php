<?php declare(strict_types = 1); ?>

<?php function drawTwoCol(array $cfg) {
  $reverse = !empty($cfg['reverse']);
  $alt     = !empty($cfg['alt']);
  $classes = 'two-col' . ($reverse ? ' two-col-reverse' : '') . ($alt ? ' two-col-alt' : '');
?>
  <section class="<?= $classes ?>">
    <div class="two-col-inner">
      <?php if ($reverse) { ?>
        <div class="two-col-text">
          <?php drawTwoColCopy($cfg); ?>
        </div>
        <figure class="img-real-wrap two-col-image">
          <img class="img-real" src="../images/<?= htmlspecialchars($cfg['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($cfg['alt_text'], ENT_QUOTES, 'UTF-8') ?>">
        </figure>
      <?php } else { ?>
        <figure class="img-real-wrap two-col-image">
          <img class="img-real" src="../images/<?= htmlspecialchars($cfg['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($cfg['alt_text'], ENT_QUOTES, 'UTF-8') ?>">
        </figure>
        <div class="two-col-text">
          <?php drawTwoColCopy($cfg); ?>
        </div>
      <?php } ?>
    </div>
  </section>
<?php } ?>

<?php function drawTwoColCopy(array $cfg) { ?>
  <p class="section-label"><?= htmlspecialchars($cfg['label'], ENT_QUOTES, 'UTF-8') ?></p>
  <h2 class="two-col-heading"><?= $cfg['heading'] ?></h2>
  <?php foreach ($cfg['paragraphs'] as $p) { ?>
    <p class="two-col-body"><?= htmlspecialchars($p, ENT_QUOTES, 'UTF-8') ?></p>
  <?php } ?>
<?php } ?>

<?php function drawValues(array $values) { ?>
  <section class="values">
    <div class="section-inner">
      <p class="section-label">02 &mdash; What We Stand For</p>
      <h2 class="section-heading">Four Principles</h2>
      <ul class="values-grid">
        <?php foreach ($values as $i => $v) { ?>
          <li class="value-item">
            <p class="value-number"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></p>
            <h3 class="value-title"><?= htmlspecialchars($v['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="value-body"><?= htmlspecialchars($v['body'], ENT_QUOTES, 'UTF-8') ?></p>
          </li>
        <?php } ?>
      </ul>
    </div>
  </section>
<?php } ?>

<?php function drawTimeline(array $items) { ?>
  <section class="timeline">
    <div class="section-inner">
      <p class="section-label">03 &mdash; History</p>
      <h2 class="section-heading">Five Years, Four Cities.</h2>
      <ol class="timeline-list">
        <?php foreach ($items as $item) { ?>
          <li class="timeline-item">
            <span class="timeline-year"><?= htmlspecialchars($item['year'], ENT_QUOTES, 'UTF-8') ?></span>
            <div class="timeline-body">
              <h3><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h3>
              <p><?= htmlspecialchars($item['body'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
          </li>
        <?php } ?>
      </ol>
    </div>
  </section>
<?php } ?>
