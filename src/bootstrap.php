<?php
// NH9 Event Booking — shared bootstrap (loads config, DB, helpers)

declare(strict_types=1);

session_start();
mb_internal_encoding('UTF-8');
date_default_timezone_set('Asia/Kolkata');

error_reporting(E_ALL);
ini_set('display_errors', '1'); // set to '0' in production

// Prefer a local/config.php for development (docker-compose mounts/uses it) —
// falls back to config.php for normal deploys. config.local.php is gitignored.
$configFile = is_file(__DIR__ . '/../config.local.php')
    ? __DIR__ . '/../config.local.php'
    : __DIR__ . '/../config.php';
$CONFIG = array_merge([], require $configFile);

// --- CSRF ----------------------------------------------------------------
function csrf_token(): string
{
    global $CONFIG;
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = hash_hmac('sha256', session_id(), $CONFIG['security']['csrf_secret']);
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    if (!isset($_POST['_token']) || !hash_equals(csrf_token(), (string)$_POST['_token'])) {
        http_response_code(419);
        exit('<h1>419 — Session expired</h1><p>Your session token is invalid or expired. Go back and try again.</p>');
    }
}

// --- Output helpers --------------------------------------------------------
function e(?string $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function flash(string $k): ?string
{
    if (isset($_SESSION['flash'][$k])) {
        $v = $_SESSION['flash'][$k];
        unset($_SESSION['flash'][$k]);
        return $v;
    }
    return null;
}

function set_flash(string $k, string $v): void
{
    $_SESSION['flash'][$k] = $v;
}

// --- DB --------------------------------------------------------------------
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    global $CONFIG;
    $d = $CONFIG['db'];
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $d['host'], $d['port'], $d['name'], $d['charset']);
    $pdo = new PDO($dsn, $d['user'], $d['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}

/**
 * Create tables if they don't exist. Runs on every request (cheap IF NOT EXISTS),
 * so no manual migration step is needed on Hostinger.
 */
function ensure_schema(): void
{
    db()->exec('CREATE TABLE IF NOT EXISTS bookings (
        id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        booking_ref CHAR(8) NOT NULL UNIQUE,
        contact_name VARCHAR(120) NOT NULL,
        contact_email VARCHAR(191),
        ticket_qty  INT UNSIGNED NOT NULL,
        status      ENUM("CONFIRMED","CANCELLED") NOT NULL DEFAULT "CONFIRMED",
        created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    db()->exec('CREATE TABLE IF NOT EXISTS tickets (
        id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        booking_id  INT UNSIGNED NOT NULL,
        attendee_name VARCHAR(120) NOT NULL,
        attendee_email VARCHAR(191),
        ticket_code CHAR(10) NOT NULL UNIQUE,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
        INDEX (booking_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
}

function random_code(int $len): string
{
    return strtoupper(substr(bin2hex(random_bytes(ceil($len / 2))), 0, $len));
}

/**
 * Total tickets currently sold (CONFIRMED) for capacity check.
 */
function tickets_sold(): int
{
    $row = db()->query('SELECT COALESCE(SUM(bookings.ticket_qty),0) AS n
                         FROM bookings WHERE bookings.status = "CONFIRMED"')->fetch();
    return (int)$row['n'];
}

/**
 * Book N tickets in one transaction. Row-locks the sum so two simultaqueous buyers
 * cannot overbook, then inserts the booking + one ticket row per attendee.
 *
 * @param array{name:string,email:?string} $attendees
 * @return array{ref:string, codes:string[]}
 * @throws RuntimeException on overbooking / DB failure (transaction rolled back)
 */
function create_booking(string $contactName, ?string $contactEmail, array $attendees): array
{
    global $CONFIG;
    $cap = (int)$CONFIG['event']['capacity'];
    $n   = count($attendees);

    if ($n < 1) {
        throw new RuntimeException('No tickets selected');
    }
    if ($n > (int)$CONFIG['event']['max_per_order']) {
        throw new RuntimeException('Max ' . $CONFIG['event']['max_per_order'] . ' tickets per booking');
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        // Lock the running total so concurrent bookings serialize correctly.
        $sold = $pdo->query('SELECT COALESCE(SUM(bookings.ticket_qty),0) AS n
                              FROM bookings WHERE bookings.status="CONFIRMED" FOR UPDATE')->fetch();
        $sold = (int)$sold['n'];
        if ($sold + $n > $cap) {
            throw new RuntimeException('Only ' . max(0, $cap - $sold) . ' tickets left');
        }

        $ref  = random_code(8);
        $stmt = $pdo->prepare('INSERT INTO bookings (booking_ref, contact_name, contact_email, ticket_qty, status)
                               VALUES (?,?,?,?,"CONFIRMED")');
        $stmt->execute([$ref, $contactName, $contactEmail ?: null, $n]);
        $bookingId = (int)$pdo->lastInsertId();

        $codes = [];
        $tStmt = $pdo->prepare('INSERT INTO tickets (booking_id, attendee_name, attendee_email, ticket_code)
                                VALUES (?,?,?,?)');
        foreach ($attendees as $a) {
            $code = random_code(10);
            $tStmt->execute([$bookingId, $a['name'], $a['email'] ?: null, $code]);
            $codes[] = $code;
        }

        $pdo->commit();
        return ['ref' => $ref, 'codes' => $codes];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function booking_by_ref(string $ref): ?array
{
    $b = db()->prepare('SELECT * FROM bookings WHERE booking_ref = ? LIMIT 1');
    $b->execute([$ref]);
    $booking = $b->fetch();
    if (!$booking) {
        return null;
    }
    $t = db()->prepare('SELECT * FROM tickets WHERE booking_id = ? ORDER BY id');
    $t->execute([$booking['id']]);
    $booking['tickets'] = $t->fetchAll();
    return $booking;
}

// --- Mini router guard -------------------------------------------------------
// Simple redirect helper for admin pages when not logged in.
function require_admin(): void
{
    if (empty($_SESSION['admin_logged_in'])) {
        header('Location: index.php');
        exit;
    }
}