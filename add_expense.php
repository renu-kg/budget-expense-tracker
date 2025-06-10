<?php
session_start();
require_once 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

// Fetch budgets
$sql = "SELECT id, title FROM budget WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$budgets = $stmt->fetchAll();

// Fetch categories
$sql = "SELECT id, name FROM categories WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $budget_id = $_POST['budget_id'] ?? null;
    $category = trim($_POST['category']);
    if ($category === '__custom__') {
        $category = trim($_POST['custom_category']);
    }
    $amount = floatval($_POST['amount']);
    $expense_date = $_POST['expense_date'];
    $description = trim($_POST['description']);

    if (($budget_id === null || $budget_id === '') && $category === '') {
        $message = "Please select a budget or enter a category.";
    } elseif ($amount <= 0 || $expense_date === '') {
        $message = "Please fill in all required fields with valid values.";
    } else {
        $budget_amount = 0;
        if ($budget_id) {
            $stmt = $pdo->prepare("SELECT amount FROM budget WHERE id = ? AND user_id = ?");
            $stmt->execute([$budget_id, $user_id]);
            $row = $stmt->fetch();
            $budget_amount = $row ? floatval($row['amount']) : 0;
        }

        $month_start = date('Y-m-01', strtotime($expense_date));
        $month_end = date('Y-m-t', strtotime($expense_date));

        if ($budget_id) {
            $stmt2 = $pdo->prepare("SELECT SUM(amount) AS total_expense FROM expenses WHERE user_id = ? AND budget_id = ? AND expense_date BETWEEN ? AND ?");
            $stmt2->execute([$user_id, $budget_id, $month_start, $month_end]);
        } else {
            $stmt2 = $pdo->prepare("SELECT SUM(amount) AS total_expense FROM expenses WHERE user_id = ? AND category = ? AND expense_date BETWEEN ? AND ?");
            $stmt2->execute([$user_id, $category, $month_start, $month_end]);
        }

        $expense_row = $stmt2->fetch();
        $total_expense = $expense_row['total_expense'] ? floatval($expense_row['total_expense']) : 0;
        $new_total = $total_expense + $amount;

        if ($budget_amount > 0 && $new_total > $budget_amount) {
            $message = "Warning: Adding this expense will exceed your budget limit.";
        } else {
            $stmt3 = $pdo->prepare("INSERT INTO expenses (user_id, budget_id, category, amount, expense_date, description) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt3->execute([$user_id, $budget_id, $category, $amount, $expense_date, $description])) {
                $message = "Expense added successfully!";
            } else {
                $message = "Error adding expense.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Expense</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        body {
            margin: 0;
            background: rgb(255, 244, 234);
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .card {
            width: 100%;
            max-width: 500px;
            background: #fff;
            padding: 30px 20px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }
        h2 {
            margin-bottom: 25px;
            font-size: 24px;
            color: #1976d2;
            text-align: center;
        }
        label {
            font-weight: 600;
            color: #555;
            display: block;
            margin-bottom: 6px;
            margin-top: 16px;
        }
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 15px;
            background: #fefefe;
        }
        textarea {
            resize: vertical;
            min-height: 90px;
        }
        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background: #1976d2;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background: #0056b3;
        }
        .message {
            margin-top: 20px;
            padding: 12px;
            border-radius: 8px;
            font-weight: 500;
            text-align: center;
        }
        .message.success { background: #d4edda; color: #155724; }
        .message.warning { background: #fff3cd; color: #856404; }
        .message.error   { background: #f8d7da; color: #721c24; }
        a.back {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-weight: 500;
            color: #007bff;
            text-decoration: none;
        }
        a.back:hover {
            text-decoration: underline;
        }
        #custom_category {
            margin-top: 10px;
            display: none;
        }

        @media (max-width: 600px) {
            body {
                padding: 20px 10px;
            }
            .card {
                padding: 20px 15px;
            }
            h2 {
                font-size: 20px;
            }
            input, select, textarea {
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
<div class="card">
    <form method="POST">
        <h2>Add New Expense</h2>

        <label for="budget_id">Select Budget</label>
        <select id="budget_id" name="budget_id">
            <option value="">-- Select Budget --</option>
            <?php foreach ($budgets as $b): ?>
                <option value="<?= htmlspecialchars($b['id']) ?>" <?= (isset($budget_id) && $budget_id == $b['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($b['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="category">Select Category</label>
        <select name="category" id="category" onchange="toggleCustomCategory(this)">
            <option value="">-- Select a Category --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat['name']) ?>" <?= (isset($category) && $category == $cat['name']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
            <option value="__custom__" <?= (isset($_POST['category']) && $_POST['category'] == '__custom__') ? 'selected' : '' ?>>Other (Type New)</option>
        </select>

        <input type="text" id="custom_category" name="custom_category" placeholder="Enter new category"
               value="<?= isset($_POST['custom_category']) ? htmlspecialchars($_POST['custom_category']) : '' ?>" />

        <label for="amount">Amount</label>
        <input type="number" step="0.01" id="amount" name="amount" required value="<?= isset($amount) ? htmlspecialchars($amount) : '' ?>" />

        <label for="expense_date">Expense Date</label>
        <input type="date" id="expense_date" name="expense_date" required value="<?= isset($expense_date) ? htmlspecialchars($expense_date) : '' ?>" />

        <label for="description">Description (optional)</label>
        <textarea id="description" name="description"><?= isset($description) ? htmlspecialchars($description) : '' ?></textarea>

        <button type="submit">Add Expense</button>

        <?php if ($message): ?>
            <?php
                $class = 'error';
                if (stripos($message, 'success') !== false) $class = 'success';
                else if (stripos($message, 'warning') !== false) $class = 'warning';
            ?>
            <div class="message <?= $class ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <a class="back" href="dashboard.php">← Back to Dashboard</a>
    </form>
</div>

<script>
function toggleCustomCategory(selectEl) {
    const customInput = document.getElementById('custom_category');
    if (selectEl.value === '__custom__') {
        customInput.style.display = 'block';
        customInput.required = true;
    } else {
        customInput.style.display = 'none';
        customInput.required = false;
    }
}
window.onload = () => toggleCustomCategory(document.getElementById('category'));
</script>
</body>
</html>
