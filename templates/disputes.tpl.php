<?php declare(strict_types = 1); ?>

<?php
  function disputeStatusLabel(string $status) : string {
    return ['open' => 'Open', 'in_review' => 'In review', 'resolved' => 'Resolved'][$status] ?? $status;
  }

  function disputeCategoryLabel(string $category) : string {
    return ucfirst($category);
  }
?>

<?php
  /** Member's report form. */
  function drawDisputeForm(string $csrf) {
?>
  <section class="dispute-block">
    <h2 class="dashboard-section-heading">Report an issue</h2>
    <form class="dispute-form" method="post" action="../actions/action_dispute_create.php">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

      <label class="form-field">
        Category
        <select id="dispute-category" name="category" required>
          <option value="equipment">Equipment</option>
          <option value="class">Class</option>
          <option value="billing">Billing</option>
          <option value="staff">Staff</option>
          <option value="other">Other</option>
        </select>
      </label>

      <label class="form-field">
        Subject
        <input type="text" id="dispute-subject" name="subject" maxlength="140" required>
      </label>

      <label class="form-field">
        Description
        <textarea id="dispute-message" name="message" rows="5" required></textarea>
      </label>

      <button type="submit" class="btn-primary">Submit report</button>
    </form>
  </section>
<?php } ?>

<?php
  /** Member's own reports, with any admin response. */
  function drawMemberDisputes(array $disputes) {
?>
  <section class="dispute-block">
    <h2 class="dashboard-section-heading">Your reports</h2>
    <?php if (empty($disputes)) { ?>
      <p class="activity-empty">You haven't reported anything yet.</p>
    <?php } else { ?>
      <ul class="dispute-list">
        <?php foreach ($disputes as $d) { ?>
          <li>
            <article class="dispute-card">
              <header class="dispute-card-head">
                <span class="dispute-cat"><?= htmlspecialchars(disputeCategoryLabel($d->category), ENT_QUOTES, 'UTF-8') ?></span>
                <span class="status-tag status-tag-<?= htmlspecialchars($d->status, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(disputeStatusLabel($d->status), ENT_QUOTES, 'UTF-8') ?></span>
              </header>
              <h3 class="dispute-subject"><?= htmlspecialchars($d->subject, ENT_QUOTES, 'UTF-8') ?></h3>
              <p class="dispute-message"><?= nl2br(htmlspecialchars($d->message, ENT_QUOTES, 'UTF-8')) ?></p>
              <p class="dispute-meta">Reported <?= htmlspecialchars(date('j M Y', strtotime($d->createdAt)), ENT_QUOTES, 'UTF-8') ?></p>
              <?php if ($d->response) { ?>
                <div class="dispute-response">
                  <p class="dispute-response-label">Iron Haven response</p>
                  <p><?= nl2br(htmlspecialchars($d->response, ENT_QUOTES, 'UTF-8')) ?></p>
                </div>
              <?php } ?>
            </article>
          </li>
        <?php } ?>
      </ul>
    <?php } ?>
  </section>
<?php } ?>

<?php
  /** Admin triage list with per-dispute respond form. */
  function drawAdminDisputes(array $disputes, string $csrf) {
?>
  <section class="dispute-block">
    <?php if (empty($disputes)) { ?>
      <p class="activity-empty">No disputes have been raised.</p>
    <?php } else { ?>
      <ul class="dispute-list">
        <?php foreach ($disputes as $d) { ?>
          <li>
            <article class="dispute-card">
              <header class="dispute-card-head">
                <span class="dispute-cat"><?= htmlspecialchars(disputeCategoryLabel($d->category), ENT_QUOTES, 'UTF-8') ?></span>
                <span class="status-tag status-tag-<?= htmlspecialchars($d->status, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(disputeStatusLabel($d->status), ENT_QUOTES, 'UTF-8') ?></span>
              </header>
              <h3 class="dispute-subject"><?= htmlspecialchars($d->subject, ENT_QUOTES, 'UTF-8') ?></h3>
              <p class="dispute-meta">From <?= htmlspecialchars($d->memberName ?? 'Member', ENT_QUOTES, 'UTF-8') ?> &middot; <?= htmlspecialchars(date('j M Y', strtotime($d->createdAt)), ENT_QUOTES, 'UTF-8') ?></p>
              <p class="dispute-message"><?= nl2br(htmlspecialchars($d->message, ENT_QUOTES, 'UTF-8')) ?></p>

              <form class="dispute-respond-form" method="post" action="../actions/action_dispute_respond.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="dispute_id" value="<?= (int)$d->id ?>">

                <label class="form-field">
                  Status
                  <select id="status-<?= (int)$d->id ?>" name="status">
                    <?php foreach (['open' => 'Open', 'in_review' => 'In review', 'resolved' => 'Resolved'] as $val => $lab) { ?>
                      <option value="<?= $val ?>"<?= $d->status === $val ? ' selected' : '' ?>><?= $lab ?></option>
                    <?php } ?>
                  </select>
                </label>

                <label class="form-field">
                  Response to member
                  <textarea id="response-<?= (int)$d->id ?>" name="response" rows="3"><?= htmlspecialchars($d->response ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </label>

                <button type="submit" class="btn-primary btn-sm">Save response</button>
              </form>
            </article>
          </li>
        <?php } ?>
      </ul>
    <?php } ?>
  </section>
<?php } ?>
