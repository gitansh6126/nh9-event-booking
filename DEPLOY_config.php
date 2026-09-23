<?php
// NH9 Event Booking — DEPLOYMENT GUIDE FOR HOSTGATOR
// ================================================
// Copy this file to config.php and fill in your details.
// This file MUST be uploaded to the server.

return [
    'db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'name'     => 'YOUR_HOSTGATOR_DB_NAME',
        'user'     => 'YOUR_HOSTGATOR_DB_USER',
        'pass'     => 'YOUR_HOSTGATOR_DB_PASSWORD',
        'charset'  => 'utf8mb4',
    ],

    'event' => [
        'name'          => 'NH9 Event 2026',
        'date'          => '2026-10-15',
        'time'          => '7:00 PM',
        'venue'         => 'Venue name & address here',
        'description'   => 'Join us for a one-time event. Book your ticket below.',
        'ticket_name'   => 'General Admission',
        'price_label'   => 'Free',
        'currency'      => '₹',
        'capacity'      => 500,
        'max_per_order' => 10,
        'booking_open'  => true,
    ],

    'site' => [
        'brand_color' => '#7c3aed',
        'organizer'   => 'NH9 Events',
        'contact'     => 'contact@example.com',
    ],

    'admin' => [
        'username'         => 'admin',
        'password_hash'    => 'CHANGE_ME_BCRYPT_HASH',
    ],

    'security' => [
        'csrf_secret' => 'CHANGE_ME_LONG_RANDOM_STRING',
    ],
];
