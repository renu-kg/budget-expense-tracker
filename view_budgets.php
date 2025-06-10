<?php
session_start();
require_once 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM budget WHERE user_id = ? ORDER BY start_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$budgets = $stmt->fetchAll();

$expenseStmt = $pdo->prepare("SELECT budget_id, SUM(amount) AS total_spent FROM expenses WHERE user_id = ? GROUP BY budget_id");
$expenseStmt->execute([$user_id]);
$expensesByBudget = $expenseStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$alert = '';
if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    $alert = "✅ Budget deleted successfully.";
} elseif (isset($_GET['updated']) && $_GET['updated'] == 1) {
    $alert = "✅ Budget updated successfully.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Budgets</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Mobile support -->
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: rgb(255, 244, 234); 
            margin: 0;
            padding: 40px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #1976d2;
            margin-bottom: 20px;
            text-align: center;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            min-width: 600px;
        }

        th, td {
            padding: 12px 15px;
            text-align: center;
        }

        th {
            background-color: #1976d2;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e0ebff;
        }

        a {
            text-decoration: none;
            color: #007bff;
        }

        a:hover {
            text-decoration: underline;
        }

        .btn-back, .back-link {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #1976d2;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s ease;
            text-align: center;
        }

        .btn-back:hover, .back-link:hover {
            background-color: #0056b3;
        }

        .alert {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .no-data {
            text-align: center;
            font-style: italic;
            color: #888;
        }

        @media (max-width: 600px) {
            body {
                padding: 20px;
            }

            .container {
                padding: 20px;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 8px 10px;
            }

            .btn-back, .back-link {
                font-size: 14px;
                padding: 8px 14px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Your Budgets</h2>

    <?php if (!empty($alert)): ?>
        <div class="alert"><?= htmlspecialchars($alert) ?></div>
    <?php endif; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Amount (₹)</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Remaining Balance (₹)</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($budgets) === 0): ?>
                <tr>
                    <td colspan="7" class="no-data">No budgets found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($budgets as $budget): 
                    $spent = $expensesByBudget[$budget['id']] ?? 0;
                    $remaining = $budget['amount'] - $spent;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($budget['title']) ?></td>
                        <td><?= number_format($budget['amount'], 2) ?></td>
                        <td><?= htmlspecialchars($budget['start_date']) ?></td>
                        <td><?= htmlspecialchars($budget['end_date']) ?></td>
                        <td><?= number_format($remaining, 2) ?></td>
                        <td><a href="edit_budget.php?id=<?= $budget['id'] ?>">Edit</a></td>
                        <td><a href="delete_budget.php?id=<?= $budget['id'] ?>" onclick="return confirm('Are you sure you want to delete this budget?');">Delete</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a class="back-link" href="dashboard.php">← Back to Dashboard</a>
</div>

</body>
</html>
