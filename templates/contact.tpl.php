<?php declare(strict_types = 1); ?>

<?php function drawContactSection(array $info, string $csrf) { ?>
  <section class="contact-section">
    <div class="section__inner">
      <div class="contact-grid">

        <div>
          <h2 class="contact-info-heading">Membership enquiry.</h2>
          <p class="contact-info-body">If you have been referred by a member, mention them by name. If you have not, tell us a little about yourself &mdash; we read every word.</p>

          <ul class="contact-info-list">
            <?php foreach ($info as $row) { ?>
              <li class="contact-info-row">
                <p class="contact-info-label"><?= htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') ?></p>
                <p class="contact-info-value"><?= $row['value'] ?></p>
              </li>
            <?php } ?>
          </ul>
        </div>

        <?php drawContactForm($csrf); ?>

      </div>
    </div>
  </section>
<?php } ?>

<?php function drawContactForm(string $csrf) { ?>
  <form class="contact-form" action="../actions/action_enquiry.php" method="post" data-validate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

    <div class="form-row">
      <label class="form-field">
        First name <span class="required-mark" aria-hidden="true">*</span>
        <input type="text" id="first" name="first" required>
      </label>
      <label class="form-field">
        Last name <span class="required-mark" aria-hidden="true">*</span>
        <input type="text" id="last" name="last" required>
      </label>
    </div>

    <label class="form-field">
      Email <span class="required-mark" aria-hidden="true">*</span>
      <input type="email" id="email" name="email" required>
    </label>

    <label class="form-field">
      Phone (optional)
      <input type="tel" id="phone" name="phone">
    </label>

    <div class="form-row">
      <label class="form-field">
        Preferred location
        <select id="location" name="location">
          <option>New York</option>
          <option>Lisbon</option>
          <option>Porto</option>
          <option>Viseu</option>
        </select>
      </label>
      <label class="form-field">
        Plan of interest
        <select id="plan" name="plan">
          <option>Essential</option>
          <option>Premium</option>
          <option>Elite</option>
          <option selected>Not sure yet</option>
        </select>
      </label>
    </div>

    <label class="form-field">
      Referring member (if any)
      <input type="text" id="referrer" name="referrer" placeholder="Optional">
    </label>

    <label class="form-field">
      A little about you <span class="required-mark" aria-hidden="true">*</span>
      <textarea id="message" name="message" rows="4" required maxlength="600" data-counter="600"></textarea>
    </label>

    <button type="submit" class="btn--primary">Send enquiry</button>
    <p class="form-disclaimer">By submitting, you consent to Iron Haven contacting you in confidence about your enquiry. We do not share, sell, or publish any applicant information.</p>

  </form>

  <script src="../javascript/contact-plan.js" defer></script>

  <script src="../javascript/form-validate.js" defer></script>
<?php } ?>

<?php function drawLocations(array $locations) { ?>
  <section class="locations-section">
    <div class="section__inner">
      <p class="section__label">02 &mdash; Our Locations</p>
      <h2 class="section__heading">Four Cities. One Standard.</h2>

      <div class="locations-grid">
        <?php foreach ($locations as $loc) { ?>
          <article class="location-card">
            <figure class="img-real__wrap location-card__img">
              <img class="img-real" src="../images/<?= htmlspecialchars($loc['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($loc['alt'], ENT_QUOTES, 'UTF-8') ?>">
            </figure>
            <h3 class="location-name"><?= htmlspecialchars($loc['name'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="location-region"><?= htmlspecialchars($loc['region'], ENT_QUOTES, 'UTF-8') ?></p>
            <address class="location-address"><?= $loc['address'] ?></address>
            <dl class="location-hours">
              <?php foreach ($loc['hours'] as $row) { ?>
                <div class="location-hours__row">
                  <dt><?= htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') ?></dt>
                  <dd><?= htmlspecialchars($row['value'], ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
              <?php } ?>
            </dl>
          </article>
        <?php } ?>
      </div>
    </div>
  </section>
<?php } ?>
