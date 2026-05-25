<?php declare(strict_types = 1); ?>

<?php function drawPricing(array $plans, string $applyHref = '#apply') { ?>
  <section class="pricing">
    <div class="section-inner">
      <p class="section-label">01 &mdash; Plans</p>
      <h2 class="section-heading">Choose Your Plan</h2>
      <div class="pricing-grid">
        <?php foreach ($plans as $p) {
          $featured  = !empty($p['featured']);
          $cardClass = 'pricing-card' . ($featured ? ' pricing-card--featured' : '');
          $btnClass  = $featured ? 'btn-primary' : 'btn-outline';
        ?>
          <article class="<?= $cardClass ?>">
            <p class="pricing-plan-label"><?= htmlspecialchars($p['label'], ENT_QUOTES, 'UTF-8') ?></p>
            <h3 class="pricing-plan-name"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="pricing-price"><?= htmlspecialchars($p['price'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="pricing-period"><?= htmlspecialchars($p['period'], ENT_QUOTES, 'UTF-8') ?></p>
            <ul class="pricing-features">
              <?php foreach ($p['features'] as $f) { ?>
                <li><?= htmlspecialchars($f, ENT_QUOTES, 'UTF-8') ?></li>
              <?php } ?>
            </ul>
            <a href="<?= htmlspecialchars($applyHref, ENT_QUOTES, 'UTF-8') ?>" class="<?= $btnClass ?>">Apply Now</a>
          </article>
        <?php } ?>
      </div>
    </div>
  </section>
<?php } ?>

<?php function drawCompareTable(array $features, array $columns) { ?>
  <section class="compare-section">
    <div class="section-inner">
      <p class="section-label">02 &mdash; Compare</p>
      <h2 class="section-heading">All Features</h2>

      <table class="compare-table">
        <thead>
          <tr>
            <th>Feature</th>
            <?php foreach ($columns as $col) {
              $cls = !empty($col['featured']) ? ' class="is-featured"' : '';
            ?>
              <th<?= $cls ?>><?= htmlspecialchars($col['name'], ENT_QUOTES, 'UTF-8') ?></th>
            <?php } ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($features as $row) { ?>
            <tr>
              <td><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
              <?php foreach ($row['values'] as $v) { ?>
                <td>
                  <?php if ($v === true) { ?>
                    <span class="compare-check">&check;</span>
                  <?php } elseif ($v === false) { ?>
                    <span class="compare-dash">&mdash;</span>
                  <?php } else { ?>
                    <?= htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8') ?>
                  <?php } ?>
                </td>
              <?php } ?>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </section>
<?php } ?>

<?php function drawFaq(array $faqs) { ?>
  <section class="faq-section">
    <div class="section-inner">
      <p class="section-label">03 &mdash; Common Questions</p>
      <h2 class="section-heading">Before You Apply</h2>
      <div class="faq-list">
        <?php foreach ($faqs as $f) { ?>
          <details class="faq-item">
            <summary class="faq-summary"><?= htmlspecialchars($f['q'], ENT_QUOTES, 'UTF-8') ?></summary>
            <div class="faq-body"><?= htmlspecialchars($f['a'], ENT_QUOTES, 'UTF-8') ?></div>
          </details>
        <?php } ?>
      </div>
    </div>
  </section>
<?php } ?>
