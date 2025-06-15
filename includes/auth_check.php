<?php
session_start();

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    // Store the requested URL for redirect after login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    
    // Set toast message
    $_SESSION['toast_message'] = 'Please login to access this page';
    $_SESSION['toast_type'] = 'warning';
    
    // Redirect to main index page
    header('Location: /mswdov2/index.php');
    exit();
}