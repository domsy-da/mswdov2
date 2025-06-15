<?php
session_start();
require_once '../includes/db.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Log successful login with your existing activity_logs structure
            $log_stmt = $pdo->prepare("INSERT INTO activity_logs (act_name, act_type) VALUES (?, ?)");
            $log_stmt->execute([
                "User {$user['username']} logged in",
                'login'
            ]);

            header('Location: ../index.php');
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    } catch (PDOException $e) {
        $error = 'Login failed. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MSWDO</title>
    <style>
        /* Update existing styles */
        :root {
            --primary-color: #333;
            --secondary-color: #666;
            --background-color: #f5f5f5;
            --error-color: #dc3545;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: var(--background-color);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.9); /* Make container transparent */
            backdrop-filter: blur(10px); /* Add blur effect */
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .logo-container {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s ease;
            text-decoration: none;
        }

        .logo-container:hover {
            transform: scale(1.05);
        }

        .logo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            color: var(--secondary-color);
            font-size: 0.9rem;
        }

        .form-group input {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .login-btn {
            background: var(--primary-color);
            color: white;
            padding: 0.75rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 1rem;
        }

        .login-btn:hover {
            background: #000;
        }

        .error-message {
            color: var(--error-color);
            text-align: center;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <a href="../index.php" class="logo-container" title="Go to Home">
                <img src="../assets/img/mswdologo.jpg" alt="MSWDO Logo">
            </a>
            <h1>MSWDO Login</h1>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form class="login-form" method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="login-btn">Login</button>
        </form>
    </div>
    <div style="
        position: fixed;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 120%;
        height: 120%;
        z-index: -1;
        opacity: 0.05;
        pointer-events: none;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    ">
        <img src="../assets/img/mswdologo2.jpg" alt="" style="
            width: 100%;
            height: 100%;
            object-fit: contain;
        ">
    </div>
</body>
</html>