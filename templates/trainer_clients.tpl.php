<?php
  declare(strict_types = 1);

  function drawTrainerClients(array $clients) {
?>
    <main class="dashboard-main">
      <header class="dashboard-header">
        <p class="dashboard-eyebrow">Trainer Management</p>
        <h1 class="dashboard-heading">Member Nutrition</h1>
        <p class="dashboard-sub">Manage nutrition plans for your class members and personal training clients.</p>
      </header>

      <section class="dashboard-activity">
        <div class="activity-table-wrap">
          <table class="activity-table">
            <thead>
              <tr>
                <th scope="col">Member Name</th>
                <th scope="col">Email</th>
                <th scope="col">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($clients as $c) { ?>
                <tr>
                  <td><?= htmlspecialchars($c['FirstName'] . ' ' . $c['LastName'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($c['Email'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <a href="trainer_nutrition.php?member_id=<?= (int)$c['UserId'] ?>" class="btn-outline" style="padding: 0.25rem 0.75rem; font-size: 0.8rem; text-decoration: none;">Manage Plan</a>
                  </td>
                </tr>
              <?php } ?>
              <?php if (empty($clients)) { ?>
                <tr>
                  <td colspan="3" class="activity-empty">You do not have any active clients yet.</td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
<?php
  }
?>
