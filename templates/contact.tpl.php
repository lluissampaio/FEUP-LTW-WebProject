<?php declare(strict_types = 1); ?>

<?php function drawContactSection(array $info) { ?>
  <section class="contact-section">
    <div class="section-inner">
      <div class="contact-grid">

        <div>
          <h2 class="contact-info-heading">Membership enquiry.</h2>
          <p class="contact-info-body">If you have been referred by a member, mention them by name. If you have not, tell us a little about yourself &mdash; we read every word.</p>

          <div class="contact-info-list">
            <?php foreach ($info as $row) { ?>
              <div class="contact-info-row">
                <p class="contact-info-label"><?= htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') ?></p>
                <p class="contact-info-value"><?= $row['value'] ?></p>
              </div>
            <?php } ?>
          </div>
        </div>

        <?php drawContactForm(); ?>

      </div>
    </div>
  </section>
<?php } ?>

<?php function drawContactForm() { ?>
  <form class="contact-form" action="../actions/action_enquiry.php" method="post" data-validate>

    <div class="form-row">
      <div class="form-field">
        <label for="first">First name</label>
        <input type="text" id="first" name="first" required>
      </div>
      <div class="form-field">
        <label for="last">Last name</label>
        <input type="text" id="last" name="last" required>
      </div>
    </div>

    <div class="form-field">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>
    </div>

    <div class="form-field">
      <label for="phone">Phone (optional)</label>
      <input type="tel" id="phone" name="phone">
    </div>

    <div class="form-row">
      <div class="form-field">
        <label for="location">Preferred location</label>
        <select id="location" name="location">
          <option>New York</option>
          <option>Lisbon</option>
          <option>Porto</option>
          <option>Viseu</option>
        </select>
      </div>
      <div class="form-field">
        <label for="plan">Plan of interest</label>
        <select id="plan" name="plan">
          <option>Essential</option>
          <option>Premium</option>
          <option>Elite</option>
          <option>Not sure yet</option>
        </select>
      </div>
    </div>

    <div class="form-field">
      <label for="referrer">Referring member (if any)</label>
      <input type="text" id="referrer" name="referrer" placeholder="Optional">
    </div>

    <div class="form-field">
      <label for="message">A little about you</label>
      <textarea id="message" name="message" rows="4" required data-counter="600"></textarea>
    </div>

    <button type="submit" class="btn-primary">Send enquiry</button>
    <p class="form-disclaimer">By submitting, you consent to Iron Haven contacting you in confidence about your enquiry. We do not share, sell, or publish any applicant information.</p>

  </form>

  <script src="../javascript/form-validate.js" defer></script>
<?php } ?>

<?php function drawLocations(array $locations) { ?>
  <section class="locations-section">
    <div class="section-inner">
      <p class="section-label">02 &mdash; Our Locations</p>
      <h2 class="section-heading">Four Cities. One Standard.</h2>

      <div class="locations-grid">
        <?php foreach ($locations as $loc) { ?>
          <article class="location-card">
            <div class="img-real-wrap location-card-img">
              <img class="img-real" src="../images/<?= htmlspecialchars($loc['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($loc['alt'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <h3 class="location-name"><?= htmlspecialchars($loc['name'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="location-region"><?= htmlspecialchars($loc['region'], ENT_QUOTES, 'UTF-8') ?></p>
            <address class="location-address"><?= $loc['address'] ?></address>
            <dl class="location-hours">
              <?php foreach ($loc['hours'] as $row) { ?>
                <div class="location-hours-row">
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
