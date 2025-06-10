<?php
session_start();
require_once 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid ID.");
}

$id = (int)$_GET['id'];

// Fetch the existing recurring expense
$stmt = $pdo->prepare("SELECT * FROM recurring_expense WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$expense = $stmt->fetch();

if (!$expense) {
    die("Recurring expense not found or access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category']);
    $amount = floatval($_POST['amount']);
    $frequency = $_POST['frequency'];
    $start_date = $_POST['start_date'];
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

    try {
        $stmt = $pdo->prepare("UPDATE recurring_expense SET category = ?, amount = ?, frequency = ?, start_date = ?, end_date = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$category, $amount, $frequency, $start_date, $end_date, $id, $user_id]);
        $success = "Recurring expense updated successfully.";

        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM recurring_expense WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        $expense = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: rgb(255, 244, 234);  
            padding: 30px; 
            color: #333; 
        }
        .container { 
            max-width: 600px; 
            margin: auto; 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.1); 
        }
        h2 { 
            text-align: center; 
            margin-bottom: 25px; 
        }
        label { 
            display: block; 
            margin-top: 15px; 
            font-weight: 600; 
        }
        input, select, button {
            width: 95%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        button { 
            margin-top: 20px; 
            background-color: #007bff; 
            border: none; 
            color: white; 
            cursor: pointer; 
            transition: background-color 0.3s ease; 
        }
        button:hover { 
            background-color: #0056b3; 
        }
        .message { 
            margin-top: 15px; 
            padding: 10px; 
            border-radius: 5px; 
        }
        .success { 
            background-color: #d4edda; 
            color: #155724; 
        }
        .error { 
            background-color: #f8d7da; 
            color: #721c24; 
        }
        a.back-link { 
            display: block; 
            margin-top: 20px; 
            color: #007bff; 
            text-decoration: none; 
            text-align: center;
        }
        a.back-link:hover { 
            text-decoration: underline; 
        }

        /* Responsive for mobile */
        @media (max-width: 480px) {
            body {
                padding: 15px 10px;
            }
            .container {
                padding: 15px;
                max-width: 100%;
                border-radius: 6px;
                box-shadow: none;
            }
            input, select, button {
                width: 100%;
                font-size: 14px;
            }
            label {
                margin-top: 12px;
                font-size: 18px;
            }
            button {
                padding: 14px 0;
                font-size: 18px;
            }
            a.back-link {
                font-size: 16px;
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Recurring Expense</h2>

    <?php if ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Category</label>
        <input type="text" name="category" value="<?= htmlspecialchars($expense['category']) ?>" required>

        <label>Amount</label>
        <input type="number" name="amount" step="0.01" value="<?= htmlspecialchars($expense['amount']) ?>" required>

        <label>Frequency</label>
        <select name="frequency" required>
            <option value="daily" <?= $expense['frequency'] === 'daily' ? 'selected' : '' ?>>Daily</option>
            <option value="weekly" <?= $expense['frequency'] === 'weekly' ? 'selected' : '' ?>>Weekly</option>
            <option value="monthly" <?= $expense['frequency'] === 'monthly' ? 'selected' : '' ?>>Monthly</option>
            <option value="yearly" <?= $expense['frequency'] === 'yearly' ? 'selected' : '' ?>>Yearly</option>
        </select>

        <label>Start Date</label>
        <input type="date" name="start_date" value="<?= htmlspecialchars($expense['start_date']) ?>" required>

        <label>End Date (Optional)</label>
        <input type="date" name="end_date" value="<?= htmlspecialchars($expense['end_date']) ?>">

        <button type="submit">Update Recurring Expense</button>
    </form>

    <a href="view_recurring_expenses.php" class="back-link">&larr; Back to Recurring Expenses</a>
</div>

</body>
</html>
