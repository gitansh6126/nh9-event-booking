# NH9 Events — HostGator Deployment Checklist
# =============================================

## Pre-Deployment (Local)
- [ ] Copy config.sample.php to config.php
- [ ] Fill in HostGator database credentials
- [ ] Generate admin password hash: php -r "echo password_hash('YOUR_PASSWORD', PASSWORD_BCRYPT);"
- [ ] Generate CSRF secret: php -r "echo bin2hex(random_bytes(32));"
- [ ] Run DEPLOY_check.php to verify everything is ready
- [ ] Set display_errors to 0 in config.php (or in .htaccess)

## HostGator Setup (hPanel)
- [ ] Log in to HostGator hPanel
- [ ] Create MySQL Database (hPanel → Databases → MySQL Databases)
- [ ] Create Database User
- [ ] Assign user to database (ALL PRIVILEGES)
- [ ] Note: Database name, username, password
- [ ] Enable SSL for your domain (hPanel → Security → SSL)
- [ ] Point your domain to HostGator nameservers

## File Upload (File Manager or FTP)
- [ ] Upload all files from booking-app/ to public_html/nh9-events/
- [ ] Upload widget.js to public_html/
- [ ] Ensure config.php has LIVE credentials (not sample)
- [ ] Set file permissions: 644 for files, 755 for folders
- [ ] Ensure .htaccess is uploaded correctly

## Post-Deployment Testing
- [ ] Visit yourdomain.com/nh9-events/ — Booking page loads
- [ ] Test booking flow with a test ticket
- [ ] Visit yourdomain.com/nh9-events/admin/ — Admin login works
- [ ] Test admin dashboard shows bookings
- [ ] Verify CSV export works
- [ ] Test confirmation page shows ticket codes
- [ ] Verify widget.js loads at yourdomain.com/widget.js
- [ ] Embed widget on a test page to confirm iframe works

## Security Hardening
- [ ] Set config.php permissions to 600 (or restrict via .htaccess)
- [ ] Verify .htaccess blocks direct access to config.php
- [ ] Disable directory listing (already in .htaccess)
- [ ] Set display_errors = 0 in production
- [ ] Change default admin username if desired
- [ ] Rotate CSRF secret periodically

## Customization (Post-Launch)
- [ ] Update event details in config.php
- [ ] Change brand_color hex code
- [ ] Adjust capacity and max_per_order
- [ ] Toggle booking_open true/false
- [ ] Add payment integration if needed
- [ ] Add email notifications
- [ ] Add custom fields to booking form

## NOT Needed for HostGator
- [ ] Docker setup
- [ ] Composer install
- [ ] Node.js / npm install
- [ ] Vite build
- [ ] Laravel artisan commands
- [ ] Supervisor configuration
- [ ] Nginx configuration
- [ ] WebSocket server
- [ ] Redis setup
