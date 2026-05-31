<?php declare(strict_types = 1); ?>

<?php function drawTrainerGrid(array $trainers) {
    // Distinct, sorted specialties for the filter dropdown.
    $specialties = [];
    foreach ($trainers as $t) {
      if ($t->specialty) $specialties[$t->specialty] = true;
    }
    $specialties = array_keys($specialties);
    sort($specialties);
?>
  <section class="trainers-section">
    <div class="section__inner">
      <p class="section__label">01 &mdash; Coaches</p>
      <h2 class="section__heading">Our Trainers</h2>

      <div class="trainer-search" id="trainer-search">
        <label class="filter-field">
          Search
          <input type="search" id="trainer-q" placeholder="Search by name&hellip;" autocomplete="off">
        </label>
        <label class="filter-field">
          Specialty
          <select id="trainer-spec">
            <option value="">All specialties</option>
            <?php foreach ($specialties as $s) { ?>
              <option value="<?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?></option>
            <?php } ?>
          </select>
        </label>
      </div>

      <div class="trainer-grid">
        <?php foreach ($trainers as $t) { ?>
          <article class="trainer-card"
                   data-name="<?= htmlspecialchars(strtolower($t->name()), ENT_QUOTES, 'UTF-8') ?>"
                   data-specialty="<?= htmlspecialchars($t->specialty ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <a href="trainer.php?id=<?= (int)$t->id ?>" class="img-real__wrap trainer-photo">
              <?php if ($t->photo) { ?>
                <img class="img-real" src="../images/<?= htmlspecialchars($t->photo, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t->name(), ENT_QUOTES, 'UTF-8') ?>">
              <?php } ?>
            </a>
            <h3 class="trainer-name"><a href="trainer.php?id=<?= (int)$t->id ?>"><?= htmlspecialchars($t->name(), ENT_QUOTES, 'UTF-8') ?></a></h3>
            <?php if ($t->specialty) { ?>
              <p class="trainer-role"><?= htmlspecialchars($t->specialty, ENT_QUOTES, 'UTF-8') ?></p>
            <?php } ?>
            <?php if ($t->bio) { ?>
              <p class="trainer-bio"><?= htmlspecialchars($t->bio, ENT_QUOTES, 'UTF-8') ?></p>
            <?php } ?>
            <?php if ($t->certifications) { ?>
              <div class="trainer-specialties">
                <?php foreach (preg_split('/,\s*/', $t->certifications) as $tag) { ?>
                  <span class="trainer-tag"><?= htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') ?></span>
                <?php } ?>
              </div>
            <?php } ?>
          </article>
        <?php } ?>
      </div>

      <p class="empty-state" id="trainer-empty" hidden>No trainers match your search.</p>
    </div>
  </section>

  <script src="../javascript/trainer-search.js" defer></script>
<?php } ?>

<?php function drawTrainerProfile(Trainer $t, array $classes) { ?>
  <section class="trainer-profile__section">
    <div class="section__inner trainer-profile">
      <div class="img-real__wrap trainer-profile__photo">
        <?php if ($t->photo) { ?>
          <img class="img-real" src="../images/<?= htmlspecialchars($t->photo, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t->name(), ENT_QUOTES, 'UTF-8') ?>">
        <?php } ?>
      </div>
      <div class="trainer-profile__body">
        <?php if ($t->specialty) { ?>
          <p class="hero__eyebrow"><?= htmlspecialchars($t->specialty, ENT_QUOTES, 'UTF-8') ?></p>
        <?php } ?>
        <h1 class="page-hero__heading"><?= htmlspecialchars($t->name(), ENT_QUOTES, 'UTF-8') ?></h1>
        <?php if ($t->bio) { ?>
          <p class="trainer-bio"><?= htmlspecialchars($t->bio, ENT_QUOTES, 'UTF-8') ?></p>
        <?php } ?>
        <?php if ($t->certifications) { ?>
          <div class="trainer-specialties">
            <?php foreach (preg_split('/,\s*/', $t->certifications) as $tag) { ?>
              <span class="trainer-tag"><?= htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') ?></span>
            <?php } ?>
          </div>
        <?php } ?>
      </div>
    </div>
  </section>

  <section class="trainer-classes-section">
    <div class="section__inner">
      <p class="section__label">01 &mdash; Programmes</p>
      <h2 class="section__heading">Classes taught</h2>
      <?php if (count($classes) === 0) { ?>
        <p class="empty-state">No classes assigned.</p>
      <?php } else { ?>
        <ul class="schedule-cards">
          <?php foreach ($classes as $c) { ?>
            <li class="schedule-card">
              <p class="schedule-card__meta"><?= htmlspecialchars($c->type, ENT_QUOTES, 'UTF-8') ?></p>
              <h3 class="schedule-card__title"><a href="class.php?id=<?= (int)$c->id ?>"><?= htmlspecialchars($c->name, ENT_QUOTES, 'UTF-8') ?></a></h3>
              <?php if ($c->description) { ?>
                <p class="schedule-card__coach"><?= htmlspecialchars($c->description, ENT_QUOTES, 'UTF-8') ?></p>
              <?php } ?>
              <p class="schedule-card__coach"><?= htmlspecialchars($c->locationName ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            </li>
          <?php } ?>
        </ul>
      <?php } ?>
    </div>
  </section>
<?php } ?>
