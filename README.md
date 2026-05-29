# ltw12g06 — Iron Haven

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

**Extra:**
- [x] Personal training bookings, membership plans, automatic waitlist with promotion, in-app notifications, nutrition plans, and a member dispute/support channel.

## Running

    sqlite3 database/database.db < database/database.sql
    php -S localhost:9000

Then open <http://localhost:9000> (redirects to `pages/`).

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
