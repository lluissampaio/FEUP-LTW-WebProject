<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/class.class.php');
  require_once(__DIR__ . '/../database/schedule.class.php');
  require_once(__DIR__ . '/../database/trainer.class.php');
  require_once(__DIR__ . '/../database/location.class.php');
  require_once(__DIR__ . '/../templates/common.tpl.php');

  $session = new Session();
  $session->requireLogin('login.php');
  if (!isAdminUser($session)) {
    $session->addMessage('error', 'Admin access required.');
    header('Location: index.php');
    exit;
  }

  $db = getDatabaseConnection();

  $editing = null;
  if (isset($_GET['edit'])) $editing = GymClass::getClass($db, (int)$_GET['edit']);

  $classes   = GymClass::getClasses($db);
  $trainers  = Trainer::getTrainers($db);
  $locations = Location::getLocations($db);
  $csrf      = $session->csrfToken();

  drawHeader($session, 'admin', 'Admin · Classes — Iron Haven');
?>
  <section class="page-hero">
    <div class="page-hero-inner">
      <p class="breadcrumb"><a href="admin.php">Admin</a> <span class="breadcrumb-sep">/</span> <span>Classes</span></p>
      <h1 class="page-hero-heading">Class catalog</h1>
      <p class="page-hero-sub">Create, edit or remove classes and assign trainers.</p>
    </div>
  </section>

  <section class="admin-section">
    <div class="section-inner">
      <h2 class="section-heading"><?= $editing ? 'Edit class' : 'New class' ?></h2>
      <form class="admin-form" method="post" action="../actions/action_admin_class.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
        <?php if ($editing) { ?><input type="hidden" name="class_id" value="<?= (int)$editing->id ?>"><?php } ?>

        <div class="form-field">
          <label for="c-name">Name</label>
          <input type="text" id="c-name" name="name" required maxlength="120" value="<?= htmlspecialchars($editing->name ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="form-field">
          <label for="c-type">Type</label>
          <input type="text" id="c-type" name="type" required maxlength="60" value="<?= htmlspecialchars($editing->type ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="form-field">
          <label for="c-desc">Description</label>
          <textarea id="c-desc" name="description" rows="3" maxlength="1000"><?= htmlspecialchars($editing->description ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <div class="form-field">
          <label for="c-trainer">Trainer</label>
          <select id="c-trainer" name="trainer_id" required>
            <?php foreach ($trainers as $tr) { ?>
              <option value="<?= (int)$tr->id ?>" <?= ($editing && $editing->trainerId === $tr->id) ? 'selected' : '' ?>><?= htmlspecialchars($tr->name(), ENT_QUOTES, 'UTF-8') ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="form-field">
          <label for="c-location">Location</label>
          <select id="c-location" name="location_id" required>
            <?php foreach ($locations as $l) { ?>
              <option value="<?= (int)$l->id ?>" <?= ($editing && $editing->locationId === $l->id) ? 'selected' : '' ?>><?= htmlspecialchars($l->name, ENT_QUOTES, 'UTF-8') ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="form-field">
          <label for="c-capacity">Capacity</label>
          <input type="number" id="c-capacity" name="capacity" min="1" max="100" value="<?= (int)($editing->capacity ?? 10) ?>" required>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary"><?= $editing ? 'Update class' : 'Create class' ?></button>
          <?php if ($editing) { ?><a href="admin_classes.php" class="btn-outline">Cancel</a><?php } ?>
        </div>
      </form>
    </div>
  </section>

  <section class="admin-section">
    <div class="section-inner">
      <h2 class="section-heading">All classes (<?= count($classes) ?>)</h2>
      <table class="data-table">
        <thead><tr><th>Name</th><th>Type</th><th>Trainer</th><th>Location</th><th>Capacity</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($classes as $c) { ?>
            <tr>
              <td><a href="class.php?id=<?= (int)$c->id ?>"><?= htmlspecialchars($c->name, ENT_QUOTES, 'UTF-8') ?></a></td>
              <td><?= htmlspecialchars($c->type, ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c->trainerName ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c->locationName ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= (int)$c->capacity ?></td>
              <td class="row-actions">
                <a href="admin_classes.php?edit=<?= (int)$c->id ?>" class="btn-outline">Edit</a>
                <form method="post" action="../actions/action_admin_class.php" style="display:inline">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="class_id" value="<?= (int)$c->id ?>">
                  <button type="submit" class="btn-outline" onclick="return confirm('Delete this class? All schedules and enrolments will be removed.')">Delete</button>
                </form>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </section>
<?php
  drawFooter();
?>
