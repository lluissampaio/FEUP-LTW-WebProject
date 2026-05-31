# ltw12g06 — Iron Haven

A gym management site built with plain PHP 8.4 and SQLite (PDO). No
frameworks — the only third-party resource is the Google Fonts CDN.

## Features

**All users:**
- [x] Register a new account.
- [x] Log in and out.
- [x] Edit their profile, including name, username, password, and profile photo.

**Members:**
- [x] Browse the schedule of available fitness classes, filtering by type, trainer, day, or time.
- [x] Enroll in and cancel enrollment from upcoming classes, subject to capacity limits.
- [x] View trainer profiles, including their specializations and the classes they teach.
- [x] Check the current availability of equipment in the main training area.
- [x] Leave ratings and reviews for classes they have attended.

**Trainers:**
- [x] Manage their public profile, including bio, specializations, and certifications.
- [x] View the roster of members enrolled in their classes.
- [x] Track and manage their assigned class schedule.

**Admins:**
- [x] Manage members and trainers (create, update, and deactivate accounts).
- [x] Manage the class catalog (create, edit, and remove classes) and assign trainers to them.
- [x] Manage equipment in the main training area (add, update availability status, and remove items).
- [x] Elevate a user to admin status.
- [x] Oversee and ensure the smooth operation of the entire system.

**Extras:**
- [x] Personal training bookings — members book one-to-one sessions against a
      trainer's availability, subject to their plan's PT session limit.
- [x] Membership plans — members apply for a plan, an admin approves the
      application, and the member then subscribes to it.
- [x] Class waitlist with automatic promotion — when a class is full, members
      join a waitlist; a cancelled enrollment auto-promotes the next person.
- [x] In-app notifications, with best-effort email delivery (each delivered
      notification is flagged via `Notification.Emailed`).
- [x] Trainer and admin analytics dashboards, with hand-rolled `<canvas>`
      charts (no charting library).
- [x] Nutrition plans with per-meal breakdowns and daily calorie/macro logging.
- [x] Disputes & feedback channel — members raise disputes, admins respond.
- [x] REST-style JSON API (`api/api_schedule.php`, `api/api_trainers.php`,
      `api/api_equipment.php`, `api/api_enroll.php`).
- [x] Equipment reservations — members reserve a piece of equipment for a date
      and time slot, with per-slot capacity enforced in code against
      `Equipment.Quantity`.
- [x] Promotional features — featured classes, trainers, and plans surfaced on
      the home page (`Featured` flags).
- [x] Member fitness goals with progress charts on the member progress page.

## Running

The canonical database is built from `database/database.sql`, which contains
the full schema plus seed data. The committed `database/database.db` is not
required: `database/connection.db.php` builds the database from
`database.sql` on first connection if `database.db` is missing or empty.
It also self-heals — on every connection it checks for any expected table
that is missing and recreates just those tables (data-preserving; it never
runs the `DROP`/`INSERT` statements over existing data). This means a stale
database built before newer tables were added will not produce 500s.

To build the database explicitly and start the server:

    sqlite3 database/database.db < database/database.sql
    php -S localhost:8000 router.php

The `router.php` script is the dev-server router: it serves real files as-is
and renders a branded 404 page for unknown URLs (without it, `php -S` would
fall back to the homepage for mistyped paths). You can still run plain
`php -S localhost:8000`; you just lose proper 404s.

Then open <http://localhost:8000> (redirects to `pages/`, i.e.
`pages/index.php`).

## Credentials

(the password is the same for every seeded account: `password`)

- `admin` / `password` — administrator
- `jmiller` / `password` — member
- `apatel` / `password` — trainer

Login accepts either the username or the email address
(e.g. `admin@ironhaven.com`, `jordan.miller@example.com`).

## Authors — ltw12g06

- José Lopes — up202406360
- Francisco Mendes — up202407152
- Luís Sampaio — up202404078
