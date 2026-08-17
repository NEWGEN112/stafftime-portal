<?php
// StaffTime - InfinityFree MySQL Configuration

// Set Nigeria / West Africa Time
date_default_timezone_set('Africa/Lagos');

define('DB_HOST', 'sql201.infinityfree.com');
define('DB_NAME', 'if0_42672226_stafftime');
define('DB_USER', 'if0_42672226');
define('DB_PASS', 'Adebiyi112');
define('DB_CHARSET', 'utf8mb4');

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
