<?php
session_start();
require_once 'db/config.php';

// Access control: Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: rgb(255, 244, 234); 
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 960px;
            margin: 50px auto;
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.07);
        }
        h1 {
            color: #1976d2;
            margin-bottom: 10px;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        nav {
            text-align: right;
            margin-bottom: 20px;
        }
        nav a {
            text-decoration: none;
            color: #1976d2;
            margin-left: 15px;
            font-weight: 500;
        }
        nav a:hover {
            text-decoration: underline;
        }
        ul {
            list-style: none;
            padding: 0;
            margin-top: 15px;
        }
        li {
            margin-bottom: 12px;
        }
        li a {
            text-decoration: none;
            color: #333;
            padding: 10px 15px;
            display: block;
            border-radius: 6px;
            background-color: #e3f2fd;
            transition: background-color 0.3s;
        }
        li a:hover {
            background-color: #bbdefb;
        }
        .section {
            margin-top: 25px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Welcome, Admin <?= htmlspecialchars($_SESSION['name']) ?> 👋</h1>
    <nav>
        <a href="logout.php">Logout</a>
    </nav>

    <div class="section">
        <h2>Admin Dashboard</h2>
        <ul>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="admin_reports.php">User Reports</a></li>
        </ul>
    </div>
</div>

</body>
</html>
