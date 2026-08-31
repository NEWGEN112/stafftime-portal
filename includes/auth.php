<?php
// StaffTime - Authentication Helper (Secured + Super Admin)

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);

    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
    }

    session_start();
}

require_once __DIR__ . '/../config/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isSuperAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin';
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ../public/login.php');
        exit;
    }
}

function requireSuperAdmin() {
    if (!isSuperAdmin()) {
        header('Location: ../public/login.php');
        exit;
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../public/login.php');
        exit;
    }
}

function currentUser() {
    if (!isLoggedIn()) return null;

    return [
        'id'          => $_SESSION['user_id'],
        'school_id'   => $_SESSION['school_id'] ?? null,
        'full_name'   => $_SESSION['full_name'] ?? '',
        'role'        => $_SESSION['role'] ?? '',
        'school_name' => $_SESSION['school_name'] ?? ''
    ];
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function secureLoginSession() {
    session_regenerate_id(true);
}
?>
