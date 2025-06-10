<?php
session_start();
require_once 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit;
}

$name = $_SESSION['name'];
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Mobile responsiveness -->
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
            margin-bottom: 10px;
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

        /* Responsive for small screens */
        @media (max-width: 480px) {
            .container {
                margin: 20px 10px;
                padding: 20px;
            }

            h1, h2 {
                font-size: 20px;
            }

            nav {
                text-align: center;
                margin-bottom: 15px;
            }

            nav a {
                display: inline-block;
                margin: 10px 8px;
                font-size: 15px;
            }

            li a {
                font-size: 15px;
                padding: 10px 12px;
            }

            .section {
                padding-top: 15px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Welcome, <?= htmlspecialchars($name) ?>!</h1>
    <nav>
        <a href="logout.php">Logout</a>
    </nav>

    <?php if ($role === 'admin'): ?>
        <div class="section">
            <h2>Admin Dashboard</h2>
            <ul>
                <li><a href="admin_dashboard.php">Go to Admin Panel</a></li>
                <li><a href="view_users.php">View All Users</a></li>
                <li><a href="user_login_details.php">User Login Details</a></li>
            </ul>
        </div>
    <?php else: ?>
        <div class="section">
            <h2>Your Dashboard</h2>
            <ul>
                <li><a href="add_budget.php">Add Budget</a></li>
                <li><a href="view_budgets.php">View Budgets</a></li>
                <li><a href="add_expense.php">Add Expense</a></li>
                <li><a href="view_expenses.php">View Expenses</a></li>
                <li><a href="add_recurring_expense.php">Add Recurring Expense</a></li>
                <li><a href="view_recurring_expenses.php">View Recurring Expenses</a></li>
                <li><a href="view_running_balance.php">View Running Balance</a></li>
            </ul>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
