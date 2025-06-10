<?php
session_start();
require_once 'db/config.php';

// Access control: Only admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>System Settings</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f9f9f9; }
        .container {
            max-width: 800px; margin: auto; background: white;
            padding: 30px; border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        a {
            display: inline-block; margin-top: 20px; padding: 10px 15px;
            background: #007bff; color: white; text-decoration: none; border-radius: 5px;
        }
        a:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>System Settings</h2>
    <p>This page is for managing system-wide settings, such as categories for budgets/expenses.</p>

    <a href="admin_dashboard.php">⬅ Back to Admin Dashboard</a>
</div>

</body>
</html>
