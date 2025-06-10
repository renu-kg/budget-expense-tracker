<?php
session_start();
require_once 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch categories
try {
    $stmt = $pdo->prepare("SELECT DISTINCT category FROM expenses WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $error = "Error fetching categories: " . $e->getMessage();
    $categories = [];
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category']);
    $amount = floatval($_POST['amount']);
    $frequency = $_POST['frequency'];
    $start_date = $_POST['start_date'];
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

    try {
        $stmt = $pdo->prepare("INSERT INTO recurring_expense (user_id, category, amount, frequency, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $category, $amount, $frequency, $start_date, $end_date]);
        $success = "Recurring expense added successfully.";
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Recurring Expense</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: rgb(255, 244, 234);
            padding: 30px;
            margin: 0;
        }

        .container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #1976d2;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-top: 10px;
            font-weight: 500;
            font-size: 14px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
            box-sizing: border-box;
        }

        button {
            padding: 12px 20px;
            background-color: #1976d2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 15px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            font-size: 14px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        a {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: #007bff;
            font-size: 14px;
            text-align: center;
        }

        a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            body {
                padding: 15px;
            }

            .container {
                padding: 20px;
                border-radius: 0;
                box-shadow: none;
            }

            h2 {
                font-size: 20px;
            }

            input, select, button {
                font-size: 15px;
                padding: 10px;
            }

            label {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Add Recurring Expense</h2>

    <?php if ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Category</label>
        <select name="category" required>
            <option value="">-- Select Expense Category --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Amount</label>
        <input type="number" name="amount" step="0.01" required>

        <label>Frequency</label>
        <select name="frequency" required>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly" selected>Monthly</option>
            <option value="yearly">Yearly</option>
        </select>

        <label>Start Date</label>
        <input type="date" name="start_date" required>

        <label>End Date</label>
        <input type="date" name="end_date">

        <button type="submit">Save Recurring Expense</button>
    </form>

    <a href="dashboard.php">&larr; Go to Dashboard</a>
</div>

</body>
</html>
