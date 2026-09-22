<?php
// NH9 Event Booking — admin login + dashboard (bookings, tickets, DB proof)
require __DIR__ . '/../src/bootstrap.php';
ensure_schema();

$C = $CONFIG;
$err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $u = trim((string)($_POST['username'] ?? ''));
    $p = (string)($_POST['password'] ?? '');
    if ($u === $C['admin']['username'] && password_verify($p, $C['admin']['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    }
    $err = 'Invalid username or password.';
}

// Optional logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged_in']);
    session_destroy();
    header('Location: index.php');
    exit;
}

$loggedIn = !empty($_SESSION['admin_logged_in']);
$sold = tickets_sold();
$left = max(0, (int)$C['event']['capacity'] - $sold);

// Dashboard data
$bookings = [];
$totalTickets = 0;
if ($loggedIn) {
    $s = db()->query('SELECT * FROM bookings ORDER BY id DESC LIMIT 100')->fetchAll();
    foreach ($s as $b) {
        $t = db()->prepare('SELECT attendee_name, attendee_email, ticket_code FROM tickets WHERE booking_id = ? ORDER BY id');
        $t->execute([$b['id']]);
        $b['tickets'] = $t->fetchAll();
        $totalTickets += (int)$b['ticket_qty'];
        $bookings[] = $b;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin · NH9 Event Booking</title>
<style>
  :root { --brand: <?= e($C['site']['brand_color']) ?>; }
  * { box-sizing:border-box; margin:0; padding:0; }
  body { font-family: system-ui, -apple-system, "Segoe UI", sans-serif; background:#f6f5fb; color:#222; }
  .wrap { max-width:980px; margin:0 auto; padding:28px 18px 60px; }
  .bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
  h1 { font-size:1.4rem; }
  a.lnk, .btn { color:var(--brand); text-decoration:none; font-weight:600; }
  .btn { background:var(--brand); color:#fff; padding:9px 14px; border-radius:8px; display:inline-block; }
  .stats { display:flex; gap:14px; margin-bottom:20px; flex-wrap:wrap; }
  .stat { background:#fff; border:1px solid #e5e2f0; border-radius:12px; padding:14px 18px; flex:1; min-width:130px; }
  .stat b { font-size:1.5rem; display:block; color:var(--brand); }
  .card { background:#fff; border:1px solid #e5e2f0; border-radius:12px; padding:18px; }
  .card + .card { margin-top:20px; }
  table { width:100%; border-collapse:collapse; font-size:.92rem; }
  th,td { text-align:left; padding:9px 8px; border-bottom:1px solid #efeaf8; vertical-align:top; }
  th { color:#6b677f; font-size:.8rem; text-transform:uppercase; letter-spacing:.03em; }
  .code { font-family:"Cascadia Code",monospace; font-weight:700; color:#0f6b37; }
  form.login { max-width:360px; }
  input[type=text], input[type=password] { width:100%; padding:10px 12px; margin-bottom:10px;
          border:1px solid #cfcae6; border-radius:8px; font-size:1rem; }
  .alert { background:#fdeaea; color:#a3162f; border:1px solid #f3c0c8; border-radius:8px;
           padding:10px 12px; margin-bottom:12px; }
  .sub { font-size:.82rem; color:#8a86a0; }
</style>
</head>
<body>
<div class="wrap">
  <div class="bar">
    <h1>NH9 Event Booking — Admin</h1>
    <?php if ($loggedIn): ?><a class="lnk" href="?logout=1">Log out</a><?php endif; ?>
  </div>

  <?php if ($err): ?><div class="alert"><?= e($err) ?></div><?php endif; ?>

  <?php if (!$loggedIn): ?>
    <form class="card login" method="post">
      <?= csrf_field() ?>
      <label><b>Username</b><input type="text" name="username" required autofocus></label>
      <label><b>Password</b><input type="password" name="password" required></label>
      <button class="btn" type="submit">Log in</button>
    </form>
  <?php else: ?>
    <div class="stats">
      <div class="stat"><b><?= $sold ?></b>tickets booked</div>
      <div class="stat"><b><?= $totalTickets ?></b>per attendee rows</div>
      <div class="stat"><b><?= $left ?></b>left of <?= (int)$C['event']['capacity'] ?></div>
      <div class="stat"><b><?= count($bookings) ?></b>bookings</div>
    </div>

    <div class="card" style="display:flex; gap:12px; flex-wrap:wrap;">
      <a class="btn" href="export.php">⬇ Download CSV</a>
      <a class="lnk" href="../index.php" target="_blank">Open booking page ↗</a>
    </div>

    <div class="card">
      <h2 style="margin-bottom:10px">Bookings (latest 100)</h2>
      <?php if (!$bookings): ?>
        <p class="sub">No bookings yet. Book tickets via the public page, they will appear here instantly.</p>
      <?php else: ?>
      <table>
        <tr><th>Ref</th><th>Date</th><th>Contact</th><th>Qty</th><th>Status</th><th>Tickets</th></tr>
        <?php foreach ($bookings as $b): ?>
        <tr>
          <td><b class="code"><?= e($b['booking_ref']) ?></b></td>
          <td><?= e(date('d M y H:i', strtotime($b['created_at']))) ?></td>
          <td><?= e($b['contact_name']) ?><br><span class="sub"><?= e($b['contact_email'] ?: '—') ?></span></td>
          <td><?= (int)$b['ticket_qty'] ?></td>
          <td><span class="sub"><?= e($b['status']) ?></span></td>
          <td>
            <?php foreach ($b['tickets'] as $t): ?>
              <div><?= e($t['attendee_name']) ?> · <span class="code"><?= e($t['ticket_code']) ?></span></div>
            <?php endforeach; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
</body>
</html>