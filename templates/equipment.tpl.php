<?php declare(strict_types = 1); ?>

<?php function drawEquipmentFilters(array $locations, array $types, ?int $locationId, string $type) { ?>
  <section class="filter-section" aria-label="Filter equipment">
    <div class="section-inner">
      <form class="filter-form" id="equipment-filter-form" method="get" action="equipment.php">
        <label class="filter-field">
          Location
          <select id="eq-location" name="location">
            <option value="">All locations</option>
            <?php foreach ($locations as $l) { ?>
              <option value="<?= (int)$l->id ?>" <?= $locationId === $l->id ? 'selected' : '' ?>><?= htmlspecialchars($l->city, ENT_QUOTES, 'UTF-8') ?></option>
            <?php } ?>
          </select>
        </label>
        <label class="filter-field">
          Type
          <select id="eq-type" name="type">
            <option value="">All types</option>
            <?php foreach ($types as $t) { ?>
              <option value="<?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?>" <?= $type === $t ? 'selected' : '' ?>><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></option>
            <?php } ?>
          </select>
        </label>
        <div class="filter-actions">
          <button type="submit" class="btn-primary">Filter</button>
          <a href="equipment.php" class="btn-outline">Reset</a>
        </div>
      </form>
    </div>
  </section>
<?php } ?>

<?php function drawEquipmentList(array $items) { ?>
  <section class="equipment-section" aria-labelledby="equipment-heading">
    <div class="section-inner">
      <p class="section-label">01 &mdash; Inventory</p>
      <h2 class="section-heading" id="equipment-heading">Equipment (<span id="equipment-count"><?= count($items) ?></span>)</h2>

      <div id="equipment-results">
        <?php if (count($items) === 0) { ?>
          <p class="empty-state">No equipment matches your filters.</p>
        <?php } else { ?>
          <div class="table-scroll">
          <table class="data-table">
            <thead>
              <tr>
                <th scope="col">Name</th>
                <th scope="col">Type</th>
                <th scope="col">Location</th>
                <th scope="col">Available</th>
                <th scope="col">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $e) {
                $statusClass = 'status-tag status-tag-' . htmlspecialchars(str_replace('_', '-', $e->status), ENT_QUOTES, 'UTF-8');
              ?>
                <tr>
                  <td><?= htmlspecialchars($e->name, ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($e->type, ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($e->locationName ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= (int)$e->available ?> / <?= (int)$e->quantity ?></td>
                  <td><span class="<?= $statusClass ?>"><?= htmlspecialchars(str_replace('_', ' ', $e->status), ENT_QUOTES, 'UTF-8') ?></span></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
          </div>
        <?php } ?>
      </div>
    </div>
  </section>

  <script src="../javascript/equipment-filter.js" defer></script>
<?php } ?>
