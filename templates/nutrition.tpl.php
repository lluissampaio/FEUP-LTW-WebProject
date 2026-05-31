<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/format.php');

  /*
   * Shared nutrition renderer used by both the member view (pages/nutrition.php)
   * and the trainer view (pages/trainer_nutrition.php).
   *
   *  - When $targetUser is null   -> member viewing their OWN plan: read-only plan +
   *    meal breakdown, plus a "log today" form and recent-logs comparison.
   *  - When $targetUser is a User -> trainer managing a client's plan: editable plan
   *    form + full meal CRUD. No logging UI (members log their own consumption).
   *
   * $meals : NutritionMeal[]  (plan breakdown, may be empty)
   * $logs  : NutritionLog[]   (member's recent consumption, only used in member view)
   */
  function drawNutritionPlan(?NutritionPlan $plan, Session $session, ?User $targetUser = null, array $meals = array(), array $logs = array()) {
    $isTrainerView = $targetUser !== null;
    $csrf          = $session->csrfToken();

    if ($isTrainerView) {
      drawNutritionPlanForm($plan, $csrf, $targetUser);
      drawNutritionMealManager($plan, $meals, $csrf, $targetUser);
    } else {
      drawNutritionPlanSummary($plan, $meals);
      if ($plan) {
        drawNutritionLogForm($plan, $csrf);
        drawNutritionLogHistory($plan, $logs);
      }
    }
  }

  /* ------------------------------------------------------------------ *
   *  Trainer: editable plan form
   * ------------------------------------------------------------------ */
  function drawNutritionPlanForm(?NutritionPlan $plan, string $csrf, User $targetUser) {
    $dietType      = $plan ? $plan->dietType : '';
    $dailyCalories = $plan ? (string)$plan->dailyCalories : '';
    $protein       = $plan ? (string)$plan->protein : '';
    $carbs         = $plan ? (string)$plan->carbs : '';
    $fats          = $plan ? (string)$plan->fats : '';
    $guidelines    = $plan ? $plan->guidelines : '';
?>
    <section class="admin-section">
      <h2 class="dashboard-section-heading">Daily macro target</h2>
      <form class="admin-form" method="post" action="../actions/action_save_nutrition.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="target_user_id" value="<?= (int)$targetUser->id ?>">

        <label class="form-field">
          Diet Type <span class="required-mark" aria-hidden="true">*</span>
          <input type="text" id="n-diet" name="diet_type" required maxlength="60" placeholder="e.g. Balanced, Keto, Vegan" value="<?= htmlspecialchars($dietType, ENT_QUOTES, 'UTF-8') ?>">
        </label>

        <label class="form-field">
          Daily Calories <span class="required-mark" aria-hidden="true">*</span>
          <input type="number" id="n-cals" name="daily_calories" required min="0" max="10000" value="<?= htmlspecialchars($dailyCalories, ENT_QUOTES, 'UTF-8') ?>">
        </label>

        <label class="form-field">
          Protein (g) <span class="required-mark" aria-hidden="true">*</span>
          <input type="number" id="n-protein" name="protein" required min="0" value="<?= htmlspecialchars($protein, ENT_QUOTES, 'UTF-8') ?>">
        </label>

        <label class="form-field">
          Carbs (g) <span class="required-mark" aria-hidden="true">*</span>
          <input type="number" id="n-carbs" name="carbs" required min="0" value="<?= htmlspecialchars($carbs, ENT_QUOTES, 'UTF-8') ?>">
        </label>

        <label class="form-field">
          Fats (g) <span class="required-mark" aria-hidden="true">*</span>
          <input type="number" id="n-fats" name="fats" required min="0" value="<?= htmlspecialchars($fats, ENT_QUOTES, 'UTF-8') ?>">
        </label>

        <label class="form-field span-full">
          Guidelines / Notes
          <textarea id="n-guide" name="guidelines" rows="6" placeholder="Any specific dietary restrictions, meal timing, or general notes..."><?= htmlspecialchars($guidelines, ENT_QUOTES, 'UTF-8') ?></textarea>
        </label>

        <div class="form-actions">
          <button type="submit" class="btn-primary">Save Nutrition Plan</button>
        </div>
      </form>
    </section>
<?php
  }

  /* ------------------------------------------------------------------ *
   *  Trainer: per-meal CRUD manager (requires an existing plan)
   * ------------------------------------------------------------------ */
  function drawNutritionMealManager(?NutritionPlan $plan, array $meals, string $csrf, User $targetUser) {
?>
    <section class="admin-section">
      <h2 class="dashboard-section-heading">Meal breakdown</h2>

      <?php if (!$plan) { ?>
        <p class="empty-state">Save a nutrition plan above before adding meals.</p>
      <?php } else { ?>
        <?php drawMealTable($meals, true, $csrf, (int)$targetUser->id); ?>

        <h3 class="dashboard-section-heading">Add a meal</h3>
        <form class="admin-form" method="post" action="../actions/action_nutrition_meal.php">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="member_id" value="<?= (int)$targetUser->id ?>">

          <label class="form-field">
            Meal name <span class="required-mark" aria-hidden="true">*</span>
            <input type="text" name="name" required maxlength="80" placeholder="e.g. Breakfast">
          </label>
          <label class="form-field">
            Calories <span class="required-mark" aria-hidden="true">*</span>
            <input type="number" name="calories" required min="0" value="0">
          </label>
          <label class="form-field">
            Protein (g) <span class="required-mark" aria-hidden="true">*</span>
            <input type="number" name="protein" required min="0" value="0">
          </label>
          <label class="form-field">
            Carbs (g) <span class="required-mark" aria-hidden="true">*</span>
            <input type="number" name="carbs" required min="0" value="0">
          </label>
          <label class="form-field">
            Fats (g) <span class="required-mark" aria-hidden="true">*</span>
            <input type="number" name="fats" required min="0" value="0">
          </label>

          <div class="form-actions">
            <button type="submit" class="btn-primary">Add meal</button>
          </div>
        </form>
      <?php } ?>
    </section>
<?php
  }

  /* ------------------------------------------------------------------ *
   *  Member: read-only plan summary + meal breakdown
   * ------------------------------------------------------------------ */
  function drawNutritionPlanSummary(?NutritionPlan $plan, array $meals) {
?>
    <section class="dashboard-stats" aria-label="Nutrition plan summary">
      <?php if (!$plan) { ?>
        <p class="empty-state">No nutrition plan yet. Your trainer will set up your daily macro targets and meal breakdown.</p>
      <?php } else { ?>
        <article class="stat-card">
          <p class="stat-card-label">Diet</p>
          <p class="stat-card-value"><?= htmlspecialchars($plan->dietType, ENT_QUOTES, 'UTF-8') ?></p>
          <p class="stat-card-note">Daily target</p>
        </article>
        <article class="stat-card">
          <p class="stat-card-label">Calories</p>
          <p class="stat-card-value"><?= (int)$plan->dailyCalories ?><span class="stat-unit"> kcal</span></p>
          <p class="stat-card-note">Per day</p>
        </article>
        <article class="stat-card">
          <p class="stat-card-label">Protein</p>
          <p class="stat-card-value"><?= (int)$plan->protein ?><span class="stat-unit">g</span></p>
          <p class="stat-card-note">Carbs <?= (int)$plan->carbs ?>g &middot; Fats <?= (int)$plan->fats ?>g</p>
        </article>
      <?php } ?>
    </section>

    <?php if ($plan) { ?>
      <section class="admin-section">
        <h2 class="dashboard-section-heading">Meal breakdown</h2>
        <?php drawMealTable($meals, false, '', 0); ?>
        <?php if ($plan->guidelines !== '') { ?>
          <p class="nutrition-guidelines"><?= nl2br(htmlspecialchars($plan->guidelines, ENT_QUOTES, 'UTF-8')) ?></p>
        <?php } ?>
      </section>
    <?php } ?>
<?php
  }

  /* ------------------------------------------------------------------ *
   *  Shared meal table (totals row). $editable adds per-row edit/delete
   *  controls (trainer view only).
   * ------------------------------------------------------------------ */
  function drawMealTable(array $meals, bool $editable, string $csrf, int $memberId) {
    if (empty($meals)) {
?>
      <p class="empty-state">No meals planned yet.</p>
<?php
      return;
    }

    $totCals = 0; $totProtein = 0; $totCarbs = 0; $totFats = 0;
    foreach ($meals as $m) {
      $totCals    += $m->calories;
      $totProtein += $m->protein;
      $totCarbs   += $m->carbs;
      $totFats    += $m->fats;
    }
?>
    <div class="table-scroll">
      <table class="data-table">
        <thead>
          <tr>
            <th scope="col">Meal</th>
            <th scope="col">Calories</th>
            <th scope="col">Protein</th>
            <th scope="col">Carbs</th>
            <th scope="col">Fats</th>
            <?php if ($editable) { ?><th scope="col"><span class="visually-hidden">Actions</span></th><?php } ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($meals as $m) { ?>
            <tr class="meal-row">
              <td><?= htmlspecialchars($m->name, ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= (int)$m->calories ?> kcal</td>
              <td><?= (int)$m->protein ?>g</td>
              <td><?= (int)$m->carbs ?>g</td>
              <td><?= (int)$m->fats ?>g</td>
              <?php if ($editable) { ?>
                <td>
                  <details class="meal-edit">
                    <summary class="btn-outline btn-sm">Edit</summary>
                    <form class="admin-form" method="post" action="../actions/action_nutrition_meal.php">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="action" value="edit">
                      <input type="hidden" name="member_id" value="<?= (int)$memberId ?>">
                      <input type="hidden" name="meal_id" value="<?= (int)$m->mealId ?>">
                      <label class="form-field">
                        Meal name <span class="required-mark" aria-hidden="true">*</span>
                        <input type="text" name="name" required maxlength="80" value="<?= htmlspecialchars($m->name, ENT_QUOTES, 'UTF-8') ?>">
                      </label>
                      <label class="form-field">
                        Calories <span class="required-mark" aria-hidden="true">*</span>
                        <input type="number" name="calories" required min="0" value="<?= (int)$m->calories ?>">
                      </label>
                      <label class="form-field">
                        Protein (g) <span class="required-mark" aria-hidden="true">*</span>
                        <input type="number" name="protein" required min="0" value="<?= (int)$m->protein ?>">
                      </label>
                      <label class="form-field">
                        Carbs (g) <span class="required-mark" aria-hidden="true">*</span>
                        <input type="number" name="carbs" required min="0" value="<?= (int)$m->carbs ?>">
                      </label>
                      <label class="form-field">
                        Fats (g) <span class="required-mark" aria-hidden="true">*</span>
                        <input type="number" name="fats" required min="0" value="<?= (int)$m->fats ?>">
                      </label>
                      <div class="form-actions">
                        <button type="submit" class="btn-primary btn-sm">Save meal</button>
                      </div>
                    </form>
                  </details>
                  <form method="post" action="../actions/action_nutrition_meal.php" class="inline-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="member_id" value="<?= (int)$memberId ?>">
                    <input type="hidden" name="meal_id" value="<?= (int)$m->mealId ?>">
                    <button type="submit" class="btn-outline btn-sm" data-confirm="Remove this meal? This cannot be undone.">Remove</button>
                  </form>
                </td>
              <?php } ?>
            </tr>
          <?php } ?>
        </tbody>
        <tfoot>
          <tr class="meal-row meal-row-total">
            <td><strong>Total</strong></td>
            <td><strong><?= (int)$totCals ?> kcal</strong></td>
            <td><strong><?= (int)$totProtein ?>g</strong></td>
            <td><strong><?= (int)$totCarbs ?>g</strong></td>
            <td><strong><?= (int)$totFats ?>g</strong></td>
            <?php if ($editable) { ?><td></td><?php } ?>
          </tr>
        </tfoot>
      </table>
    </div>
<?php
  }

  /* ------------------------------------------------------------------ *
   *  Member: log today's consumption
   * ------------------------------------------------------------------ */
  function drawNutritionLogForm(NutritionPlan $plan, string $csrf) {
?>
    <section class="admin-section">
      <h2 class="dashboard-section-heading">Log today</h2>
      <p class="nutrition-target-hint">
        Target: <?= (int)$plan->dailyCalories ?> kcal &middot;
        <?= (int)$plan->protein ?>g protein &middot;
        <?= (int)$plan->carbs ?>g carbs &middot;
        <?= (int)$plan->fats ?>g fats
      </p>
      <form class="admin-form" method="post" action="../actions/action_nutrition_log.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="log">

        <label class="form-field">
          Calories <span class="required-mark" aria-hidden="true">*</span>
          <input type="number" name="calories" required min="0" max="20000" value="0">
        </label>
        <label class="form-field">
          Protein (g) <span class="required-mark" aria-hidden="true">*</span>
          <input type="number" name="protein" required min="0" value="0">
        </label>
        <label class="form-field">
          Carbs (g) <span class="required-mark" aria-hidden="true">*</span>
          <input type="number" name="carbs" required min="0" value="0">
        </label>
        <label class="form-field">
          Fats (g) <span class="required-mark" aria-hidden="true">*</span>
          <input type="number" name="fats" required min="0" value="0">
        </label>
        <label class="form-field span-full">
          Note
          <input type="text" name="note" maxlength="140" placeholder="Optional note about today's intake">
        </label>

        <div class="form-actions">
          <button type="submit" class="btn-primary">Save today's log</button>
        </div>
      </form>
    </section>
<?php
  }

  /* ------------------------------------------------------------------ *
   *  Member: recent logs comparing consumed vs target
   * ------------------------------------------------------------------ */
  function drawNutritionLogHistory(NutritionPlan $plan, array $logs) {
?>
    <section class="admin-section">
      <h2 class="dashboard-section-heading">Recent logs</h2>
      <?php if (empty($logs)) { ?>
        <p class="empty-state">No nutrition logged yet. Use the form above to record today's intake.</p>
      <?php } else { ?>
        <div class="table-scroll">
          <table class="data-table">
            <thead>
              <tr>
                <th scope="col">Date</th>
                <th scope="col">Calories</th>
                <th scope="col">Protein</th>
                <th scope="col">Carbs</th>
                <th scope="col">Fats</th>
                <th scope="col">Note</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($logs as $log) {
                $diff      = $log->calories - $plan->dailyCalories;
                $diffLabel = ($diff >= 0 ? '+' : '') . $diff . ' vs target';
                $diffClass = $diff > 0 ? 'nutrition-diff-over' : ($diff < 0 ? 'nutrition-diff-under' : 'nutrition-diff-on');
              ?>
                <tr>
                  <td><?= htmlspecialchars(formatDate($log->logDate), ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <?= (int)$log->calories ?> kcal
                    <span class="nutrition-diff <?= $diffClass ?>"><?= htmlspecialchars($diffLabel, ENT_QUOTES, 'UTF-8') ?></span>
                  </td>
                  <td><?= (int)$log->protein ?>g</td>
                  <td><?= (int)$log->carbs ?>g</td>
                  <td><?= (int)$log->fats ?>g</td>
                  <td><?= $log->note !== null ? htmlspecialchars($log->note, ENT_QUOTES, 'UTF-8') : '&mdash;' ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      <?php } ?>
    </section>
<?php
  }
?>
