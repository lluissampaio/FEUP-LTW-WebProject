<?php declare(strict_types = 1); ?>

<?php function drawHomeHero() { ?>
  <section class="hero" id="home" aria-labelledby="hero-heading">
    <div class="hero-inner">
      <p class="hero-eyebrow">New York &middot; Lisbon &middot; Porto &middot; Viseu</p>
      <h1 class="hero-heading" id="hero-heading">The Haven<br>You Deserve.</h1>
      <p class="hero-sub">Elite training. Uncompromised privacy. A private membership for those who demand discretion and excellence.</p>
      <a href="membership.php" class="btn-primary hero-cta">REQUEST MEMBERSHIP</a>
      <div class="hero-locations" aria-label="Locations">
        <span class="location-badge">New York</span>
        <span class="location-badge">Lisbon</span>
        <span class="location-badge">Porto</span>
        <span class="location-badge">Viseu</span>
      </div>
    </div>
  </section>
<?php } ?>

<?php function drawHomeStats(array $stats) { ?>
  <section class="stats" aria-label="Key numbers">
    <div class="section-inner">
      <div class="stats-grid">
        <?php foreach ($stats as $stat) { ?>
          <div class="stat-block">
            <p class="stat-number"><?= htmlspecialchars($stat['number'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="stat-label"><?= htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        <?php } ?>
      </div>
    </div>
  </section>
<?php } ?>

<?php function drawHomeServices(array $services) { ?>
  <section class="services" id="services" aria-labelledby="services-heading">
    <div class="section-inner">
      <p class="section-label">02 &mdash; Facilities</p>
      <h2 class="section-heading" id="services-heading">World-Class Facilities</h2>
      <div class="services-grid">
        <?php foreach ($services as $i => $s) { ?>
          <article class="service-card">
            <p class="service-number"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></p>
            <h3 class="service-title"><?= htmlspecialchars($s['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="service-desc"><?= htmlspecialchars($s['desc'], ENT_QUOTES, 'UTF-8') ?></p>
            <a href="<?= htmlspecialchars($s['link'], ENT_QUOTES, 'UTF-8') ?>" class="service-link">Explore &rarr;</a>
          </article>
        <?php } ?>
      </div>
    </div>
  </section>
<?php } ?>

<?php function drawHomeTestimonials(array $testimonials) { ?>
  <section class="testimonials" aria-labelledby="testimonials-heading">
    <div class="section-inner">
      <p class="section-label">03 &mdash; Members</p>
      <h2 class="section-heading" id="testimonials-heading">What Our Members Say</h2>
      <div class="testimonials-grid">
        <?php foreach ($testimonials as $t) { ?>
          <figure class="testimonial-block">
            <blockquote><?= htmlspecialchars($t['quote'], ENT_QUOTES, 'UTF-8') ?></blockquote>
            <figcaption class="testimonial-author"><?= htmlspecialchars($t['author'], ENT_QUOTES, 'UTF-8') ?></figcaption>
          </figure>
        <?php } ?>
      </div>
    </div>
  </section>
<?php } ?>

<?php function drawHomePricing(array $plans) { ?>
  <section class="pricing" id="pricing" aria-labelledby="pricing-heading">
    <div class="section-inner">
      <p class="section-label">04 &mdash; Membership</p>
      <h2 class="section-heading" id="pricing-heading">Choose Your Plan</h2>
      <div class="pricing-grid">
        <?php foreach ($plans as $p) {
          $featured  = !empty($p['featured']);
          $cardClass = 'pricing-card' . ($featured ? ' pricing-card-featured' : '');
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
            <a href="membership.php" class="<?= $btnClass ?>">Apply Now</a>
          </article>
        <?php } ?>
      </div>
    </div>
  </section>
<?php } ?>
