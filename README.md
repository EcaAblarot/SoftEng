# SoftEng — Enrollment System (local dev)

This is a small PHP enrollment/dashboard project (no framework). The app uses MySQL and expects a database named `enrollment_db` with a `users` table.

Quick run instructions (Windows / PowerShell)

1. Ensure prerequisites are installed:
	- PHP 7.4+ (php -v)
	- MySQL (or use XAMPP which bundles Apache + MySQL + phpMyAdmin)

2. Initialize the database (recommended):
	- From MySQL client or phpMyAdmin, run the SQL in `init.sql` which creates the database, table, and a sample user (`testuser` / `TestPass123`).

3. Start the built-in PHP server for quick testing:
	```powershell
	cd C:\Projects\PHP\SoftEng
	php -S localhost:8000 -t .
	```
	Then open http://localhost:8000/login.php

4. Or use XAMPP/Apache:
	- Copy the repository folder into `C:\xampp\htdocs\SoftEng` (or configure a vhost)
	- Start Apache & MySQL from XAMPP Control Panel
	- Visit http://localhost/SoftEng/login.php

Security notes / Improvements
- DB credentials are currently `root` with an empty password in `db.php`. Create a dedicated DB user for this app and do not use root in production.
- `login.php` and `forgot_password.php` now use prepared statements, but additional hardening and validation are recommended.
- Remove display_errors in production and enable HTTPS when exposing the app externally.

Files added/changed of interest
- `init.sql` — creates DB + users table + a sample user.
- `login.php`, `forgot_password.php` — updated to use prepared statements and safer session handling.

If you'd like, I can:
- Create a small `install.ps1` PowerShell script to run the SQL automatically against a local MySQL instance.
- Add a `logout.php` endpoint and CSRF protection.
- Add simple PHPUnit tests for PHP helper functions (requires setup).

If you want me to proceed with any of the above, tell me which one and I'll implement it.
