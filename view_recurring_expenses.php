<?php
session_start();
require_once 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM recurring_expense WHERE user_id = ? ORDER BY start_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$recurring_expenses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Recurring Expenses</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: rgb(255, 244, 234); 
            padding: 40px;
            margin: 0;
            color: #333;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #1976d2;
        }

        table {
            max-width: 900px;
            margin: auto;
            background: white;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px 20px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background-color: #1976d2;
            color: white;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f1f7ff;
        }

        a.button {
            display: inline-block;
            padding: 6px 14px;
            margin-right: 6px;
            background-color: #1976d2;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        a.button:hover {
            background-color: #0056b3;
        }

        .actions {
            white-space: nowrap;
        }

        .no-data {
            text-align: center;
            font-size: 18px;
            color: #666;
            margin-top: 30px;
        }

        a.back-link {
            display: block;
            width: 200px;
            margin: 30px auto 0;
            padding: 10px;
            background-color: white;
            color: #007bff;
            text-align: center;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }

        a.back-link:hover {
            text-decoration: underline;
        }

        /* ✅ Mobile responsiveness */
        @media (max-width: 600px) {
            body {
                padding: 15px;
            }

            table, thead, tbody, th, td, tr {
                display: block;
                width: 95%;
            }

            thead {
                display: none;
            }

            tr {
                margin-bottom: 15px;
                background: white;
                padding: 10px;
                border-radius: 8px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            }

            td {
                padding: 8px 10px;
                border: none;
                display: flex;
                justify-content: space-between;
                font-size: 14px;
                border-bottom: 1px solid #eee;
            }

            td:last-child {
                border-bottom: none;
            }

            td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #1976d2;
                flex: 1;
                padding-right: 10px;
            }

            .actions {
                display: flex;
                gap: 8px;
                justify-content: flex-start;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>

<h2>Your Recurring Expenses</h2>

<?php if (count($recurring_expenses) === 0): ?>
    <p class="no-data">No recurring expenses found.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Amount</th>
                <th>Frequency</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recurring_expenses as $expense): ?>
                <tr>
                    <td data-label="Category"><?= htmlspecialchars($expense['category']) ?></td>
                    <td data-label="Amount"><?= number_format($expense['amount'], 2) ?></td>
                    <td data-label="Frequency"><?= htmlspecialchars(ucfirst($expense['frequency'])) ?></td>
                    <td data-label="Start Date"><?= htmlspecialchars($expense['start_date']) ?></td>
                    <td data-label="End Date"><?= $expense['end_date'] ? htmlspecialchars($expense['end_date']) : '-' ?></td>
                    <td data-label="Actions" class="actions">
                        <a class="button" href="edit_recurring_expense.php?id=<?= $expense['id'] ?>">Edit</a>
                        <a class="button" href="delete_recurring_expense.php?id=<?= $expense['id'] ?>" onclick="return confirm('Are you sure you want to delete this recurring expense?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<a href="dashboard.php" class="back-link">&larr; Back to Dashboard</a>

</body>
</html>
