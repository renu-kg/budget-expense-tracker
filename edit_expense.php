<?php
session_start();
require_once 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: view_expenses.php");
    exit;
}

$expense_id = intval($_GET['id']);

// Fetch the expense to edit, only if it belongs to the user
$sql = "SELECT * FROM expenses WHERE id = ? AND user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$expense_id, $user_id]);
$expense = $stmt->fetch();

if (!$expense) {
    header("Location: view_expenses.php");
    exit;
}

// Fetch user's budgets for dropdown
$budget_sql = "SELECT id, title FROM budget WHERE user_id = ?";
$stmt = $pdo->prepare($budget_sql);
$stmt->execute([$user_id]);
$budgets = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $budget_id = $_POST['budget_id'] ?: null;
    $category = trim($_POST['category']);
    $amount = floatval($_POST['amount']);
    $expense_date = $_POST['expense_date'];
    $description = trim($_POST['description']);

    if ($category === '' || $amount <= 0 || $expense_date === '') {
        $message = "Please fill in all required fields with valid values.";
    } else {
        $update_sql = "UPDATE expenses SET budget_id = ?, category = ?, amount = ?, expense_date = ?, description = ? WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($update_sql);
        if ($stmt->execute([$budget_id, $category, $amount, $expense_date, $description, $expense_id, $user_id])) {
            $message = "Expense updated successfully!";
            // Refresh the data
            $stmt = $pdo->prepare("SELECT * FROM expenses WHERE id = ? AND user_id = ?");
            $stmt->execute([$expense_id, $user_id]);
            $expense = $stmt->fetch();
        } else {
            $message = "Error updating expense.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Expense</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: rgb(255, 244, 234);
            padding: 40px;
            margin: 0;
        }

        .form-container {
            max-width: 450px;
            margin: auto;
            background: white;
            padding: 25px 30px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }

        h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #1976d2;
            text-align: center;
            font-weight: 600;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
            color: #555;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 15px;
            background: #fefefe;
            box-sizing: border-box;
            resize: vertical;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        textarea {
            min-height: 80px;
        }

        button {
            margin-top: 25px;
            padding: 12px;
            width: 100%;
            background-color: #1976d2;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            margin-top: 20px;
            padding: 12px;
            border-radius: 8px;
            font-weight: 500;
            text-align: center;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
        }

        a {
            display: block;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
            font-weight: 500;
            text-align: center;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Responsive for phones */
        @media (max-width: 600px) {
            body {
                padding: 20px 10px;
            }

            .form-container {
                padding: 20px 15px;
                border-radius: 12px;
                box-shadow: 0 6px 15px rgba(0,0,0,0.08);
            }

            h2 {
                font-size: 20px;
                margin-bottom: 15px;
            }

            label {
                margin-top: 12px;
                font-size: 14px;
            }

            input[type="text"],
            input[type="number"],
            input[type="date"],
            select,
            textarea {
                font-size: 14px;
                padding: 10px;
            }

            button {
                font-size: 15px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Expense</h2>

    <form method="POST" novalidate>
        <label for="budget_id">Budget (optional)</label>
        <select id="budget_id" name="budget_id">
            <option value="">-- Select Budget --</option>
            <?php foreach ($budgets as $budget): ?>
                <option value="<?= $budget['id'] ?>" <?= $expense['budget_id'] == $budget['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($budget['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="category">Category</label>
        <input type="text" id="category" name="category" placeholder="Category" required value="<?= htmlspecialchars($expense['category']) ?>" />

        <label for="amount">Amount</label>
        <input type="number" id="amount" step="0.01" min="0" name="amount" placeholder="Amount" required value="<?= htmlspecialchars($expense['amount']) ?>" />

        <label for="expense_date">Expense Date</label>
        <input type="date" id="expense_date" name="expense_date" required value="<?= htmlspecialchars($expense['expense_date']) ?>" />

        <label for="description">Description</label>
        <textarea id="description" name="description" placeholder="Description"><?= htmlspecialchars($expense['description']) ?></textarea>

        <button type="submit">Update Expense</button>

        <?php if ($message): ?>
            <?php
            $class = (strpos($message, 'successfully') !== false) ? 'message success' : 'message error';
            ?>
            <div class="<?= $class ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
    </form>

    <a href="view_expenses.php">← Back to Expenses</a>
</div>

</body>
</html>
