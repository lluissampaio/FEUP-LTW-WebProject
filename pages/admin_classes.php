<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
  require_once(__DIR__ . '/../database/class.class.php');
  require_once(__DIR__ . '/../database/schedule.class.php');
  require_once(__DIR__ . '/../database/trainer.class.php');
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
  if (isset($_GET['edit'])) $editing = GymClass::getClass($db, (int)$_GET['edit']);

  $classes   = GymClass::getClasses($db);
  $trainers  = Trainer::getTrainers($db);
  $locations = Location::getLocations($db);
  $csrf      = $session->csrfToken();
  $days      = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

  $editingSchedules = [];
  if ($editing) $editingSchedules = Schedule::getByClass($db, $editing->id);

  // Quick lookup: how many sessions each class has (so admins can spot
  // classes that won't show on the public schedule page yet).
  $sessionCounts = [];
  $stmt = $db->query('SELECT ClassId, COUNT(*) AS n FROM Schedule GROUP BY ClassId');
  while ($row = $stmt->fetch()) $sessionCounts[(int)$row['ClassId']] = (int)$row['n'];

  drawHeader($session, '', 'Admin · Classes — Iron Haven');
?>
  <section class="dashboard">
    <?php drawDashboardSidebar($user, 'admin'); ?>
    <main class="dashboard-main">
      <header class="dashboard-header">
        <p class="dashboard-eyebrow">Admin</p>
        <h1 class="dashboard-heading">Class catalog</h1>
        <p class="dashboard-sub">Create, edit or remove classes and assign trainers.</p>
      </header>

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

        <?php if (!$editing) { ?>
          <fieldset class="form-field" style="grid-column: 1 / -1; border: 1px dashed var(--color-border); padding: 16px;">
            <legend style="padding: 0 8px; color: var(--color-muted); font-size: 12px; letter-spacing: .12em; text-transform: uppercase;">Optional first session</legend>
            <p class="schedule-card-meta" style="margin-bottom: 8px;">A class only shows up on the schedule page once it has at least one session. Add one here, or add more after creating.</p>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
              <div class="form-field">
                <label for="c-day">Day</label>
                <select id="c-day" name="schedule_day">
                  <option value="">— None —</option>
                  <?php foreach ($days as $d) { ?>
                    <option value="<?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="form-field">
                <label for="c-start">Start time</label>
                <input type="time" id="c-start" name="schedule_start" value="">
              </div>
              <div class="form-field">
                <label for="c-duration">Duration (min)</label>
                <input type="number" id="c-duration" name="schedule_duration" min="15" max="240" step="5" value="60">
              </div>
            </div>
          </fieldset>
        <?php } ?>

        <div class="form-actions">
          <button type="submit" class="btn-primary"><?= $editing ? 'Update class' : 'Create class' ?></button>
          <?php if ($editing) { ?><a href="admin_classes.php" class="btn-outline">Cancel</a><?php } ?>
        </div>
      </form>
    </div>
  </section>

  <?php if ($editing) { ?>
    <section class="admin-section">
      <div class="section-inner">
        <h2 class="section-heading">Sessions for &ldquo;<?= htmlspecialchars($editing->name, ENT_QUOTES, 'UTF-8') ?>&rdquo; (<?= count($editingSchedules) ?>)</h2>

        <form class="admin-form" method="post" action="../actions/action_admin_schedule.php" style="margin-bottom: 24px;">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="action"     value="create">
          <input type="hidden" name="class_id"   value="<?= (int)$editing->id ?>">

          <div class="form-field">
            <label for="s-day">Day</label>
            <select id="s-day" name="day_of_week" required>
              <?php foreach ($days as $d) { ?>
                <option value="<?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="form-field">
            <label for="s-start">Start time</label>
            <input type="time" id="s-start" name="start_time" required>
          </div>
          <div class="form-field">
            <label for="s-duration">Duration (min)</label>
            <input type="number" id="s-duration" name="duration" min="15" max="240" step="5" value="60" required>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-primary">Add session</button>
          </div>
        </form>

        <?php if (count($editingSchedules) === 0) { ?>
          <p class="empty-state">No sessions yet. Add one above to make this class visible on the schedule page.</p>
        <?php } else { ?>
          <table class="data-table">
            <thead><tr><th>Day</th><th>Time</th><th>Duration</th><th>Enrolled</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($editingSchedules as $s) { ?>
                <tr>
                  <td><?= htmlspecialchars($s->dayOfWeek, ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($s->startTime, ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= (int)$s->duration ?> min</td>
                  <td><?= (int)($s->enrolledCount ?? 0) ?> / <?= (int)($s->capacity ?? 0) ?></td>
                  <td class="row-actions">
                    <form method="post" action="../actions/action_admin_schedule.php" style="display:inline">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="action"      value="delete">
                      <input type="hidden" name="class_id"    value="<?= (int)$editing->id ?>">
                      <input type="hidden" name="schedule_id" value="<?= (int)$s->id ?>">
                      <button type="submit" class="btn-outline" onclick="return confirm('Delete this session and its enrolments?')">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        <?php } ?>
      </div>
    </section>
  <?php } ?>

  <section class="admin-section">
    <div class="section-inner">
      <h2 class="section-heading">All classes (<?= count($classes) ?>)</h2>
      <table class="data-table">
        <thead><tr><th>Name</th><th>Type</th><th>Trainer</th><th>Location</th><th>Capacity</th><th>Sessions</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($classes as $c) {
            $sessions = $sessionCounts[$c->id] ?? 0;
          ?>
            <tr>
              <td><a href="class.php?id=<?= (int)$c->id ?>"><?= htmlspecialchars($c->name, ENT_QUOTES, 'UTF-8') ?></a></td>
              <td><?= htmlspecialchars($c->type, ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c->trainerName ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($c->locationName ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= (int)$c->capacity ?></td>
              <td>
                <?php if ($sessions === 0) { ?>
                  <span class="status-tag status-tag--full">none</span>
                <?php } else { ?>
                  <span class="status-tag status-tag--open"><?= $sessions ?></span>
                <?php } ?>
              </td>
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
    </main>
  </section>
<?php
  drawFooter();
?>
