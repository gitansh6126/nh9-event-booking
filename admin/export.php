<?php
// NH9 Event Booking — CSV export of all bookings + tickets
require __DIR__ . '/../src/bootstrap.php';
ensure_schema();
require_admin();

$C = $CONFIG;

$rows = db()->query('SELECT b.id, b.booking_ref, b.contact_name, b.contact_email,
                            b.ticket_qty, b.status, b.created_at,
                            t.attendee_name, t.attendee_email, t.ticket_code
                     FROM bookings b
                     JOIN tickets t ON t.booking_id = b.id
                     ORDER BY b.id, t.id')->fetchAll();

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="nh9-bookings-' . date('Ymd-His') . '.csv"');
header('Pragma: no-cache');

$out = fopen('php://output', 'w');
fputcsv($out, ['Booking Ref', 'Booked At', 'Contact Name', 'Contact Email', 'Qty', 'Status',
               'Attendee Name', 'Attendee Email', 'Ticket Code']);

foreach ($rows as $r) {
    fputcsv($out, [
        $r['booking_ref'], $r['created_at'], $r['contact_name'], $r['contact_email'],
        $r['ticket_qty'], $r['status'], $r['attendee_name'], $r['attendee_email'], $r['ticket_code'],
    ]);
}
fclose($out);
exit;