<?php
session_start();
require_once 'db/config.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

// Fetch user stats
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$active_users = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active=1")->fetchColumn();
$inactive_users = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active=0")->fetchColumn();

// Export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="users_report.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Name', 'Email', 'Role', 'Status', 'Created At', 'Last Login']);

    $stmt = $pdo->query("SELECT id, name, email, role, is_active, created_at, last_login FROM users ORDER BY created_at DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $row['email'],
            $row['role'],
            $row['is_active'] ? 'Active' : 'Inactive',
            $row['created_at'],
            $row['last_login'] ?? '-'
        ]);
    }
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin User Reports</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: rgb(255, 244, 234);  /* Soft orange */
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 720px;
            margin: 60px auto;
            background: #fff;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
        }
        h2 {
            text-align: center;
            color: #1976d2;
            font-size: 1.8rem;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #e0e0e0;
            padding: 14px 16px;
            text-align: left;
        }
        th {
            background: #1976d2;
            color: white;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        a.button {
            background: #1976d2;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }
        a.button:hover {
            background: #0056b3;
        }
        .back-link {
            display: block;
            margin-top: 25px;
            text-align: center;
            color: #1976d2;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }
            table, th, td {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Admin User Reports</h2>

    <table>
        <tr><th>Metric</th><th>Value</th></tr>
        <tr><td>Total Users</td><td><?= $total_users ?></td></tr>
        <tr><td>Active Users</td><td><?= $active_users ?></td></tr>
        <tr><td>Inactive Users</td><td><?= $inactive_users ?></td></tr>
    </table>

    <a href="?export=csv" class="button">⬇ Export Users CSV</a>
    <a href="admin_dashboard.php" class="back-link">⬅ Back to Admin Dashboard</a>
</div>

</body>
</html>
