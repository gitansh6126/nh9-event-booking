<?php
// NH9 Event Booking — handles the booking POST (capacity-safe, one row per ticket)
require __DIR__ . '/src/bootstrap.php';
ensure_schema();
csrf_check();

$C = $CONFIG;

if (!$C['event']['booking_open']) {
    set_flash('error', 'Booking is closed.');
    header('Location: index.php');
    exit;
}

$contactName  = trim((string)($_POST['contact_name'] ?? ''));
$contactEmail = trim((string)($_POST['contact_email'] ?? ''));
$names    = $_POST['attendee_name'] ?? [];
$emails   = $_POST['attendee_email'] ?? [];

// Build attendee list and validate
$attendees = [];
foreach ($names as $i => $n) {
    $name = trim((string)$n);
    if ($name === '') {
        continue; // ignore empty rows
    }
    $attendees[] = [
        'name'  => mb_substr($name, 0, 120),
        'email' => isset($emails[$i]) ? mb_substr(trim((string)$emails[$i]), 0, 191) : null,
    ];
}

// Trust row count over the hidden qty field (no mismatch / tamper)
$qty = count($attendees);

if ($contactName === '') {
    set_flash('error', 'Please enter your name.');
    header('Location: index.php');
    exit;
}
if ($qty < 1) {
    set_flash('error', 'Please add at least one attendee name.');
    header('Location: index.php');
    exit;
}
if ($qty > (int)$C['event']['max_per_order']) {
    set_flash('error', 'Maximum ' . $C['event']['max_per_order'] . ' tickets per booking.');
    header('Location: index.php');
    exit;
}

try {
    $result = create_booking($contactName, $contactEmail, $attendees);
    header('Location: confirm.php?ref=' . urlencode($result['ref']));
    exit;
} catch (RuntimeException $e) {
    set_flash('error', $e->getMessage());
    header('Location: index.php');
    exit;
} catch (Throwable $e) {
    error_log('[NH9 booking] ' . $e->getMessage());
    set_flash('error', 'Something went wrong saving your booking. Please try again.');
    header('Location: index.php');
    exit;
}