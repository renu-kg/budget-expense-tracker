<?php
session_start();
require_once 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

$budget_id = $_GET['id'] ?? null;
if (!$budget_id) {
    header("Location: dashboard.php");
    exit;
}

// Fetch existing budget details
$sql = "SELECT * FROM budget WHERE id = ? AND user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$budget_id, $user_id]);
$budget = $stmt->fetch();

if (!$budget) {
    header("Location: dashboard.php");
    exit;
}

$title = $budget['title'];
$amount = $budget['amount'];
$start_date = $budget['start_date'];
$end_date = $budget['end_date'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $amount = floatval($_POST['amount']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    if ($title === '' || $amount <= 0 || $start_date === '' || $end_date === '') {
        $message = "Please fill all fields with valid values.";
    } elseif ($end_date < $start_date) {
        $message = "End date cannot be earlier than start date.";
    } else {
        $update_sql = "UPDATE budget SET title = ?, amount = ?, start_date = ?, end_date = ? WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($update_sql);
        if ($stmt->execute([$title, $amount, $start_date, $end_date, $budget_id, $user_id])) {
            $message = "Budget updated successfully!";
        } else {
            $message = "Error updating budget.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Budget</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
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
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
            color: #555;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 15px;
            background: #fefefe;
            box-sizing: border-box;
        }

        button {
            margin-top: 20px;
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
            margin-top: 15px;
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

        /* Responsive adjustments for phone view */
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
            input[type="date"] {
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
    <h2>Edit Budget</h2>

    <form method="POST" novalidate>
        <label for="title">Budget Title</label>
        <input type="text" id="title" name="title" required value="<?= htmlspecialchars($title) ?>" />

        <label for="amount">Amount</label>
        <input type="number" id="amount" name="amount" step="0.01" min="0" required value="<?= htmlspecialchars($amount) ?>" />

        <label for="start_date">Start Date</label>
        <input type="date" id="start_date" name="start_date" required value="<?= htmlspecialchars($start_date) ?>" />

        <label for="end_date">End Date</label>
        <input type="date" id="end_date" name="end_date" required value="<?= htmlspecialchars($end_date) ?>" />

        <button type="submit">Update Budget</button>

        <?php if ($message): ?>
            <?php
                $class = (stripos($message, 'success') !== false) ? 'success' : 'error';
            ?>
            <div class="message <?= $class ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
    </form>

    <a href="dashboard.php">← Back to Dashboard</a>
</div>

</body>
</html>
