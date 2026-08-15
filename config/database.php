<?php
// StaffTime - Database Configuration
// Change these values to match your hosting or local database

define('DB_HOST', 'localhost');
define('DB_NAME', 'stafftime');
define('DB_USER', 'root');
define('DB_PASS', '');          // Put your database password here
define('DB_CHARSET', 'utf8mb4');

// Create connection
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}
?>
