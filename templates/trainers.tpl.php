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
    <div class="section-inner">
      <p class="section-label">01 &mdash; Coaches</p>
      <h2 class="section-heading">Our Trainers</h2>

      <div class="trainer-search" id="trainer-search">
        <div class="filter-field">
          <label for="trainer-q">Search</label>
          <input type="search" id="trainer-q" placeholder="Search by name&hellip;" autocomplete="off">
        </div>
        <div class="filter-field">
          <label for="trainer-spec">Specialty</label>
          <select id="trainer-spec">
            <option value="">All specialties</option>
            <?php foreach ($specialties as $s) { ?>
              <option value="<?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?></option>
            <?php } ?>
          </select>
        </div>
      </div>

      <div class="trainer-grid">
        <?php foreach ($trainers as $t) { ?>
          <article class="trainer-card"
                   data-name="<?= htmlspecialchars(strtolower($t->name()), ENT_QUOTES, 'UTF-8') ?>"
                   data-specialty="<?= htmlspecialchars($t->specialty ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <a href="trainer.php?id=<?= (int)$t->id ?>" class="img-real-wrap trainer-photo">
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
  <section class="trainer-profile-section">
    <div class="section-inner trainer-profile">
      <div class="img-real-wrap trainer-profile-photo">
        <?php if ($t->photo) { ?>
          <img class="img-real" src="../images/<?= htmlspecialchars($t->photo, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t->name(), ENT_QUOTES, 'UTF-8') ?>">
        <?php } ?>
      </div>
      <div class="trainer-profile-body">
        <?php if ($t->specialty) { ?>
          <p class="hero-eyebrow"><?= htmlspecialchars($t->specialty, ENT_QUOTES, 'UTF-8') ?></p>
        <?php } ?>
        <h1 class="page-hero-heading"><?= htmlspecialchars($t->name(), ENT_QUOTES, 'UTF-8') ?></h1>
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
    <div class="section-inner">
      <p class="section-label">01 &mdash; Programmes</p>
      <h2 class="section-heading">Classes taught</h2>
      <?php if (count($classes) === 0) { ?>
        <p class="empty-state">No classes assigned.</p>
      <?php } else { ?>
        <ul class="schedule-cards">
          <?php foreach ($classes as $c) { ?>
            <li class="schedule-card">
              <p class="schedule-card-meta"><?= htmlspecialchars($c->type, ENT_QUOTES, 'UTF-8') ?></p>
              <h3 class="schedule-card-title"><a href="class.php?id=<?= (int)$c->id ?>"><?= htmlspecialchars($c->name, ENT_QUOTES, 'UTF-8') ?></a></h3>
              <?php if ($c->description) { ?>
                <p class="schedule-card-coach"><?= htmlspecialchars($c->description, ENT_QUOTES, 'UTF-8') ?></p>
              <?php } ?>
              <p class="schedule-card-coach"><?= htmlspecialchars($c->locationName ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            </li>
          <?php } ?>
        </ul>
      <?php } ?>
    </div>
  </section>
<?php } ?>
