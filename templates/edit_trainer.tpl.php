<?php
  declare(strict_types = 1);

  function drawEditTrainerForm(Trainer $trainer, string $csrf) {
?>
  <section class="dashboard-activity">
    <h2 class="dashboard-section-heading">Public profile</h2>
    <form action="../actions/action_edit_trainer.php" method="post" class="dashboard-form">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

      <div class="form-field">
        <label for="specialty">Specialty</label>
        <input type="text" id="specialty" name="specialty"
               value="<?= htmlspecialchars($trainer->specialty ?? '', ENT_QUOTES, 'UTF-8') ?>"
               placeholder="e.g. Strength, Mobility, Pilates">
      </div>

      <div class="form-field">
        <label for="certifications">Certifications</label>
        <input type="text" id="certifications" name="certifications"
               value="<?= htmlspecialchars($trainer->certifications ?? '', ENT_QUOTES, 'UTF-8') ?>"
               placeholder="e.g. NASM-CPT, CSCS">
      </div>

      <div class="form-field">
        <label for="bio">Bio</label>
        <textarea id="bio" name="bio" rows="5"
                  placeholder="Tell members about yourself..."><?= htmlspecialchars($trainer->bio ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>

      <button type="submit" class="btn-primary">Save changes</button>
    </form>
  </section>
<?php
  }
?>
