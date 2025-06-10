<?php
session_start();
require_once 'db/config.php';

// Redirect to admin dashboard if already logged in as admin
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check if admin exists
    $sql = "SELECT * FROM users WHERE email = ? AND role = 'admin' AND is_active = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        // Valid admin login
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['email'] = $admin['email'];
        $_SESSION['role'] = $admin['role'];
        $_SESSION['name'] = $admin['name'];

        // Update last login
        $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);

        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
        body { font-family:'Segoe UI', sans-serif; background-color: #f2f2f2; padding: 60px; }
        .login-box {
            max-width: 400px; margin: auto; background: #fff; padding: 20px;
            border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input[type="email"], input[type="password"] {
            width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px;
        }
        button {
            width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 5px;
        }
        .error { color: red; margin-top: 10px; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Admin Login</h2>
    <form method="POST">
        <label>Email:</label>
        <input type="email" name="email" required>
        
        <label>Password:</label>
        <input type="password" name="password" required>
        
        <button type="submit">Login</button>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </form>
</div>

</body>
</html>
