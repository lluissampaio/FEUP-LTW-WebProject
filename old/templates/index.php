<?php
$page_title       = 'Iron Haven — Private Members Gym';
$page_description = 'Iron Haven — Elite training and uncompromised privacy for private members.';
include __DIR__ . '/partials/header.php';
?>

  <main>

    <section class="hero" id="home" aria-labelledby="hero-heading">
      <div class="hero__inner">
        <p class="hero__eyebrow">New York · Lisbon · Porto · Viseu</p>
        <h1 class="hero__heading" id="hero-heading">The Haven<br>You Deserve.</h1>
        <p class="hero__sub">Elite training. Uncompromised privacy. A private membership for those who demand discretion
          and excellence.</p>
        <a href="register.php" class="btn btn--primary">Request Membership</a>
        <ul class="hero__locations" aria-label="Locations">
          <li class="hero__badge">New York</li>
          <li class="hero__badge">Lisbon</li>
          <li class="hero__badge">Porto</li>
          <li class="hero__badge">Viseu</li>
        </ul>
      </div>
    </section>

    <hr class="divider">

    <section class="stats" aria-label="Key numbers">
      <div class="section__inner">
        <dl class="stats__grid">
          <div class="stats__block">
            <dt class="stats__label">Locations</dt>
            <dd class="stats__number">4</dd>
          </div>
          <div class="stats__block">
            <dt class="stats__label">Members</dt>
            <dd class="stats__number">2,400+</dd>
          </div>
          <div class="stats__block">
            <dt class="stats__label">Weekly Classes</dt>
            <dd class="stats__number">60+</dd>
          </div>
          <div class="stats__block">
            <dt class="stats__label">Elite Trainers</dt>
            <dd class="stats__number">30+</dd>
          </div>
        </dl>
      </div>
    </section>

    <section class="services" id="services" aria-labelledby="services-heading">
      <div class="section__inner">
        <p class="section__label">02 — Facilities</p>
        <h2 class="section__heading" id="services-heading">World-Class Facilities</h2>
        <div class="services__grid">

          <article class="service-card">
            <p class="service-card__number">01</p>
            <h3 class="service-card__title">Private Training Suites</h3>
            <p class="service-card__desc">Dedicated one-on-one spaces equipped for every programme. Fully private, fully
              equipped — designed around you and your coach alone.</p>
            <a href="classes.php" class="service-card__link">Explore suites</a>
          </article>

          <article class="service-card">
            <p class="service-card__number">02</p>
            <h3 class="service-card__title">Recovery &amp; Spa</h3>
            <p class="service-card__desc">Cold plunge, infrared sauna, contrast therapy, and on-demand sports massage.
              Recovery is not optional — it is part of the programme.</p>
            <a href="classes.php" class="service-card__link">Explore recovery</a>
          </article>

          <article class="service-card">
            <p class="service-card__number">03</p>
            <h3 class="service-card__title">Members Lounge</h3>
            <p class="service-card__desc">A curated social space for our community. No cameras, no outsiders. Connect
              with peers who share your standard — entirely off the record.</p>
            <a href="classes.php" class="service-card__link">Explore the lounge</a>
          </article>

        </div>
      </div>
    </section>

    <section class="testimonials" aria-labelledby="testimonials-heading">
      <div class="section__inner">
        <p class="section__label">03 — Members</p>
        <h2 class="section__heading" id="testimonials-heading">What Our Members Say</h2>
        <div class="testimonials__grid">

          <figure class="testimonial">
            <blockquote class="testimonial__quote">
              Iron Haven is the only place I can train without thinking about who is watching. The level of discretion
              here is unmatched anywhere in New York.
            </blockquote>
            <figcaption class="testimonial__author">J.M. — New York Member</figcaption>
          </figure>

          <figure class="testimonial">
            <blockquote class="testimonial__quote">
              The trainers are exceptional and the environment is unlike any gym I have used globally. It feels more
              like a private club than a fitness centre.
            </blockquote>
            <figcaption class="testimonial__author">A.R. — Lisbon Member</figcaption>
          </figure>

          <figure class="testimonial">
            <blockquote class="testimonial__quote">
              I joined for the training. I stayed for the community. Iron Haven has connected me with people I would
              never have met elsewhere — all in complete privacy.
            </blockquote>
            <figcaption class="testimonial__author">C.F. — Porto Member</figcaption>
          </figure>

        </div>
      </div>
    </section>

    <section class="pricing" id="pricing" aria-labelledby="pricing-heading">
      <div class="section__inner">
        <p class="section__label">04 — Membership</p>
        <h2 class="section__heading" id="pricing-heading">Choose Your Plan</h2>
        <div class="pricing__grid">

          <article class="pricing-card">
            <p class="pricing-card__label">Plan 01</p>
            <h3 class="pricing-card__name">Essential</h3>
            <p class="pricing-card__price">450€</p>
            <p class="pricing-card__period">per month</p>
            <ul class="pricing-card__features">
              <li>Full gym floor access</li>
              <li>Group classes (60+ weekly)</li>
              <li>Locker &amp; towel service</li>
              <li>Access to one location</li>
              <li>Member app access</li>
            </ul>
            <a href="register.php" class="btn btn--outline">Apply for Essential</a>
          </article>

          <article class="pricing-card pricing-card--featured">
            <p class="pricing-card__label">Plan 02 — Most Popular</p>
            <h3 class="pricing-card__name">Premium</h3>
            <p class="pricing-card__price">950€</p>
            <p class="pricing-card__period">per month</p>
            <ul class="pricing-card__features">
              <li>Everything in Essential</li>
              <li>4 personal training sessions</li>
              <li>Recovery &amp; spa access</li>
              <li>Multi-location access</li>
              <li>Guest privileges (2/month)</li>
              <li>Priority class booking</li>
            </ul>
            <a href="register.php" class="btn btn--primary">Apply for Premium</a>
          </article>

          <article class="pricing-card">
            <p class="pricing-card__label">Plan 03</p>
            <h3 class="pricing-card__name">Elite</h3>
            <p class="pricing-card__price">2,400€</p>
            <p class="pricing-card__period">per month</p>
            <ul class="pricing-card__features">
              <li>Everything in Premium</li>
              <li>Unlimited personal training</li>
              <li>Private suite reservation</li>
              <li>Dedicated concierge</li>
              <li>All-location global access</li>
              <li>Invite-only members events</li>
              <li>Nutritionist consultation</li>
            </ul>
            <a href="register.php" class="btn btn--outline">Apply for Elite</a>
          </article>

        </div>
      </div>
    </section>

  </main>

<?php include __DIR__ . '/partials/footer.php'; ?>
