<?php
  declare(strict_types = 1);

  function drawAdminPromotions(array $classes, array $trainers, string $csrf) {
?>
  <section class="admin-section">
    <div class="section__inner">
      <h2 class="section__heading">Classes (<?= count($classes) ?>)</h2>
      <?php if (count($classes) === 0) { ?>
        <p class="empty-state">No classes yet.</p>
      <?php } else { ?>
        <div class="table-scroll">
        <table class="data-table">
          <thead><tr><th>Name</th><th>Type</th><th>Trainer</th><th>Location</th><th>Featured</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($classes as $c) { ?>
              <tr>
                <td><?= htmlspecialchars($c->name, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($c->type, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($c->trainerName ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($c->locationName ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?php if ($c->featured) { ?>
                    <span class="promo-status promo-status--on">Featured</span>
                  <?php } else { ?>
                    <span class="promo-status promo-status--off">Not featured</span>
                  <?php } ?>
                </td>
                <td class="row-actions">
                  <form method="post" action="../actions/action_admin_promo.php" class="inline-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="entity_type" value="class">
                    <input type="hidden" name="entity_id" value="<?= (int)$c->id ?>">
                    <input type="hidden" name="featured" value="<?= $c->featured ? '0' : '1' ?>">
                    <button type="submit" class="<?= $c->featured ? 'btn--outline' : 'btn--primary' ?> btn--sm">
                      <?= $c->featured ? 'Unfeature' : 'Feature' ?>
                    </button>
                  </form>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
        </div>
      <?php } ?>
    </div>
  </section>

  <section class="admin-section">
    <div class="section__inner">
      <h2 class="section__heading">Trainers (<?= count($trainers) ?>)</h2>
      <?php if (count($trainers) === 0) { ?>
        <p class="empty-state">No trainers yet.</p>
      <?php } else { ?>
        <div class="table-scroll">
        <table class="data-table">
          <thead><tr><th>Name</th><th>Specialty</th><th>Featured</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($trainers as $t) { ?>
              <tr>
                <td><?= htmlspecialchars($t->name(), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($t->specialty ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?php if ($t->featured) { ?>
                    <span class="promo-status promo-status--on">Featured</span>
                  <?php } else { ?>
                    <span class="promo-status promo-status--off">Not featured</span>
                  <?php } ?>
                </td>
                <td class="row-actions">
                  <form method="post" action="../actions/action_admin_promo.php" class="inline-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="entity_type" value="trainer">
                    <input type="hidden" name="entity_id" value="<?= (int)$t->id ?>">
                    <input type="hidden" name="featured" value="<?= $t->featured ? '0' : '1' ?>">
                    <button type="submit" class="<?= $t->featured ? 'btn--outline' : 'btn--primary' ?> btn--sm">
                      <?= $t->featured ? 'Unfeature' : 'Feature' ?>
                    </button>
                  </form>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
        </div>
      <?php } ?>
    </div>
  </section>
<?php
  }
?>
