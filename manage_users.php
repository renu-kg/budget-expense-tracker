<?php
session_start();
require_once 'db/config.php';

// Only allow access if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

// Fetch all users
$sql  = "SELECT id, name, email, role, is_active, created_at, last_login 
         FROM users 
         ORDER BY created_at DESC";
$stmt  = $pdo->query($sql);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <style>
        /* ---- page basics -------------------------------------------- */
        body {
            font-family: 'Segoe UI', sans-serif;
            background: rgb(255, 244, 234);       /* soft orange, same as dashboard */
            margin: 0;
            padding: 0;
        }
        /* ---- card container ----------------------------------------- */
        .container {
            max-width: 1100px;
            margin: 60px auto;
            background: #fff;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
        }
        h2 {
            margin-top: 0;
            margin-bottom: 25px;
            color: #1976d2;                       /* blue accent */
            font-size: 1.8rem;
            text-align: center;
        }
        /* ---- table styling ------------------------------------------ */
        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;                     /* keeps rounded corners */
        }
        th, td {
            padding: 14px 16px;
            border: 1px solid #e0e0e0;
            text-align: left;
        }
        th {
            background: #1976d2;
            color: #fff;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        tr:hover {
            background: #bbdefb;                  /* light blue hover */
        }
        /* ---- buttons / links ---------------------------------------- */
        .btn {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: background 0.25s;
        }
        .toggle {
            background: #e3f2fd;                  /* pale blue */
            color: #1976d2;
        }
        .toggle:hover {
            background: #bbdefb;
        }
        /* “Back” link */
        .back-link {
            display: inline-block;
            margin-top: 30px;
            text-decoration: none;
            color: #1976d2;
            font-weight: 500;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        /* small screens ---------------------------------------------- */
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
    <h2>User Management</h2>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Created</th>
                <th>Last&nbsp;Login</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($users) === 0): ?>
                <tr>
                    <td colspan="7" style="text-align:center;">No users found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td><?= $user['is_active'] ? 'Active' : 'Inactive' ?></td>
                        <td><?= $user['created_at'] ?></td>
                        <td><?= $user['last_login'] ?? '-' ?></td>
                        <td>
                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                <a  href="toggle_user_status.php?id=<?= $user['id'] ?>"
                                    class="btn toggle"
                                    onclick="return confirm('Toggle user status?');">
                                    <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="admin_dashboard.php" class="back-link">⬅ Back to Admin Dashboard</a>
</div>

</body>
</html>
