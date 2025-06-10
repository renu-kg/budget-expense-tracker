<?php
session_start();
require_once 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Filter handling
$conditions = ["e.user_id = ?"];
$params = [$user_id];

if (!empty($_GET['start_date'])) {
    $conditions[] = "e.expense_date >= ?";
    $params[] = $_GET['start_date'];
}
if (!empty($_GET['end_date'])) {
    $conditions[] = "e.expense_date <= ?";
    $params[] = $_GET['end_date'];
}
if (!empty($_GET['budget_id'])) {
    $conditions[] = "e.budget_id = ?";
    $params[] = $_GET['budget_id'];
}

$sql = "SELECT e.*, b.title AS budget_title,
        CASE 
            WHEN EXISTS (
                SELECT 1 FROM recurring_expense r 
                WHERE r.user_id = e.user_id 
                AND r.amount = e.amount 
                AND r.category = e.category 
                AND e.expense_date BETWEEN r.start_date AND IFNULL(r.end_date, CURDATE())
            ) 
            THEN 1 ELSE 0 
        END AS is_recurring
        FROM expenses e
        LEFT JOIN budget b ON e.budget_id = b.id
        WHERE " . implode(" AND ", $conditions) . "
        ORDER BY e.expense_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Expenses</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        form.filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 25px;
            align-items: center;
            justify-content: space-between;
        }

        .filter-group {
            flex: 1 1 20%;
            min-width: 120px;
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: 500;
            margin-bottom: 4px;
            font-size: 13px;
        }

        .filter-group input,
        .filter-group select {
            padding: 6px 8px;
            font-size: 13px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .filter-form button {
            padding: 8px 16px;
            background-color: #1976d2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            align-self: flex-end;
        }

        .filter-form button:hover {
            background-color: #0056b3;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
            margin-bottom: 25px;
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

        .recurring-tag {
            display: inline-block;
            background-color: #28a745;
            color: white;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 5px;
        }

        .back-link {
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

        .back-link:hover {
            background-color: #0056b3;
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

            form.filter-form {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group, .filter-form button {
                width: 100%;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 8px 10px;
            }

            .back-link {
                font-size: 14px;
                padding: 8px 14px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Your Expenses</h2>

    <!-- Filter Form -->
    <form method="GET" class="filter-form">
        <div class="filter-group">
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
        </div>

        <div class="filter-group">
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
        </div>

        <div class="filter-group">
            <label for="budget_id">Budget:</label>
            <select name="budget_id">
                <option value="">All</option>
                <?php
                $budgetStmt = $pdo->prepare("SELECT id, title FROM budget WHERE user_id = ?");
                $budgetStmt->execute([$user_id]);
                while ($b = $budgetStmt->fetch()) {
                    $selected = (isset($_GET['budget_id']) && $_GET['budget_id'] == $b['id']) ? 'selected' : '';
                    echo "<option value=\"{$b['id']}\" $selected>" . htmlspecialchars($b['title']) . "</option>";
                }
                ?>
            </select>
        </div>

        <button type="submit">Filter</button>
    </form>

    <!-- Expenses Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Budget</th>
                    <th>Category</th>
                    <th>Amount (₹)</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($expenses) === 0): ?>
                <tr>
                    <td colspan="7" class="no-data">No expenses found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?= htmlspecialchars($expense['budget_title'] ?? 'None') ?></td>
                        <td>
                            <?= htmlspecialchars($expense['category']) ?>
                            <?php if ($expense['is_recurring']): ?>
                                <span class="recurring-tag">RE</span>
                            <?php endif; ?>
                        </td>
                        <td><?= number_format($expense['amount'], 2) ?></td>
                        <td><?= htmlspecialchars($expense['expense_date']) ?></td>
                        <td><?= htmlspecialchars($expense['description']) ?></td>
                        <td><a href="edit_expense.php?id=<?= $expense['id'] ?>">Edit</a></td>
                        <td><a href="delete_expense.php?id=<?= $expense['id'] ?>" onclick="return confirm('Are you sure you want to delete this expense?');">Delete</a></td>
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
