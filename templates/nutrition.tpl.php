<?php
  declare(strict_types = 1);

  function drawNutritionPlan(?NutritionPlan $plan, Session $session, ?User $targetUser = null) {
    $dietType      = $plan ? $plan->dietType : '';
    $dailyCalories = $plan ? $plan->dailyCalories : 2000;
    $protein       = $plan ? $plan->protein : 150;
    $carbs         = $plan ? $plan->carbs : 200;
    $fats          = $plan ? $plan->fats : 65;
    $guidelines    = $plan ? $plan->guidelines : '';
?>

    <section class="admin-section">
      <form class="admin-form" method="post" action="../actions/action_save_nutrition.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($session->csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <?php if ($targetUser) { ?>
          <input type="hidden" name="target_user_id" value="<?= (int)$targetUser->id ?>">
        <?php } ?>
        
        <label class="form-field">
          Diet Type
          <input type="text" id="n-diet" name="diet_type" required maxlength="60" placeholder="e.g. Balanced, Keto, Vegan" value="<?= htmlspecialchars($dietType, ENT_QUOTES, 'UTF-8') ?>">
        </label>

        <label class="form-field">
          Daily Calories
          <input type="number" id="n-cals" name="daily_calories" required min="0" max="10000" value="<?= htmlspecialchars((string)$dailyCalories, ENT_QUOTES, 'UTF-8') ?>">
        </label>

        <label class="form-field">
          Protein (g)
          <input type="number" id="n-protein" name="protein" required min="0" value="<?= htmlspecialchars((string)$protein, ENT_QUOTES, 'UTF-8') ?>">
        </label>

        <label class="form-field">
          Carbs (g)
          <input type="number" id="n-carbs" name="carbs" required min="0" value="<?= htmlspecialchars((string)$carbs, ENT_QUOTES, 'UTF-8') ?>">
        </label>

        <label class="form-field">
          Fats (g)
          <input type="number" id="n-fats" name="fats" required min="0" value="<?= htmlspecialchars((string)$fats, ENT_QUOTES, 'UTF-8') ?>">
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
?>
