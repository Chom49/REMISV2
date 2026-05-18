<?php
// Session management helper

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to redirect to login if not logged in
function requireLogin($redirectTo = null) {
    if (!isLoggedIn()) {
        if ($redirectTo === null) {
            $redirectTo = '../php/signIn.php';
        }
        header("Location: " . $redirectTo);
        exit;
    }
}

// Function to get current user data
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? 'User',
        'email' => $_SESSION['user_email'] ?? '',
        'phone' => $_SESSION['user_phone'] ?? ''
    ];
}

// Function to logout
function logout() {
    session_destroy();
    header("Location: ../php/signIn.php");
    exit;
}
?>
