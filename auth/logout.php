<?php
session_start();
require_once '../includes/db.php';

// Log the logout activity if user was logged in
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (act_name, act_type) VALUES (?, ?)");
        $stmt->execute([
            "User {$_SESSION['username']} logged out",
            'logout'
        ]);
    } catch (PDOException $e) {
        // Silent fail - still want to log user out even if logging fails
    }
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login page
header('Location: ../index.php');
exit();