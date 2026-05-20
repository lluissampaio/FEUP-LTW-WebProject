<?php
$page_title       = 'Iron Haven — Dashboard';
$page_description = 'Your Iron Haven member dashboard.';
$body_class       = 'dashboard-page';
include __DIR__ . '/partials/header.php';
?>

  <aside class="sidebar" aria-label="Member sidebar">
    <header class="profile">
      <div class="profile__photo" aria-hidden="true"></div>
      <p class="profile__name">Member Name</p>
      <p class="profile__since">Member since 2026</p>
    </header>

    <nav class="sidebar__nav" aria-label="Dashboard navigation">
      <ul class="sidebar__list">
        <li><a href="#dashboard" class="sidebar__link sidebar__link--active">Dashboard</a></li>
        <li><a href="#workouts" class="sidebar__link">Workout History</a></li>
        <li><a href="#nutrition" class="sidebar__link">Nutrition Plan</a></li>
        <li><a href="#account" class="sidebar__link">Manage Account</a></li>
        <li>
          <button type="button" class="sidebar__link sidebar__link--signout">Sign Out</button>
        </li>
      </ul>
    </nav>
  </aside>

  <main class="dashboard" aria-labelledby="dashboard-heading">

    <header class="dashboard__header">
      <h1 id="dashboard-heading">Welcome back</h1>
      <p>Today is a great day to train.</p>
    </header>

    <section class="dashboard__stats" aria-label="Your statistics">

      <dl class="stat-card">
        <dt class="stat-card__label">Total Workouts</dt>
        <dd class="stat-card__value">42</dd>
        <dd class="stat-card__note">This month: +5 vs last month</dd>
      </dl>

      <dl class="stat-card">
        <dt class="stat-card__label">Active Time</dt>
        <dd class="stat-card__value">38h</dd>
        <dd class="stat-card__note">Average 1h per session</dd>
      </dl>

      <dl class="stat-card">
        <dt class="stat-card__label">Estimated Calories</dt>
        <dd class="stat-card__value">18,200</dd>
        <dd class="stat-card__note">Goal: 80% complete</dd>
      </dl>

      <dl class="stat-card">
        <dt class="stat-card__label">Weekly Frequency</dt>
        <dd class="stat-card__value">4 / 7</dd>
        <dd class="stat-card__note">days this week</dd>
      </dl>

    </section>

    <section class="dashboard__details" aria-label="Progress and activity">

      <section class="chart" aria-labelledby="chart-heading">
        <h2 class="chart__title" id="chart-heading">Progress Over Time</h2>
        <figure class="chart__figure">
          <canvas class="chart__canvas" id="evolutionChart" role="img" aria-label="Workout progress chart">
            Your progress chart is currently unavailable.
          </canvas>
          <figcaption class="sr-only">Workout progress over time</figcaption>
        </figure>
      </section>

      <section class="activity" aria-labelledby="activity-heading">
        <h2 class="activity__title" id="activity-heading">Recent Activity</h2>
        <div class="activity__scroll">
          <table class="activity__table">
            <caption class="sr-only">Recent workout activity</caption>
            <thead>
              <tr>
                <th scope="col">Date</th>
                <th scope="col">Workout Type</th>
                <th scope="col">Duration</th>
                <th scope="col">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>22/10/2026</td>
                <td>Lower Body</td>
                <td>1h 10min</td>
                <td><span class="tag tag--complete">Completed</span></td>
              </tr>
              <tr>
                <td>20/10/2026</td>
                <td>Mobility</td>
                <td>45min</td>
                <td><span class="tag tag--complete">Completed</span></td>
              </tr>
              <tr>
                <td>18/10/2026</td>
                <td>Upper Body</td>
                <td>1h 05min</td>
                <td><span class="tag tag--pending">Pending</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </section>
  </main>

<?php include __DIR__ . '/partials/footer.php'; ?>
