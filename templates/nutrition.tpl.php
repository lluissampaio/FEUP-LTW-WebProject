<?php
  declare(strict_types = 1);

  function drawNutritionPlan(?NutritionPlan $plan, Session $session, ?User $targetUser = null) {
    $dietType      = $plan ? $plan->dietType : '';
    $dailyCalories = $plan ? $plan->dailyCalories : 2000;
    $protein       = $plan ? $plan->protein : 150;
    $carbs         = $plan ? $plan->carbs : 200;
    $fats          = $plan ? $plan->fats : 65;
    $guidelines    = $plan ? $plan->guidelines : '';

    $title = $targetUser ? htmlspecialchars($targetUser->firstName . '\'s Nutrition Plan', ENT_QUOTES, 'UTF-8') : 'Your Nutrition Plan';
    $subtitle = $targetUser ? 'Manage dietary goals and macros for your client.' : 'Manage your daily macros and dietary goals.';
?>
  <main class="dashboard-main">
    <header class="dashboard-header">
      <p class="dashboard-eyebrow">Nutrition</p>
      <h1 class="dashboard-heading"><?= $title ?></h1>
      <p class="dashboard-sub"><?= $subtitle ?></p>
    </header>

    <section class="admin-section">
      <form class="admin-form" method="post" action="../actions/action_save_nutrition.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <?php if ($targetUser) { ?>
          <input type="hidden" name="target_user_id" value="<?= (int)$targetUser->id ?>">
        <?php } ?>
        
        <div class="form-field">
          <label for="n-diet">Diet Type</label>
          <input type="text" id="n-diet" name="diet_type" required maxlength="60" placeholder="e.g. Balanced, Keto, Vegan" value="<?= htmlspecialchars($dietType, ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-field">
          <label for="n-cals">Daily Calories</label>
          <input type="number" id="n-cals" name="daily_calories" required min="0" max="10000" value="<?= $dailyCalories ?>">
        </div>

        <div class="form-field">
          <label for="n-protein">Protein (g)</label>
          <input type="number" id="n-protein" name="protein" required min="0" value="<?= $protein ?>">
        </div>

        <div class="form-field">
          <label for="n-carbs">Carbs (g)</label>
          <input type="number" id="n-carbs" name="carbs" required min="0" value="<?= $carbs ?>">
        </div>

        <div class="form-field">
          <label for="n-fats">Fats (g)</label>
          <input type="number" id="n-fats" name="fats" required min="0" value="<?= $fats ?>">
        </div>

        <div class="form-field" style="grid-column: 1 / -1;">
          <label for="n-guide">Guidelines / Notes</label>
          <textarea id="n-guide" name="guidelines" rows="6" placeholder="Any specific dietary restrictions, meal timing, or general notes..."><?= htmlspecialchars($guidelines, ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary">Save Nutrition Plan</button>
        </div>
      </form>
    </section>
  </main>
<?php
  }
?>
