<?php
  declare(strict_types = 1);

  function drawAdminEquipment(array $items, array $locations, string $csrf, ?Equipment $editing) {
?>
  <section class="admin-section">
    <div class="section-inner">
      <h2 class="section-heading"><?= $editing ? 'Edit equipment' : 'New equipment' ?></h2>
      <form class="admin-form" method="post" action="../actions/action_admin_equipment.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
        <?php if ($editing) { ?><input type="hidden" name="equipment_id" value="<?= (int)$editing->id ?>"><?php } ?>

        <label class="form-field">
          Name <span class="required-mark" aria-hidden="true">*</span>
          <input type="text" id="e-name" name="name" required maxlength="120" value="<?= htmlspecialchars($editing->name ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label class="form-field">
          Type <span class="required-mark" aria-hidden="true">*</span>
          <input type="text" id="e-type" name="type" required maxlength="60" value="<?= htmlspecialchars($editing->type ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label class="form-field">
          Location <span class="required-mark" aria-hidden="true">*</span>
          <select id="e-location" name="location_id" required>
            <?php foreach ($locations as $l) { ?>
              <option value="<?= (int)$l->id ?>" <?= ($editing && $editing->locationId === $l->id) ? 'selected' : '' ?>><?= htmlspecialchars($l->name, ENT_QUOTES, 'UTF-8') ?></option>
            <?php } ?>
          </select>
        </label>
        <label class="form-field">
          Quantity <span class="required-mark" aria-hidden="true">*</span>
          <input type="number" id="e-quantity" name="quantity" min="0" max="999" value="<?= (int)($editing->quantity ?? 1) ?>" required>
        </label>
        <label class="form-field">
          Available <span class="required-mark" aria-hidden="true">*</span>
          <input type="number" id="e-available" name="available" min="0" max="999" value="<?= (int)($editing->available ?? 1) ?>" required>
        </label>
        <label class="form-field">
          Status <span class="required-mark" aria-hidden="true">*</span>
          <select id="e-status" name="status" required>
            <?php foreach (['operational','maintenance','out_of_order'] as $s) { ?>
              <option value="<?= $s ?>" <?= ($editing && $editing->status === $s) ? 'selected' : '' ?>><?= str_replace('_', ' ', $s) ?></option>
            <?php } ?>
          </select>
        </label>

        <div class="form-actions">
          <button type="submit" class="btn-primary"><?= $editing ? 'Update equipment' : 'Add equipment' ?></button>
          <?php if ($editing) { ?><a href="admin_equipment.php" class="btn-outline">Cancel</a><?php } ?>
        </div>
      </form>
    </div>
  </section>

  <section class="admin-section">
    <div class="section-inner">
      <h2 class="section-heading">All equipment (<?= count($items) ?>)</h2>
      <div class="table-scroll">
      <table class="data-table">
        <thead><tr><th>Name</th><th>Type</th><th>Location</th><th>Available</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($items as $e) { ?>
            <tr>
              <td><?= htmlspecialchars($e->name, ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($e->type, ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($e->locationName ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= (int)$e->available ?> / <?= (int)$e->quantity ?></td>
              <td><span class="status-tag status-tag-<?= str_replace('_','-', $e->status) ?>"><?= str_replace('_',' ', $e->status) ?></span></td>
              <td class="row-actions">
                <a href="admin_equipment.php?edit=<?= (int)$e->id ?>" class="btn-outline">Edit</a>
                <form method="post" action="../actions/action_admin_equipment.php" class="inline-form">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="equipment_id" value="<?= (int)$e->id ?>">
                  <button type="submit" class="btn-outline" data-confirm="Delete this item?">Delete</button>
                </form>
              </td>
            </tr>
          <?php } ?>
          <?php if (empty($items)) { ?>
            <tr><td colspan="6" class="empty-state">No equipment yet.</td></tr>
          <?php } ?>
        </tbody>
      </table>
      </div>
    </div>
  </section>
<?php
  }
?>
