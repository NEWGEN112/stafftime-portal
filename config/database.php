<?php
// StaffTime - Supabase (PostgreSQL) Database Configuration

define('DB_HOST', 'aws-1-eu-west-3.pooler.supabase.com');
define('DB_PORT', '5432');
define('DB_NAME', 'postgres');
define('DB_USER', 'postgres.yhpvuvzcnpxwdjyuhhcj');
define('DB_PASS', 'Hpffellowship001');   // ← Replace this with your real password
define('DB_CHARSET', 'utf8');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=require";
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
