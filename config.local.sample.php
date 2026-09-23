<?php
// LOCAL-ONLY configuration for docker-compose (mysql:8 runs on host port 3307).
// This file is gitignored AND only used when present. Copy from config.sample.php.
// Matches the values in docker-compose.yml:
//   DB host = "db" (container name), user = nh9, pass = nh9_secret, db = nh9_booking
return [
    'db' => [
        'host' => 'db',
        'port' => 3306,
        'name' => 'nh9_booking',
        'user' => 'nh9',
        'pass' => 'nh9_secret',
        'charset' => 'utf8mb4',
    ],

    'event' => [
        'name'            => 'NH9 Event 2026',
        'date'            => '2026-10-15',
        'time'            => '7:00 PM',
        'venue'           => 'Venue name & address here',
        'description'     => 'Join us for a one-time event. Book your ticket below.',
        'ticket_name'     => 'General Admission',
        'price_label'     => 'Free',
        'currency'        => '₹',
        'capacity'        => 500,
        'max_per_order'   => 10,
        'booking_open'    => true,
    ],

    'site' => [
        'brand_color' => '#7c3aed',
        'organizer'   => 'NH9 Events',
        'contact'     => 'contact@example.com',
    ],

    // Dev admin: admin / password  (canonical bcrypt hash for "password")
    'admin' => [
        'username' => 'admin',
        'password_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    ],

    'security' => [
        'csrf_secret' => 'LOCAL_DEV_ONLY_CHANGE_ME_9f1c2d3e4a5b',
    ],
];