<?php declare(strict_types = 1); ?>

<?php function drawHomeHero() {
  $heroPhotos = [
    'location-new-york.jpg',
    'location-lisbon.jpg',
    'location-porto.jpg',
    'location-viseu.jpg',
  ];
  // Duplicate the set so the horizontal scroll loops seamlessly.
  $heroSlides = array_merge($heroPhotos, $heroPhotos);
?>
  <section class="hero" id="home" aria-labelledby="hero__heading">
    <div class="hero__carousel" aria-hidden="true">
      <ul class="hero__carousel-track">
        <?php foreach ($heroSlides as $file) { ?>
          <li class="hero__slide" style="background-image:url('../images/<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>')"></li>
        <?php } ?>
      </ul>
    </div>
    <div class="hero__inner">
      <p class="hero__eyebrow">New York &middot; Lisbon &middot; Porto &middot; Viseu</p>
      <h1 class="hero__heading" id="hero__heading">The Haven<br>You Deserve.</h1>
      <p class="hero__sub">Elite training. Uncompromised privacy. A private membership for those who demand discretion and excellence.</p>
      <a href="membership.php" class="btn--primary hero__cta">REQUEST MEMBERSHIP</a>
      <ul class="hero__locations" aria-label="Locations">
        <li class="location-badge">New York</li>
        <li class="location-badge">Lisbon</li>
        <li class="location-badge">Porto</li>
        <li class="location-badge">Viseu</li>
      </ul>
    </div>
  </section>
<?php } ?>

<?php function drawHomeStats(array $stats) { ?>
  <section class="stats" aria-label="Key numbers">
    <div class="section__inner">
      <ul class="stats__grid">
        <?php foreach ($stats as $stat) { ?>
          <li class="stat-block">
            <p class="stat-number"><?= htmlspecialchars($stat['number'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="stat-label"><?= htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8') ?></p>
          </li>
        <?php } ?>
      </ul>
    </div>
  </section>
<?php } ?>

<?php function drawHomeServices(array $services) { ?>
  <section class="services" id="services" aria-labelledby="services-heading">
    <div class="section__inner">
      <p class="section__label">02 &mdash; Facilities</p>
      <h2 class="section__heading" id="services-heading">World-Class Facilities</h2>
      <ul class="services__grid">
        <?php foreach ($services as $i => $s) { ?>
          <li>
            <article class="service-card">
              <p class="service-number"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></p>
              <h3 class="service-title"><?= htmlspecialchars($s['title'], ENT_QUOTES, 'UTF-8') ?></h3>
              <p class="service-desc"><?= htmlspecialchars($s['desc'], ENT_QUOTES, 'UTF-8') ?></p>
              <a href="<?= htmlspecialchars($s['link'], ENT_QUOTES, 'UTF-8') ?>" class="service-link">Explore &rarr;</a>
            </article>
          </li>
        <?php } ?>
      </ul>
    </div>
  </section>
<?php } ?>

<?php function drawHomeTestimonials(array $testimonials) { ?>
  <section class="testimonials" aria-labelledby="testimonials-heading">
    <div class="section__inner">
      <p class="section__label">03 &mdash; Members</p>
      <h2 class="section__heading" id="testimonials-heading">What Our Members Say</h2>
      <ul class="testimonials__grid">
        <?php foreach ($testimonials as $t) { ?>
          <li>
            <figure class="testimonial-block">
              <blockquote><?= htmlspecialchars($t['quote'], ENT_QUOTES, 'UTF-8') ?></blockquote>
              <figcaption class="testimonial-author"><?= htmlspecialchars($t['author'], ENT_QUOTES, 'UTF-8') ?></figcaption>
            </figure>
          </li>
        <?php } ?>
      </ul>
    </div>
  </section>
<?php } ?>

<?php function drawHomeFeatured(array $classes, array $trainers) {
  // Promotional features: only render the section if something is actually featured.
  if (count($classes) === 0 && count($trainers) === 0) return;
?>
  <section class="featured" id="featured" aria-labelledby="featured-heading">
    <div class="section__inner">
      <p class="section__label">05 &mdash; Highlights</p>
      <h2 class="section__heading" id="featured-heading">Featured at Iron Haven</h2>

      <?php if (count($classes) > 0) { ?>
        <h3 class="featured__subheading">Featured Classes</h3>
        <ul class="featured__grid">
          <?php foreach ($classes as $c) { ?>
            <li>
              <article class="promo-card">
                <p class="promo-card__meta"><?= htmlspecialchars($c->type, ENT_QUOTES, 'UTF-8') ?></p>
                <h4 class="promo-card__title"><?= htmlspecialchars($c->name, ENT_QUOTES, 'UTF-8') ?></h4>
                <?php if ($c->trainerName) { ?>
                  <p class="promo-card__sub">with <?= htmlspecialchars($c->trainerName, ENT_QUOTES, 'UTF-8') ?></p>
                <?php } ?>
                <?php if ($c->description) { ?>
                  <p class="promo-card__desc"><?= htmlspecialchars($c->description, ENT_QUOTES, 'UTF-8') ?></p>
                <?php } ?>
                <a href="class.php?id=<?= (int)$c->id ?>" class="service-link">View class &rarr;</a>
              </article>
            </li>
          <?php } ?>
        </ul>
      <?php } ?>

      <?php if (count($trainers) > 0) { ?>
        <h3 class="featured__subheading">Featured Trainers</h3>
        <ul class="featured__grid">
          <?php foreach ($trainers as $t) { ?>
            <li>
              <article class="promo-card">
                <?php if ($t->photo) { ?>
                  <div class="img-real__wrap promo-card__photo">
                    <img class="img-real" src="../images/<?= htmlspecialchars($t->photo, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t->name(), ENT_QUOTES, 'UTF-8') ?>">
                  </div>
                <?php } ?>
                <?php if ($t->specialty) { ?>
                  <p class="promo-card__meta"><?= htmlspecialchars($t->specialty, ENT_QUOTES, 'UTF-8') ?></p>
                <?php } ?>
                <h4 class="promo-card__title"><?= htmlspecialchars($t->name(), ENT_QUOTES, 'UTF-8') ?></h4>
                <?php if ($t->bio) { ?>
                  <p class="promo-card__desc"><?= htmlspecialchars($t->bio, ENT_QUOTES, 'UTF-8') ?></p>
                <?php } ?>
                <a href="trainer.php?id=<?= (int)$t->id ?>" class="service-link">View profile &rarr;</a>
              </article>
            </li>
          <?php } ?>
        </ul>
      <?php } ?>
    </div>
  </section>
<?php } ?>

<?php function drawHomePricing(array $plans) { ?>
  <section class="pricing" id="pricing" aria-labelledby="pricing-heading">
    <div class="section__inner">
      <p class="section__label">04 &mdash; Membership</p>
      <h2 class="section__heading" id="pricing-heading">Choose Your Plan</h2>
      <ul class="pricing__grid">
        <?php foreach ($plans as $p) {
          $featured  = !empty($p['featured']);
          $cardClass = 'pricing__card' . ($featured ? ' pricing__card--featured' : '');
          $btnClass  = $featured ? 'btn--primary' : 'btn--outline';
        ?>
          <li>
            <article class="<?= $cardClass ?>">
              <p class="pricing__plan-label"><?= htmlspecialchars($p['label'], ENT_QUOTES, 'UTF-8') ?></p>
              <h3 class="pricing__plan-name"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></h3>
              <p class="pricing__price"><?= htmlspecialchars($p['price'], ENT_QUOTES, 'UTF-8') ?></p>
              <p class="pricing__period"><?= htmlspecialchars($p['period'], ENT_QUOTES, 'UTF-8') ?></p>
              <ul class="pricing__features">
                <?php foreach ($p['features'] as $f) { ?>
                  <li><?= htmlspecialchars($f, ENT_QUOTES, 'UTF-8') ?></li>
                <?php } ?>
              </ul>
              <a href="membership.php" class="<?= $btnClass ?>">Apply Now</a>
            </article>
          </li>
        <?php } ?>
      </ul>
    </div>
  </section>
<?php } ?>
