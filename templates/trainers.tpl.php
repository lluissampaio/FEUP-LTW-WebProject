<?php declare(strict_types = 1); ?>

<?php function drawTrainerSection(string $number, string $label, string $heading, array $trainers, bool $altBg = false) { ?>
  <section class="trainers-section"<?= $altBg ? ' style="background-color: var(--color-bg-alt);"' : '' ?>>
    <div class="section-inner">
      <p class="section-label"><?= htmlspecialchars($number, ENT_QUOTES, 'UTF-8') ?> &mdash; <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></p>
      <h2 class="section-heading"><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h2>

      <div class="trainer-grid">
        <?php foreach ($trainers as $t) { ?>
          <article class="trainer-card">
            <div class="img-real-wrap trainer-photo">
              <img class="img-real" src="../images/<?= htmlspecialchars($t['photo'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <h3 class="trainer-name"><?= htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="trainer-role"><?= htmlspecialchars($t['role'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="trainer-bio"><?= htmlspecialchars($t['bio'], ENT_QUOTES, 'UTF-8') ?></p>
            <div class="trainer-specialties">
              <?php foreach ($t['tags'] as $tag) { ?>
                <span class="trainer-tag"><?= htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') ?></span>
              <?php } ?>
            </div>
          </article>
        <?php } ?>
      </div>
    </div>
  </section>
<?php } ?>
