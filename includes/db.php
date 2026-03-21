<?php
/**
 * includes/db.php
 * PDO Database Connection — XAMPP / phpMyAdmin compatible
 *
 * Usage: require_once 'includes/db.php';
 * Then use $pdo for all queries.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'complaint_system');
define('DB_USER', 'root');       // Default XAMPP username
define('DB_PASS', '');           // Default XAMPP has no password
define('DB_CHARSET', 'utf8mb4');

$dsn = 'mysql:host=' . DB_HOST
     . ';dbname='    . DB_NAME
     . ';charset='   . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // In production, log the error; don't expose to user
    error_log('DB Connection failed: ' . $e->getMessage());
    http_response_code(500);
    die('<h2 style="font-family:sans-serif;padding:40px;color:#ef4444;">
         ⚠️ Database connection failed.<br>
         <small style="color:#94a3b8;font-size:14px;">
           Please check your XAMPP MySQL service and <code>includes/db.php</code> settings.
         </small>
         </h2>');
}
