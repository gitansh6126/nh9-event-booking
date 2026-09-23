<?php
// NH9 Event Booking — DEPLOYMENT SCRIPT FOR HOSTGATOR
// ====================================================
// Upload this file temporarily, run it once, then DELETE it.

echo "<h2>NH9 Events — Deployment Check</h2>";

// Check PHP version
$phpVersion = phpversion();
$phpOk = version_compare($phpVersion, '7.4', '>=');
echo "<p>PHP Version: <strong>$phpVersion</strong> " . ($phpOk ? "✅ OK" : "❌ NEEDS 7.4+") . "</p>";

// Check MySQL extension
$mysqlOk = extension_loaded('pdo_mysql');
echo "<p>PDO MySQL: " . ($mysqlOk ? "✅ OK" : "❌ NEED PDO MySQL extension") . "</p>";

// Check sessions
session_start();
echo "<p>PHP Sessions: ✅ OK</p>";

// Check file permissions
$files = ['index.php', 'book.php', 'confirm.php', 'src/bootstrap.php', 'admin/index.php', 'admin/export.php', '.htaccess'];
echo "<h3>File Permissions:</h3><ul>";
foreach ($files as $f) {
    if (file_exists($f)) {
        $perms = substr(sprintf('%o', fileperms($f)), -4);
        $ok = ($perms === '0644' || $perms === '0755');
        echo "<li>$f: $perms " . ($ok ? "✅" : "⚠️ should be 0644/0755") . "</li>";
    } else {
        echo "<li>$f: ❌ NOT FOUND</li>";
    }
}
echo "</ul>";

// Check if config exists
echo "<h3>Configuration:</h3>";
if (file_exists('config.php')) {
    echo "<p>✅ config.php exists</p>";
    $config = require 'config.php';
    echo "<p>DB Host: " . ($config['db']['host'] ?? '❌ MISSING') . "</p>";
    echo "<p>DB Name: " . ($config['db']['name'] ?? '❌ MISSING') . "</p>";
    echo "<p>Event: " . ($config['event']['name'] ?? '❌ MISSING') . "</p>";
    echo "<p>Admin user: " . ($config['admin']['username'] ?? '❌ MISSING') . "</p>";
} else {
    echo "<p>❌ config.php NOT FOUND — Copy config.sample.php to config.php and fill in details</p>";
}

// Check admin password hash
echo "<h3>Security:</h3>";
if (isset($config['admin']['password_hash']) && $config['admin']['password_hash'] !== 'CHANGE_ME') {
    echo "<p>✅ Admin password hash configured</p>";
} else {
    echo "<p>❌ Admin password_hash is still 'CHANGE_ME' — Generate with: php -r \"echo password_hash('YOUR_PASS', PASSWORD_BCRYPT);\"</p>";
}

if (isset($config['security']['csrf_secret']) && strlen($config['security']['csrf_secret'] ?? '') > 10) {
    echo "<p>✅ CSRF secret configured</p>";
} else {
    echo "<p>❌ CSRF secret is still 'CHANGE_ME' — Generate a 32+ character random string</p>";
}

// Check .htaccess
echo "<h3>.htaccess:</h3>";
if (file_exists('.htaccess')) {
    echo "<p>✅ .htaccess file exists</p>";
    $htaccess = file_get_contents('.htaccess');
    if (strpos($htaccess, 'Files "config.php"') !== false) {
        echo "<p>✅ Config file protection is in place</p>";
    }
} else {
    echo "<p>❌ .htaccess file NOT FOUND</p>";
}

// Check PHP short tags
echo "<h3>PHP Configuration:</h3>";
echo "<p>display_errors: " . (ini_get('display_errors') ? "⚠️ ON (should be OFF in production)" : "✅ OFF") . "</p>";
echo "<p>memory_limit: " . ini_get('memory_limit') . "</p>";
echo "<p>max_execution_time: " . ini_get('max_execution_time') . " seconds</p>";
echo "<p>upload_max_filesize: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>post_max_size: " . ini_get('post_max_size') . "</p>";

echo "<hr><h2>✅ Deployment Check Complete</h2>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ol><li>Fix any ❌ items above</li><li>Create MySQL database on HostGator hPanel</li><li>Update config.php with DB credentials</li><li>Upload all files to public_html/nh9-events/</li><li>Test by visiting yourdomain.com/nh9-events/</li></ol>";
