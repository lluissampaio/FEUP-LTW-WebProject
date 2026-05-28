<?php
  declare(strict_types = 1);

  function drawEditTrainerForm(Trainer $trainer, string $csrf) {
?>
  <section class="dashboard-activity">
    <form action="../actions/action_edit_trainer.php" method="post" class="auth-form" style="max-width: 600px;">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

      <div class="form-group">
        <label class="form-label" for="specialty">Specialty</label>
        <input class="form-input" type="text" id="specialty" name="specialty" 
               value="<?= htmlspecialchars($trainer->specialty ?? '', ENT_QUOTES, 'UTF-8') ?>" 
               placeholder="e.g. Strength, Mobility, Pilates">
      </div>

      <div class="form-group">
        <label class="form-label" for="certifications">Certifications</label>
        <input class="form-input" type="text" id="certifications" name="certifications" 
               value="<?= htmlspecialchars($trainer->certifications ?? '', ENT_QUOTES, 'UTF-8') ?>" 
               placeholder="e.g. NASM-CPT, CSCS">
      </div>

      <div class="form-group">
        <label class="form-label" for="bio">Bio</label>
        <textarea class="form-input" id="bio" name="bio" rows="5" 
                  placeholder="Tell us about yourself..."><?= htmlspecialchars($trainer->bio ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>

      <button type="submit" class="btn auth-btn">Save Changes</button>
    </form>
  </section>
<?php
  }
?>
