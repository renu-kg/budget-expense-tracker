<?php
session_start();
require_once 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Filters
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$selected_budget_id = $_GET['budget_id'] ?? '';

if ($start_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) $start_date = '';
if ($end_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) $end_date = '';

// Date condition
$date_condition = '';
$date_params = [];
if ($start_date && $end_date) {
    $date_condition = " AND e.expense_date BETWEEN ? AND ? ";
    $date_params = [$start_date, $end_date];
} elseif ($start_date) {
    $date_condition = " AND e.expense_date >= ? ";
    $date_params = [$start_date];
} elseif ($end_date) {
    $date_condition = " AND e.expense_date <= ? ";
    $date_params = [$end_date];
}

// Fetch all budgets
$sql = "SELECT * FROM budget WHERE user_id = ? ORDER BY start_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$all_budgets = $stmt->fetchAll();

// Filtered budgets
$filtered_budgets = $selected_budget_id ? array_filter($all_budgets, fn($b) => $b['id'] == $selected_budget_id) : $all_budgets;

// Running balance data
$running_balances = [];
$total_budget = 0;
$total_expenses = 0;
$total_remaining = 0;

foreach ($filtered_budgets as $budget) {
    $sql_expense = "SELECT COALESCE(SUM(amount), 0) as total_expense 
                    FROM expenses e 
                    WHERE budget_id = ? AND user_id = ? $date_condition";

    $stmt_expense = $pdo->prepare($sql_expense);
    $params = array_merge([$budget['id'], $user_id], $date_params);
    $stmt_expense->execute($params);
    $expense = $stmt_expense->fetchColumn();

    $remaining = $budget['amount'] - $expense;

    $running_balances[] = [
        'title' => $budget['title'],
        'budget_amount' => $budget['amount'],
        'total_expense' => $expense,
        'running_balance' => $remaining
    ];

    $total_budget += $budget['amount'];
    $total_expenses += $expense;
    $total_remaining += $remaining;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Running Balance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: rgb(255, 244, 234);
            padding: 40px;
            margin: 0;
        }

        table {
            border-collapse: collapse;
            width: 80%;
            max-width: 900px;
            margin: auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #1976d2;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        caption {
            font-size: 1.7em;
            margin-bottom: 13px;
            color: #1976d2;
        }

        a {
            text-decoration: none;
            color: #007bff;
            margin: 20px;
            display: inline-block;
        }

        form {
            max-width: 850px;
            margin: 20px auto;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-wrap: wrap;
            gap: 13px;
            align-items: center;
        }

        label {
            font-weight: bold;
        }

        input[type="date"], select {
            padding: 5px;
        }

        button {
            background-color: #1976d2;
            color: white;
            border: none;
            padding: 7px 15px;
            cursor: pointer;
            border-radius: 4px;
        }

        button:hover {
            background-color: #0056b3;
        }

        tfoot td {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            table, table * {
                visibility: visible;
            }

            table {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            form, button, a {
                display: none !important;
            }
        }

        /* Mobile Responsive Styles */
        @media (max-width: 600px) {
            body {
                padding: 15px;
            }

            form {
                flex-direction: column;
                align-items: stretch;
            }

            table, thead, tbody, th, td, tr {
                display: block;
                width: 95%;
            }

            table {
                box-shadow: none;
                background: none;
            }

            caption {
                text-align: center;
                font-size: 1.4em;
                margin: 15px 0;
            }

            tr {
                background: white;
                margin-bottom: 15px;
                border-radius: 8px;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
                padding: 10px;
            }

            td {
                display: flex;
                justify-content: space-between;
                padding: 8px 12px;
                border: none;
                border-bottom: 1px solid #eee;
                font-size: 15px;
            }

            td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #1976d2;
            }

            td:last-child {
                border-bottom: none;
            }

            thead, tfoot {
                display: none; /* Hide table header and footer on mobile */
            }

            /* Mobile total summary card */
            .mobile-total {
                display: block;
                background-color: #f1f1f1;
                padding: 15px;
                border-radius: 8px;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
                margin-top: 20px;
                max-width: 95%;
                margin-left: auto;
                margin-right: auto;
            }

            .mobile-total div {
                display: flex;
                justify-content: space-between;
                margin: 5px 0;
                font-weight: bold;
                font-size: 16px;
            }

            .mobile-total div span:first-child {
                color: #1976d2;
            }
        }

        /* Hide mobile total on desktop */
        .mobile-total {
            display: none;
        }
    </style>
</head>
<body>

<form method="GET">
    <label for="start_date">Start Date:</label>
    <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">

    <label for="end_date">End Date:</label>
    <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">

    <label for="budget_id">Budget:</label>
    <select name="budget_id">
        <option value="">All</option>
        <?php foreach ($all_budgets as $b): ?>
            <option value="<?= $b['id'] ?>" <?= ($selected_budget_id == $b['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($b['title']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Filter</button>
    <a href="view_running_balance.php" style="margin-left:10px;">Clear</a>
</form>

<table>
    <caption>Running Balance Summary</caption>
    <thead>
        <tr>
            <th>Budget Title</th>
            <th>Budget Amount</th>
            <th>Total Expenses</th>
            <th>Running Balance</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($running_balances)): ?>
            <tr><td colspan="4" style="text-align:center;">No data found.</td></tr>
        <?php else: ?>
            <?php foreach ($running_balances as $row): ?>
                <tr>
                    <td data-label="Budget Title"><?= htmlspecialchars($row['title']) ?></td>
                    <td data-label="Budget Amount"><?= number_format($row['budget_amount'], 2) ?></td>
                    <td data-label="Total Expenses"><?= number_format($row['total_expense'], 2) ?></td>
                    <td data-label="Running Balance"><?= number_format($row['running_balance'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
    <?php if (!empty($running_balances)): ?>
    <tfoot>
        <tr>
            <td>Total</td>
            <td><?= number_format($total_budget, 2) ?></td>
            <td><?= number_format($total_expenses, 2) ?></td>
            <td><?= number_format($total_remaining, 2) ?></td>
        </tr>
    </tfoot>
    <?php endif; ?>
</table>

<?php if (!empty($running_balances)): ?>
<div class="mobile-total">
    <div><span>Total Budget</span><span><?= number_format($total_budget, 2) ?></span></div>
    <div><span>Total Expenses</span><span><?= number_format($total_expenses, 2) ?></span></div>
    <div><span>Remaining</span><span><?= number_format($total_remaining, 2) ?></span></div>
</div>
<?php endif; ?>

<div style="max-width: 900px; margin: 20px auto; text-align: center;">
    <button onclick="window.print()">Print / Export PDF</button>
</div>

<a href="dashboard.php">← Back to Dashboard</a>

</body>
</html>
