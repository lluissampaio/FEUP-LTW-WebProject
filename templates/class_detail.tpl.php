<?php declare(strict_types = 1); ?>

<?php function drawClassDetail(GymClass $class, array $schedules, array $myEnrolled, array $myWaiting, bool $isLoggedIn, string $csrf) { ?>
  <section class="page-hero">
    <div class="page-hero-inner">
      <p class="breadcrumb"><a href="classes.php">Classes</a> <span class="breadcrumb-sep">/</span> <span><?= htmlspecialchars($class->name, ENT_QUOTES, 'UTF-8') ?></span></p>
      <p class="hero-eyebrow"><?= htmlspecialchars($class->type, ENT_QUOTES, 'UTF-8') ?> &middot; <?= htmlspecialchars($class->locationName ?? '', ENT_QUOTES, 'UTF-8') ?></p>
      <h1 class="page-hero-heading"><?= htmlspecialchars($class->name, ENT_QUOTES, 'UTF-8') ?></h1>
      <?php if ($class->description) { ?>
        <p class="page-hero-sub"><?= htmlspecialchars($class->description, ENT_QUOTES, 'UTF-8') ?></p>
      <?php } ?>
      <p class="page-hero-sub">Coached by <a href="trainer.php?id=<?= (int)$class->trainerId ?>"><?= htmlspecialchars($class->trainerName ?? '', ENT_QUOTES, 'UTF-8') ?></a> &middot; capacity <?= (int)$class->capacity ?></p>
    </div>
  </section>

  <section class="schedule-list-section" aria-labelledby="sessions-heading">
    <div class="section-inner">
      <p class="section-label">01 &mdash; Sessions</p>
      <h2 class="section-heading" id="sessions-heading">Weekly sessions</h2>

      <?php if (count($schedules) === 0) { ?>
        <p class="empty-state">No upcoming sessions scheduled.</p>
      <?php } ?>

      <ul class="schedule-cards">
        <?php foreach ($schedules as $s) {
          $enrolled = isset($myEnrolled[$s->id]);
          $waiting  = isset($myWaiting[$s->id]);
          $full     = $s->isFull();
          $spots    = max(0, ($s->capacity ?? 0) - ($s->enrolledCount ?? 0));
        ?>
          <li class="schedule-card" data-schedule-id="<?= (int)$s->id ?>">
            <header class="schedule-card-head">
              <p class="schedule-card-day"><?= htmlspecialchars($s->dayOfWeek, ENT_QUOTES, 'UTF-8') ?> &middot; <?= htmlspecialchars($s->startTime, ENT_QUOTES, 'UTF-8') ?></p>
              <p class="schedule-card-meta"><?= (int)$s->duration ?> min</p>
            </header>
            <p class="schedule-card-capacity">
              <?php if ($full) { ?>
                <span class="status-tag status-tag--full">Full</span>
              <?php } else { ?>
                <span class="status-tag status-tag--open"><?= $spots ?> spot<?= $spots === 1 ? '' : 's' ?> left</span>
              <?php } ?>
            </p>
            <div class="schedule-card-actions">
              <?php if (!$isLoggedIn) { ?>
                <a href="login.php" class="btn-outline">Sign in to book</a>
              <?php } elseif ($enrolled) { ?>
                <form method="post" action="../actions/action_cancel.php">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="schedule_id" value="<?= (int)$s->id ?>">
                  <span class="enrolled-tag">Enrolled</span>
                  <button type="submit" class="btn-outline">Cancel</button>
                </form>
              <?php } elseif ($waiting) { ?>
                <form method="post" action="../actions/action_waitlist_leave.php">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="schedule_id" value="<?= (int)$s->id ?>">
                  <span class="enrolled-tag">On waitlist</span>
                  <button type="submit" class="btn-outline">Leave waitlist</button>
                </form>
              <?php } elseif ($full) { ?>
                <form method="post" action="../actions/action_waitlist_join.php">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="schedule_id" value="<?= (int)$s->id ?>">
                  <button type="submit" class="btn-outline">Join waitlist</button>
                </form>
              <?php } else { ?>
                <form method="post" action="../actions/action_enroll.php">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="schedule_id" value="<?= (int)$s->id ?>">
                  <button type="submit" class="btn-primary">Enrol</button>
                </form>
              <?php } ?>
            </div>
          </li>
        <?php } ?>
      </ul>
    </div>
  </section>
<?php } ?>

<?php function drawClassReviews(GymClass $class, array $reviews, ?float $avg, bool $canReview, ?Review $myReview, string $csrf) { ?>
  <section class="reviews-section" id="reviews" aria-labelledby="reviews-heading">
    <div class="section-inner">
      <p class="section-label">02 &mdash; Member feedback</p>
      <h2 class="section-heading" id="reviews-heading">
        Ratings &amp; reviews
        <?php if ($avg !== null) { ?>
          <span class="reviews-avg" aria-label="Average rating">&nbsp;&nbsp;<?= number_format($avg, 1) ?> / 5 &middot; <?= count($reviews) ?> review<?= count($reviews) === 1 ? '' : 's' ?></span>
        <?php } ?>
      </h2>

      <?php if ($canReview) { ?>
        <form class="review-form" method="post" action="../actions/action_review.php">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="class_id"   value="<?= (int)$class->id ?>">

          <fieldset class="review-rating">
            <legend>Your rating</legend>
            <?php for ($r = 5; $r >= 1; $r--) {
              $checked = $myReview && $myReview->rating === $r ? 'checked' : ($r === 5 && !$myReview ? 'checked' : '');
            ?>
              <label><input type="radio" name="rating" value="<?= $r ?>" <?= $checked ?> required> <?= str_repeat('★', $r) ?></label>
            <?php } ?>
          </fieldset>

          <div class="form-field">
            <label for="review-comment">Your comment</label>
            <textarea id="review-comment" name="comment" rows="4" maxlength="1000"><?= htmlspecialchars($myReview->comment ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>

          <button type="submit" class="btn-primary"><?= $myReview ? 'Update review' : 'Submit review' ?></button>
        </form>
      <?php } ?>

      <?php if (count($reviews) === 0) { ?>
        <p class="empty-state">No reviews yet.</p>
      <?php } ?>

      <ul class="reviews-list">
        <?php foreach ($reviews as $r) { ?>
          <li class="review-item">
            <p class="review-rating-display" aria-label="<?= (int)$r->rating ?> out of 5"><?= str_repeat('★', $r->rating) . str_repeat('☆', 5 - $r->rating) ?></p>
            <?php if ($r->comment) { ?>
              <blockquote><?= htmlspecialchars($r->comment, ENT_QUOTES, 'UTF-8') ?></blockquote>
            <?php } ?>
            <p class="review-author"><?= htmlspecialchars($r->userName ?? '', ENT_QUOTES, 'UTF-8') ?> &middot; <?= htmlspecialchars(date('d M Y', strtotime($r->createdAt)), ENT_QUOTES, 'UTF-8') ?></p>
          </li>
        <?php } ?>
      </ul>
    </div>
  </section>
<?php } ?>
