
# Costal-villa
# SeaGlass Villa Booking System

A responsive PHP and MySQL booking application for a fictional coastal villa in Mirissa, Sri Lanka. It demonstrates guest registration and login, room discovery, date-based availability, booking requests, guest booking management, and administrator room and reservation management.

## Features

- Browse active rooms and filter by stay dates and guest count.
- Register, log in, log out, and view your own bookings.
- Submit a booking request with a server-calculated price estimate.
- Cancel a pending booking.
- Manage room listings and booking status in the administrator area.
- Block overlapping confirmed reservations when searching and again when an administrator confirms a request.
- Use PDO prepared statements, password hashing, CSRF tokens, session role checks, and output escaping.
- Responsive layouts and client-side date, occupancy, and cost feedback.

## Requirements

- PHP 8.1 or later with PDO MySQL and mbstring enabled.
- MySQL 8.0 or MariaDB 10.5 or later.
- XAMPP, WAMP, MAMP, or another local PHP/MySQL stack.

## Install with XAMPP

1. Copy the `coastal-villa` folder into XAMPP's `htdocs` directory.
2. Start Apache and MySQL.
3. Open phpMyAdmin, import `db_structure.sql`, or run it with the MySQL command line.
4. If your database credentials differ from the defaults, set the `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` environment variables before starting Apache. Defaults are `127.0.0.1`, `coastal_villa`, `root`, and an empty password.
5. From the project folder, run `php seed_demo_accounts.php` once. This creates the demo accounts with PHP's `password_hash()` and can also be run again safely.
6. Visit `http://localhost/coastal-villa/`.

Example database import:

```sh
mysql -u root -p < db_structure.sql
php seed_demo_accounts.php
```

## Demo logins

Run the seed command first.

- Administrator: `admin@seaglass.lk` / `CoastAdmin!2026`
- Guest: `guest@example.com` / `GuestStay!2026`

Change these sample passwords before using the application beyond a classroom demonstration. Never publish the demo credentials on a live site.

## Main pages

- `index.php` - home page, rooms, and availability search
- `room.php` - room details
- `register.php`, `login.php`, `logout.php` - guest account access
- `book.php` - authenticated booking request
- `my-bookings.php` - guest reservation list and pending cancellation
- `admin/index.php` - reservation management dashboard
- `admin/rooms.php` - room create, update, and deactivate actions

## Database

`db_structure.sql` creates three connected tables: `users`, `rooms`, and `bookings`. The SQL file seeds fictional room listings. The PHP seed script creates a sample administrator and guest with secure hashes. Booking rows use foreign keys and indexed date/status fields.

## Security and production notes

This is an educational local application. It uses prepared queries, output escaping, CSRF protection, secure password hashes, role checks, and booking ownership checks. For a public deployment, configure HTTPS, secure cookies, environment-based secrets, backups, rate limiting, email verification, and stronger operational monitoring. Payment processing is intentionally out of scope.

## Project report

See `project_report.pdf` for the concise overview, database relationship diagram, feature summary, and interface previews. The report previews are illustrative layouts based on the implemented interface; replace them with screenshots from your own local XAMPP run before final academic submission. The included application has not been executed in this authoring environment because PHP and MySQL are not installed here.

## Generative AI declaration

Generative AI was used to help draft the project structure, interface copy, and code. The student should review, run, adapt, and be prepared to explain every submitted file, and should disclose assistance according to the course rules.
>>>>>>> 4561db6 (Initial Sea glass Villa Project)
