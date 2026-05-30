<?php declare(strict_types = 1); ?>

<?php function drawPricing(array $plans, string $applyHref = '#apply') { ?>
  <section class="pricing">
    <div class="section-inner">
      <p class="section-label">01 &mdash; Plans</p>
      <h2 class="section-heading">Choose Your Plan</h2>
      <ul class="pricing-grid">
        <?php foreach ($plans as $p) {
          $featured  = !empty($p['featured']);
          $cardClass = 'pricing-card' . ($featured ? ' pricing-card-featured' : '');
          $btnClass  = $featured ? 'btn-primary' : 'btn-outline';
        ?>
        <li>
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
        </li>
        <?php } ?>
      </ul>
    </div>
  </section>
<?php } ?>

<?php
  /**
   * Logged-in member view: current subscription + actionable plan cards.
   * $displayPlans: array of plans with precomputed presentation properties
   * $current : ?Membership (current subscription, plan joined)
   * $features: [planName => string[]] feature bullets for display
   */
  function drawMembershipManager(array $displayPlans, ?Membership $current, array $features, string $csrf) {
    $hasActive   = $current && $current->isActive();
    $currentTier = $hasActive && $current->plan ? $current->plan->tier : 0;
?>
  <section class="section" style="padding-top: 0;">
    <div class="section-inner">
      <p class="section-label">01 &mdash; Your membership</p>

      <?php if ($hasActive && $current->plan) { ?>
        <div class="membership-current">
          <div>
            <h2 class="section-heading">You are on the <?= htmlspecialchars($current->plan->name, ENT_QUOTES, 'UTF-8') ?> plan.</h2>
            <p class="membership-current-meta">
              <?= htmlspecialchars($current->plan->priceLabel(), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($current->plan->period, ENT_QUOTES, 'UTF-8') ?>
              <?php if ($current->renewsAt) { ?>
                &middot; renews <?= htmlspecialchars(date('j M Y', strtotime($current->renewsAt)), ENT_QUOTES, 'UTF-8') ?>
              <?php } ?>
            </p>
          </div>
          <form method="post" action="../actions/action_cancel_membership.php" class="inline-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="btn-outline">Cancel membership</button>
          </form>
        </div>
      <?php } else { ?>
        <h2 class="section-heading">Choose your plan.</h2>
        <p class="membership-current-meta">You don't have an active membership yet.</p>
      <?php } ?>

      <ul class="pricing-grid">
        <?php foreach ($displayPlans as $dp) {
          $p = $dp['plan'];
        ?>
          <li>
            <article class="<?= $dp['cardClass'] ?>">
              <p class="pricing-plan-label"><?= htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8') ?></p>
              <h3 class="pricing-plan-name"><?= htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8') ?></h3>
              <p class="pricing-price"><?= htmlspecialchars($p->priceLabel(), ENT_QUOTES, 'UTF-8') ?></p>
              <p class="pricing-period"><?= htmlspecialchars($p->period, ENT_QUOTES, 'UTF-8') ?></p>
              <ul class="pricing-features">
                <?php foreach (($features[$p->name] ?? []) as $f) { ?>
                  <li><?= htmlspecialchars($f, ENT_QUOTES, 'UTF-8') ?></li>
                <?php } ?>
              </ul>
              <?php if ($dp['disabled']) { ?>
                <button type="button" class="<?= $dp['btnClass'] ?>" disabled><?= htmlspecialchars($dp['label'], ENT_QUOTES, 'UTF-8') ?></button>
              <?php } elseif (!empty($dp['isApply'])) { ?>
                <a href="contact.php?plan=<?= urlencode($p->name) ?>" class="<?= $dp['btnClass'] ?>"><?= htmlspecialchars($dp['label'], ENT_QUOTES, 'UTF-8') ?></a>
              <?php } else { ?>
                <form method="post" action="../actions/action_subscribe.php">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="plan_id" value="<?= (int)$p->id ?>">
                  <button type="submit" class="<?= $dp['btnClass'] ?>"><?= htmlspecialchars($dp['label'], ENT_QUOTES, 'UTF-8') ?></button>
                </form>
              <?php } ?>
            </article>
          </li>
        <?php } ?>
      </ul>
    </div>
  </section>
<?php } ?>

<?php function drawCompareTable(array $features, array $columns) { ?>
  <section class="compare-section">
    <div class="section-inner">
      <p class="section-label">02 &mdash; Compare</p>
      <h2 class="section-heading">All Features</h2>

      <div class="table-scroll">
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
    </div>
  </section>
<?php } ?>

<?php function drawFaq(array $faqs) { ?>
  <section class="faq-section">
    <div class="section-inner">
      <p class="section-label">03 &mdash; Common Questions</p>
      <h2 class="section-heading">Before You Apply</h2>
      <ul class="faq-list">
        <?php foreach ($faqs as $f) { ?>
          <li>
            <details class="faq-item">
              <summary class="faq-summary"><?= htmlspecialchars($f['q'], ENT_QUOTES, 'UTF-8') ?></summary>
              <div class="faq-body"><?= htmlspecialchars($f['a'], ENT_QUOTES, 'UTF-8') ?></div>
            </details>
          </li>
        <?php } ?>
      </ul>
    </div>
  </section>
<?php } ?>
