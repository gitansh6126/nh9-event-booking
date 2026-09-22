<?php
// NH9 Event Booking — confirmation screen (lists each named ticket + its code)
require __DIR__ . '/src/bootstrap.php';
ensure_schema();

$C = $CONFIG;
$ref = trim((string)($_GET['ref'] ?? ''));
$booking = $ref !== '' ? booking_by_ref($ref) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Booking confirmed · <?= e($C['event']['name']) ?></title>
<style>
  :root { --brand: <?= e($C['site']['brand_color']) ?>; }
  * { box-sizing: border-box; margin:0; padding:0; }
  body { font-family: system-ui, -apple-system, "Segoe UI", sans-serif; background:#f6f5fb; color:#222; }
  .wrap { max-width:620px; margin:0 auto; padding:36px 18px 60px; }
  .card { background:#fff; border:1px solid #e5e2f0; border-radius:14px; padding:26px; }
  .check { width:64px; height:64px; border-radius:50%; background:#e6f9ec; color:#0f6b37;
           display:flex; align-items:center; justify-content:center; font-size:2rem; margin-bottom:14px; }
  h1 { font-size:1.5rem; margin-bottom:6px; }
  .ref { font-weight:700; color:var(--brand); }
  table { width:100%; border-collapse:collapse; margin-top:16px; }
  th,td { text-align:left; padding:10px 8px; border-bottom:1px solid #efeaf8; font-size:.95rem; }
  .code { font-family:"Cascadia Code", monospace; font-weight:700; color:#0f6b37; background:#e6f9ec;
          padding:2px 8px; border-radius:6px; }
  .btn { display:inline-block; margin-top:22px; padding:12px 20px; color:#fff; background:var(--brand);
         border-radius:10px; text-decoration:none; font-weight:700; }
  footer { text-align:center; margin-top:30px; font-size:.82rem; color:#8a86a0; }
</style>
</head>
<body>
<div class="wrap">
<?php if (!$booking): ?>
  <div class="card"><h1>Not found</h1><p>We could not find that booking reference.</p>
  <a class="btn" href="index.php">Back to event</a></div>
<?php else: ?>
  <div class="card">
    <div class="check">✔</div>
    <h1>Booking confirmed!</h1>
    <p>Reference <span class="ref"><?= e($booking['booking_ref']) ?></span> ·
       booked by <?= e($booking['contact_name']) ?> ·
       <?= (int)$booking['ticket_qty'] ?> <?= e($C['event']['ticket_name']) ?><?= ((int)$booking['ticket_qty']) > 1 ? 's' : '' ?></p>
    <table>
      <tr><th>#</th><th>Attendee</th><th>Ticket code</th></tr>
      <?php $i = 1;
      foreach ($booking['tickets'] as $t): ?>
        <tr><td><?= $i++ ?></td>
            <td><?= e($t['attendee_name']) ?></td>
            <td><span class="code"><?= e($t['ticket_code']) ?></span></td></tr>
      <?php endforeach; ?>
    </table>
    <p style="font-size:.85rem;color:#6b677f;margin-top:12px">Keep this page / ticket codes — show them at the venue for entry. No payment is due.</p>
    <a class="btn" href="index.php">Back to event</a>
  </div>
  <footer>Organised by <?= e($C['site']['organizer']) ?></footer>
<?php endif; ?>
</div>
</body>
</html>