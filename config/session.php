<?php
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $user = query("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    return $user[0] ?? null;
}

// Check user role
function hasRole($role) {
    $user = getCurrentUser();
    return $user && $user['role'] === $role;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect if not authorized
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: dashboard.php');
        exit();
    }
}

// Get current cabang for non-superadmin
function getCurrentCabang() {
    $user = getCurrentUser();
    if (!$user) return null;
    
    if ($user['role'] === 'superadmin') {
        return $_GET['cabang_id'] ?? $_SESSION['cabang_id'] ?? null;
    }
    
    return $user['cabang_id'];
}
?>
