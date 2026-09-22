<?php
// NH9 Event Booking — public landing page (single ticket type, qty -> per-ticket names)
require __DIR__ . '/src/bootstrap.php';
ensure_schema();

$C = $CONFIG;
$sold = tickets_sold();
$left = max(0, (int)$C['event']['capacity'] - $sold);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($C['event']['name']) ?></title>
<style>
  :root { --brand: <?= e($C['site']['brand_color']) ?>; }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: system-ui, -apple-system, "Segoe UI", sans-serif; background:#f6f5fb; color:#222; }
  .wrap { max-width: 720px; margin: 0 auto; padding: 32px 18px 60px; }
  .hero { background: linear-gradient(135deg, var(--brand), #4c1d95); color:#fff;
          border-radius: 18px; padding: 36px 30px; margin-bottom: 26px; }
  .hero h1 { font-size: 1.9rem; margin-bottom: 8px; }
  .hero .meta { opacity: .92; line-height: 1.7; }
  .card { background:#fff; border:1px solid #e5e2f0; border-radius:14px; padding:26px; margin-top:18px; }
  label { font-weight:600; font-size:.9rem; display:block; margin: 14px 0 6px; }
  input[type=text], input[type=email] { width:100%; padding:11px 12px; font-size:1rem;
          border:1px solid #cfcae6; border-radius:8px; }
  input:focus { outline:2px solid var(--brand); outline-offset:1px; border-color:transparent; }
  select { width:100%; padding:11px 12px; font-size:1rem; border:1px solid #cfcae6; border-radius:8px;
          background:#fff; }
  .qty-row { display:flex; align-items:center; gap:16px; flex-wrap:wrap; }
  .ticket-block { border:1px dashed #cfcae6; border-radius:10px; padding:14px 16px; margin-top:14px;
          background:#faf9ff; }
  .ticket-block .lbl { font-weight:700; color: var(--brand); }
  .btn { display:inline-block; width:100%; margin-top:24px; padding:14px; font-size:1.05rem;
          font-weight:700; color:#fff; background: var(--brand); border:0; border-radius:10px;
          cursor:pointer; }
  .btn:hover { filter: brightness(1.07); }
  .btn:disabled { background:#b4aee0; cursor:not-allowed; }
  .note { font-size:.85rem; color:#6b677f; margin-top:10px; text-align:center; }
  .alert { border-radius:10px; padding:12px 14px; margin-bottom:14px; font-size:.93rem; }
  .success { background:#e6f9ec; color:#0f6b37; border:1px solid #bfe6cd; }
  .error { background:#fdeaea; color:#a3162f; border:1px solid #f3c0c8; }
  footer { text-align:center; margin-top:34px; font-size:.82rem; color:#8a86a0; }
  footer a { color: var(--brand); }
  .rc { display:flex; gap:10px; align-items:center; margin-top:18px; }
  .chip { font-size:.85rem; padding:6px 12px; border-radius:999px; background:var(--brand);
          color:#fff; font-weight:600; }
  .chip.soldout { background:#dc2626; }
</style>
</head>
<body>
<div class="wrap">
  <div class="hero">
    <h1><?= e($C['event']['name']) ?></h1>
    <div class="meta">
      <div>📅 <?= e($C['event']['date']) ?> · <?= e($C['event']['time']) ?></div>
      <div>📍 <?= e($C['event']['venue']) ?></div>
      <div><?= e($C['event']['description']) ?></div>
    </div>
  </div>

  <?php if ($msg = flash('success')): ?><div class="alert success"><?= e($msg) ?></div><?php endif; ?>
  <?php if ($err = flash('error')):   ?><div class="alert error"><?= e($err) ?></div><?php endif; ?>

  <?php if (!$C['event']['booking_open']): ?>
    <div class="card"><h2>Booking closed</h2>
      <p>Ticket sales for this event have closed. Contact
      <a href="mailto:<?= e($C['site']['contact']) ?>"><?= e($C['site']['contact']) ?></a>.</p></div>
  <?php elseif ($left <= 0): ?>
    <div class="card"><h2>Sold out 🎫</h2><p>All <?= e($C['event']['capacity']) ?> tickets are booked.</p></div>
  <?php else: ?>

  <form method="post" action="book.php" id="bookingForm">
    <?= csrf_field() ?>
    <div class="card">
      <h2><?= e($C['event']['ticket_name']) ?></h2>
      <div class="rc">
        <span class="chip"><?= e($C['event']['price_label']) ?></span>
        <span class="chip"><?= $left ?> left</span>
      </div>

      <div class="qty-row">
        <label style="margin:16px 0 6px">Number of tickets
        <select name="qty" id="qty">
          <?php $max = min((int)$C['event']['max_per_order'], $left);
          for ($i = 1; $i <= $max; $i++) echo "<option value=\"{$i}\">{$i}</option>"; ?>
        </select></label>
      </div>

      <label>Your name (booking owner)
        <input type="text" name="contact_name" required maxlength="120" autocomplete="name"></label>
      <label>Your email <span style="font-weight:400">(optional)</span>
        <input type="email" name="contact_email" maxlength="191" autocomplete="email"></label>

      <div id="ticketFields"></div>

      <button type="submit" class="btn">Confirm booking — <?= e($C['event']['price_label']) ?></button>
      <div class="note">No payment now. You will get one ticket code per attendee
        (<?= e($C['event']['ticket_name']) ?>) on the confirmation screen.</div>
    </div>
  </form>
  <?php endif; ?>

  <footer>Organised by <?= e($C['site']['organizer']) ?> · Questions?
    <a href="mailto:<?= e($C['site']['contact']) ?>"><?= e($C['site']['contact']) ?></a></footer>
</div>

<script>
(function () {
  const qtyEl  = document.getElementById('qty');
  const holder = document.getElementById('ticketFields');
  if (!qtyEl || !holder) return;

  function render() {
    const n = parseInt(qtyEl.value, 10);
    let html = '';
    for (let i = 1; i <= n; i++) {
      html += '<div class="ticket-block">'
        + '<div class="lbl">Ticket #' + i + ' — attendee name</div>'
        + '<label style="font-weight:500">Full name'
        + '<input type="text" name="attendee_name[]" maxlength="120" required placeholder="Person attending"></label>'
        + '<label style="font-weight:500">Email <span style="font-weight:400">(optional)</span>'
        + '<input type="email" name="attendee_email[]" maxlength="191"></label>'
        + '</div>';
    }
    holder.innerHTML = html;
  }
  qtyEl.addEventListener('change', render);
  render();
})();
</script>
</body>
</html>