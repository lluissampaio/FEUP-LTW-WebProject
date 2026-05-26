<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  require_once(__DIR__ . '/../database/connection.db.php');
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

  $db        = getDatabaseConnection();
  $me        = User::getUser($db, $session->getId());
  $filter    = $_GET['role'] ?? '';
  $validRole = in_array($filter, ['member','trainer','admin'], true) ? $filter : null;
  $users     = User::getAll($db, $validRole);
  $csrf      = $session->csrfToken();
  $meId      = $session->getId();

  drawHeader($session, '', 'Admin · Users — Iron Haven');
?>
  <section class="dashboard">
    <?php drawDashboardSidebar($me, 'admin_users'); ?>
    <main class="dashboard-main">
      <header class="dashboard-header">
        <p class="dashboard-eyebrow">Admin</p>
        <h1 class="dashboard-heading">Users</h1>
        <p class="dashboard-sub">Promote, deactivate or remove accounts.</p>
      </header>

  <section class="admin-section">
    <div class="section-inner">
      <form method="get" class="admin-form" style="margin-bottom: 16px;">
        <div class="form-field">
          <label for="role-filter">Filter by role</label>
          <select id="role-filter" name="role" onchange="this.form.submit()">
            <option value=""        <?= $validRole === null      ? 'selected' : '' ?>>All roles</option>
            <option value="member"  <?= $validRole === 'member'  ? 'selected' : '' ?>>Member</option>
            <option value="trainer" <?= $validRole === 'trainer' ? 'selected' : '' ?>>Trainer</option>
            <option value="admin"   <?= $validRole === 'admin'   ? 'selected' : '' ?>>Admin</option>
          </select>
        </div>
      </form>

      <h2 class="section-heading">All users (<?= count($users) ?>)</h2>
      <table class="data-table">
        <thead>
          <tr>
            <th>Name</th><th>Username</th><th>Email</th><th>Role</th>
            <th>Status</th><th>Joined</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u) { $isSelf = $u->id === $meId; ?>
            <tr>
              <td><?= htmlspecialchars($u->name(), ENT_QUOTES, 'UTF-8') ?><?= $isSelf ? ' <span class="status-tag status-tag--open">you</span>' : '' ?></td>
              <td><?= htmlspecialchars($u->username, ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($u->email, ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <?php if ($isSelf) { ?>
                  <span class="status-tag status-tag--open"><?= htmlspecialchars($u->role, ENT_QUOTES, 'UTF-8') ?></span>
                <?php } else { ?>
                  <form method="post" action="../actions/action_admin_user.php" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action"     value="role">
                    <input type="hidden" name="user_id"    value="<?= (int)$u->id ?>">
                    <select name="role" onchange="this.form.submit()">
                      <option value="member"  <?= $u->role === 'member'  ? 'selected' : '' ?>>member</option>
                      <option value="trainer" <?= $u->role === 'trainer' ? 'selected' : '' ?>>trainer</option>
                      <option value="admin"   <?= $u->role === 'admin'   ? 'selected' : '' ?>>admin</option>
                    </select>
                  </form>
                <?php } ?>
              </td>
              <td>
                <?php if ($u->active) { ?>
                  <span class="status-tag status-tag--open">active</span>
                <?php } else { ?>
                  <span class="status-tag status-tag--full">disabled</span>
                <?php } ?>
              </td>
              <td><?= htmlspecialchars(date('d/m/Y', strtotime($u->joinDate)), ENT_QUOTES, 'UTF-8') ?></td>
              <td class="row-actions">
                <?php if (!$isSelf) { ?>
                  <?php if ($u->active) { ?>
                    <form method="post" action="../actions/action_admin_user.php" style="display:inline">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="action"     value="deactivate">
                      <input type="hidden" name="user_id"    value="<?= (int)$u->id ?>">
                      <button type="submit" class="btn-outline" onclick="return confirm('Deactivate this account?')">Deactivate</button>
                    </form>
                  <?php } else { ?>
                    <form method="post" action="../actions/action_admin_user.php" style="display:inline">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="action"     value="activate">
                      <input type="hidden" name="user_id"    value="<?= (int)$u->id ?>">
                      <button type="submit" class="btn-outline">Activate</button>
                    </form>
                  <?php } ?>
                  <form method="post" action="../actions/action_admin_user.php" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action"     value="delete">
                    <input type="hidden" name="user_id"    value="<?= (int)$u->id ?>">
                    <button type="submit" class="btn-outline" onclick="return confirm('Delete this account permanently? Related data will be removed.')">Delete</button>
                  </form>
                <?php } ?>
              </td>
            </tr>
          <?php } ?>
          <?php if (empty($users)) { ?>
            <tr><td colspan="7" class="empty-state">No users match this filter.</td></tr>
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
