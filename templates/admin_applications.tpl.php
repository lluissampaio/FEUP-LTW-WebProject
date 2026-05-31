<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/format.php');

  function drawAdminApplications(array $apps, string $csrf, ?string $validStatus) {
    $statusTag = [
      'pending'  => 'status-tag',
      'accepted' => 'status-tag status-tag--open',
      'rejected' => 'status-tag status-tag--full',
    ];
?>
  <section class="admin-section">
    <div class="section__inner">
      <form method="get" class="admin-form">
        <div class="form-field">
          <label for="status-filter">Filter by status</label>
          <select id="status-filter" name="status" onchange="this.form.submit()">
            <option value=""         <?= $validStatus === null       ? 'selected' : '' ?>>All applications</option>
            <option value="pending"  <?= $validStatus === 'pending'  ? 'selected' : '' ?>>Pending</option>
            <option value="accepted" <?= $validStatus === 'accepted' ? 'selected' : '' ?>>Accepted</option>
            <option value="rejected" <?= $validStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
          </select>
        </div>
      </form>

      <h2 class="section__heading">Applications (<?= count($apps) ?>)</h2>
      <div class="table-scroll">
        <table class="data-table">
          <thead>
            <tr>
              <th scope="col">Member</th><th scope="col">Email</th><th scope="col">Requested plan</th>
              <th scope="col">Note</th><th scope="col">Status</th><th scope="col">Submitted</th><th scope="col"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($apps as $a) { ?>
              <tr>
                <td><?= htmlspecialchars($a->memberName ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($a->memberEmail ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($a->planName ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($a->message ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="<?= $statusTag[$a->status] ?? 'status-tag' ?>"><?= htmlspecialchars($a->status, ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><?= htmlspecialchars(formatDate($a->createdAt), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="row-actions">
                  <?php if ($a->status === 'pending') { ?>
                    <form method="post" action="../actions/action_admin_application.php" class="inline-form">
                      <input type="hidden" name="csrf_token"     value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="action"         value="accept">
                      <input type="hidden" name="application_id" value="<?= (int)$a->id ?>">
                      <button type="submit" class="btn--outline btn--sm">Accept</button>
                    </form>
                    <form method="post" action="../actions/action_admin_application.php" class="inline-form">
                      <input type="hidden" name="csrf_token"     value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="action"         value="reject">
                      <input type="hidden" name="application_id" value="<?= (int)$a->id ?>">
                      <button type="submit" class="btn--outline btn--sm" data-confirm="Reject this application? This cannot be undone.">Reject</button>
                    </form>
                  <?php } else { ?>
                    <span class="activity-empty">decided</span>
                  <?php } ?>
                </td>
              </tr>
            <?php } ?>
            <?php if (empty($apps)) { ?>
              <tr><td colspan="7" class="empty-state">No applications match this filter.</td></tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
<?php
  }
?>
