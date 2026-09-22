<?php
// NH9 Event Booking — configuration
// Copy this file to config.php and fill in your Hostinger details.
// DB: create in hPanel > Databases > MySQL Databases FIRST, then paste the details here.

return [
    'db' => [
        'host' => 'localhost',              // Hostinger: usually 'localhost'
        'port' => 3306,
        'name' => 'u123456789_nh9',         // e.g. u123456789_nh9
        'user' => 'u123456789_nh9',
        'pass' => 'CHANGE_ME',
        'charset' => 'utf8mb4',
    ],

    'event' => [
        'name'            => 'NH9 Event 2026',
        'date'            => '2026-10-15',
        'time'            => '7:00 PM',
        'venue'           => 'Venue name & address here',
        'description'     => 'Join us for a one-time event. Book your ticket below.',
        'ticket_name'     => 'General Admission',
        'price_label'     => 'Free',                     // shown on the button
        'currency'        => '₹',
        'capacity'        => 500,                        // total tickets
        'max_per_order'   => 10,                         // max quantity per booking
        'booking_open'    => true,                       // false = sales closed
    ],

    'site' => [
        'brand_color' => '#7c3aed',                       // accent colour (hex)
        'organizer'   => 'NH9 Events',
        'contact'     => 'contact@example.com',           // footer contact
    ],

    // Admin login. Generate a SCALED password hash:
    //   php -r "echo password_hash('YOUR_PASSWORD', PASSWORD_BCRYPT);"
    'admin' => [
        'username' => 'admin',
        'password_hash' => 'CHANGE_ME',   // put the bcrypt hash here
    ],

    'security' => [
        'csrf_secret' => 'CHANGE_ME_LONG_RANDOM_STRING',
    ],
];