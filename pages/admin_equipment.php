<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/equipment.class.php');
  require_once(__DIR__ . '/../database/location.class.php');
  require_once(__DIR__ . '/../database/user.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/dashboard.tpl.php');

  $session = new Session();
  $session->requireLogin('login.php');
  if (!isAdminUser($session)) {
    $session->addMessage('error', 'Admin access required.');
    header('Location: index.php');
    exit;
  }

  $db   = getDatabaseConnection();
  $user = User::getUser($db, $session->getId());

  $editing = null;
  if (isset($_GET['edit'])) $editing = Equipment::getEquipment($db, (int)$_GET['edit']);

  $items     = Equipment::getAll($db);
  $locations = Location::getLocations($db);
  $csrf      = $session->csrfToken();

  drawHeader($session, '', 'Admin · Equipment — Iron Haven');
?>
  <section class="dashboard">
    <?php drawDashboardSidebar($user, 'admin_equipment'); ?>
    <main class="dashboard-main">
      <header class="dashboard-header">
        <p class="dashboard-eyebrow">Admin</p>
        <h1 class="dashboard-heading">Equipment inventory</h1>
        <p class="dashboard-sub">Add, update availability or remove equipment across all locations.</p>
      </header>

  <section class="admin-section">
    <div class="section-inner">
      <h2 class="section-heading"><?= $editing ? 'Edit equipment' : 'New equipment' ?></h2>
      <form class="admin-form" method="post" action="../actions/action_admin_equipment.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
        <?php if ($editing) { ?><input type="hidden" name="equipment_id" value="<?= (int)$editing->id ?>"><?php } ?>

        <div class="form-field">
          <label for="e-name">Name</label>
          <input type="text" id="e-name" name="name" required maxlength="120" value="<?= htmlspecialchars($editing->name ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="form-field">
          <label for="e-type">Type</label>
          <input type="text" id="e-type" name="type" required maxlength="60" value="<?= htmlspecialchars($editing->type ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="form-field">
          <label for="e-location">Location</label>
          <select id="e-location" name="location_id" required>
            <?php foreach ($locations as $l) { ?>
              <option value="<?= (int)$l->id ?>" <?= ($editing && $editing->locationId === $l->id) ? 'selected' : '' ?>><?= htmlspecialchars($l->name, ENT_QUOTES, 'UTF-8') ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="form-field">
          <label for="e-quantity">Quantity</label>
          <input type="number" id="e-quantity" name="quantity" min="0" max="999" value="<?= (int)($editing->quantity ?? 1) ?>" required>
        </div>
        <div class="form-field">
          <label for="e-available">Available</label>
          <input type="number" id="e-available" name="available" min="0" max="999" value="<?= (int)($editing->available ?? 1) ?>" required>
        </div>
        <div class="form-field">
          <label for="e-status">Status</label>
          <select id="e-status" name="status" required>
            <?php foreach (['operational','maintenance','out_of_order'] as $s) { ?>
              <option value="<?= $s ?>" <?= ($editing && $editing->status === $s) ? 'selected' : '' ?>><?= str_replace('_', ' ', $s) ?></option>
            <?php } ?>
          </select>
        </div>

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
      <table class="data-table">
        <thead><tr><th>Name</th><th>Type</th><th>Location</th><th>Available</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($items as $e) { ?>
            <tr>
              <td><?= htmlspecialchars($e->name, ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($e->type, ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($e->locationName ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= (int)$e->available ?> / <?= (int)$e->quantity ?></td>
              <td><span class="status-tag status-tag--<?= str_replace('_','-', $e->status) ?>"><?= str_replace('_',' ', $e->status) ?></span></td>
              <td class="row-actions">
                <a href="admin_equipment.php?edit=<?= (int)$e->id ?>" class="btn-outline">Edit</a>
                <form method="post" action="../actions/action_admin_equipment.php" style="display:inline">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="equipment_id" value="<?= (int)$e->id ?>">
                  <button type="submit" class="btn-outline" onclick="return confirm('Delete this item?')">Delete</button>
                </form>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </section>
    </main>
  </section>
<?php
  drawFooter();
?>
