<?php
  declare(strict_types = 1);

  function drawEditTrainerForm(Trainer $trainer, string $csrf) {
?>
  <section class="dashboard__activity">
    <h2 class="dashboard__section-heading">Public profile</h2>
    <form action="../actions/action_edit_trainer.php" method="post" class="dashboard__form" data-validate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

      <label class="form-field">
        Specialty
        <input type="text" id="specialty" name="specialty"
               value="<?= htmlspecialchars($trainer->specialty ?? '', ENT_QUOTES, 'UTF-8') ?>"
               placeholder="e.g. Strength, Mobility, Pilates">
      </label>

      <label class="form-field">
        Certifications
        <input type="text" id="certifications" name="certifications"
               value="<?= htmlspecialchars($trainer->certifications ?? '', ENT_QUOTES, 'UTF-8') ?>"
               placeholder="e.g. NASM-CPT, CSCS">
      </label>

      <label class="form-field">
        Bio
        <textarea id="bio" name="bio" rows="5" maxlength="600" data-counter="600"
                  placeholder="Tell members about yourself..."><?= htmlspecialchars($trainer->bio ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
      </label>

      <button type="submit" class="btn--primary">Save changes</button>
    </form>
  </section>

  <script src="../javascript/form-validate.js" defer></script>
<?php
  }
?>
