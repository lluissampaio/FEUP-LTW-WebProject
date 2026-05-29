<?php
  declare(strict_types = 1);

  function drawAdminUsers(array $users, string $csrf, int $meId, ?string $validRole) {
?>
  <section class="admin-section">
    <div class="section-inner">
      <form method="get" class="admin-form">
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

      <h2 class="section-heading">Create a new account</h2>
      <form method="post" action="../actions/action_admin_user.php" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="create">

        <div class="form-field">
          <label for="new-first">First name</label>
          <input type="text" id="new-first" name="first_name" required maxlength="40">
        </div>
        <div class="form-field">
          <label for="new-last">Last name</label>
          <input type="text" id="new-last" name="last_name" required maxlength="40">
        </div>
        <div class="form-field">
          <label for="new-username">Username</label>
          <input type="text" id="new-username" name="username" required maxlength="40">
        </div>
        <div class="form-field">
          <label for="new-email">Email</label>
          <input type="email" id="new-email" name="email" required maxlength="120">
        </div>
        <div class="form-field">
          <label for="new-password">Temporary password</label>
          <input type="password" id="new-password" name="password" required minlength="8" autocomplete="new-password">
        </div>
        <div class="form-field">
          <label for="new-role">Role</label>
          <select id="new-role" name="role">
            <option value="member">Member</option>
            <option value="trainer">Trainer</option>
            <option value="admin">Admin</option>
          </select>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary">Create account</button>
        </div>
      </form>

      <h2 class="section-heading">All users (<?= count($users) ?>)</h2>
      <div class="table-scroll">
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
              <td><?= htmlspecialchars($u->name(), ENT_QUOTES, 'UTF-8') ?><?= $isSelf ? ' <span class="status-tag status-tag-open">you</span>' : '' ?></td>
              <td><?= htmlspecialchars($u->username, ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($u->email, ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <?php if ($isSelf) { ?>
                  <span class="status-tag status-tag-open"><?= htmlspecialchars($u->role, ENT_QUOTES, 'UTF-8') ?></span>
                <?php } else { ?>
                  <form method="post" action="../actions/action_admin_user.php" class="inline-form">
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
                  <span class="status-tag status-tag-open">active</span>
                <?php } else { ?>
                  <span class="status-tag status-tag-full">disabled</span>
                <?php } ?>
              </td>
              <td><?= htmlspecialchars(date('d/m/Y', strtotime($u->joinDate)), ENT_QUOTES, 'UTF-8') ?></td>
              <td class="row-actions">
                <?php if (!$isSelf) { ?>
                  <?php if ($u->active) { ?>
                    <form method="post" action="../actions/action_admin_user.php" class="inline-form">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="action"     value="deactivate">
                      <input type="hidden" name="user_id"    value="<?= (int)$u->id ?>">
                      <button type="submit" class="btn-outline" onclick="return confirm('Deactivate this account?')">Deactivate</button>
                    </form>
                  <?php } else { ?>
                    <form method="post" action="../actions/action_admin_user.php" class="inline-form">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="action"     value="activate">
                      <input type="hidden" name="user_id"    value="<?= (int)$u->id ?>">
                      <button type="submit" class="btn-outline">Activate</button>
                    </form>
                  <?php } ?>
                  <form method="post" action="../actions/action_admin_user.php" class="inline-form">
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
    </div>
  </section>
<?php
  }
?>
