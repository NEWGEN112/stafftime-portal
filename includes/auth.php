<?php
// StaffTime - Authentication Helper
// This file protects admin pages and manages login sessions

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if the logged-in user is an Admin
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Protect Admin pages - redirect to login if not logged in as admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ../public/login.php');
        exit;
    }
}

/**
 * Protect Staff pages
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../public/login.php');
        exit;
    }
}

/**
 * Get current logged-in user data
 */
function currentUser() {
    if (!isLoggedIn()) return null;
    
    return [
        'id'         => $_SESSION['user_id'],
        'school_id'  => $_SESSION['school_id'] ?? null,
        'full_name'  => $_SESSION['full_name'] ?? '',
        'role'       => $_SESSION['role'] ?? '',
        'school_name'=> $_SESSION['school_name'] ?? ''
    ];
}
?>
