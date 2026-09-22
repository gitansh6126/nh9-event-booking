# NH9 Event Booking — Deploy to Hostinger Web Hosting

Always-on, no VPS, no Render. Runs on the existing Hostinger web hosting plan.

## Files (in this folder)
```
booking-app/
├── index.php          → public event page (quantity + name per ticket)
├── book.php           → saves the booking (capacity-safe, 1 row per ticket)
├── confirm.php        → confirmation screen with one ticket code per person
├── config.sample.php  → CONFIG TEMPLATE (copy to config.php, fill in values)
├── admin/
│   ├── index.php      → admin login + dashboard (bookings + tickets)
│   └── export.php     → CSV export of all bookings
└── src/bootstrap.php  → DB connection + schema (auto-creates tables)
```

## Step 1 — Create the MySQL database (hPanel)
1. Login → **hPanel → Databases → MySQL Databases**
2. Create database (e.g. `nh9_booking`). Hostinger generates the user automatically —
   note the full name, e.g. `u123456789_nh9booking` and its password.
3. Save those 4 values (host = `localhost`, user, pass, db name)

## Step 2 — Prepare config.php locally
1. Copy `config.sample.php` → `config.php`
2. Fill in DB details (from Step 1)
3. Edit `event` settings (name, date, venue, capacity, max per order)
4. Generate a bcrypt hash for the admin password:
   - If SSH/terminal available: `php -r "echo password_hash('MYPASSWORD', PASSWORD_BCRYPT), PHP_EOL;"`
   - If not, use any online bcrypt generator or `htpasswd -bnBC 10 "" PASS | tr -d ':\n'`
   - Paste hash into `admin.password_hash`
5. Set `security.csrf_secret` to a long random string
6. Set `site.brand_color` to the NH9 colour (optional)

> ⚠️ `config.php` contains credentials. Recommended layout keeps it OUT of the public root
> (Option A below). If you just upload everything into `public_html/` (Option B), make sure
> Hostinger's PHP protection is on (default) so `.php` files are executed, not downloaded —
> config.php is readable only if PHP is misconfigured, but Option A is still safer.

## Step 3 — Upload files
Upload the contents of `booking-app/` into your site's document root so `index.php`
sits at the root, for example:
```
public_html/                      ← document root
├── index.php                     → https://yourdomain.com/
├── book.php
├── confirm.php
├── config.php                    ← sits with the app (see note below)
├── admin/
│   ├── index.php                 → https://yourdomain.com/admin/
│   └── export.php
└── src/
    └── bootstrap.php
```
Prefer a subfolder if the domain already hosts a site:
```
public_html/event/index.php       → https://yourdomain.com/event/
```
That works unchanged — all `require` paths are relative to the app folder.

> ⚠️ `config.php` contains credentials. Direct access to `config.php`
> merely executes a `return` (blank output) — no leak. For extra safety, add a
> `.htaccess` in the app root:
> ```
> <Files "config.php"> Require all denied </Files>
> ```
> (Hostinger runs Apache → works.) Keep config.php inside the app folder; the code
> loads it via `require __DIR__ . '/../config.php'` from `src/` and expects it there.

## Step 4 — PHP version + settings (hPanel)
- **hPanel → Websites → your site → Advanced → PHP Configuration**
- Set **PHP version 8.1 or later** (8.2/8.3 preferred)
- Extensions needed: `pdo_mysql`, `mbstring`, `openssl` (all enabled by Hostinger by default)

## Step 5 — Test (public)
1. Open `https://yourdomain.com/event/` (or root)
2. Book 2 tickets → enters 2 names → Confirm
3. Confirmation shows ref + 2 ticket codes
4. Tables `bookings` and `tickets` are created automatically on first load

## Step 6 — Admin
- Open `https://yourdomain.com/event/admin/`
- Login with the username/hash created in Step 2
- Verify booking + tickets, download CSV

## Maintenance
- **Backup:** hPanel → Files → Backup (or phpMyAdmin → Export) before/after real event
- **Logs:** errors write to PHP error log (check hPanel → Logs) — `display_errors` is on in
  `bootstrap.php`; set to `0` in production
- **Sold out behaviour:** automatic when capacity is reached; set `event.booking_open = false`
  to stop booking manually after the event

## Data model (DB proof)
- `bookings` — one row per order (contact, qty, ref, status)
- `tickets` — one row PER attendee (name, email, ticket_code) → FK to bookings
- Capacity enforced with `SELECT ... FOR UPDATE` inside a transaction → no overbooking