<?php
session_start();
require_once 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id    = $_SESSION['user_id'];
    $title      = trim($_POST['title']);
    $amount     = $_POST['amount'];
    $start_date = $_POST['start_date'];
    $end_date   = $_POST['end_date'];

    if ($title === '' || $amount === '' || $start_date === '' || $end_date === '') {
        $message = "❌ Please fill in all fields.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $message = "❌ Please enter a valid amount.";
    } elseif ($start_date > $end_date) {
        $message = "❌ Start date must be before end date.";
    } else {
        $sql = "INSERT INTO budget (user_id, title, amount, start_date, end_date) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$user_id, $title, $amount, $start_date, $end_date])) {
            $message = "✅ Budget added successfully!";
        } else {
            $message = "❌ Error adding budget.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Budget</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Added for mobile responsiveness -->
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: rgb(255, 244, 234); 
            margin: 0;
            padding: 40px;
        }
        .container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.07);
        }
        h2 {
            text-align: center;
            color: #1976d2;
            margin-bottom: 25px;
        }
        label {
            font-weight: 500;
            color: #333;
            display: block;
            margin-top: 15px;
        }
        input {
            width: 95%;
            padding: 12px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }
        button {
            margin-top: 25px;
            width: 100%;
            padding: 12px;
            background-color: #1976d2;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background-color: #1565c0;
        }
        .message {
            text-align: center;
            margin-top: 20px;
            font-weight: bold;
            color: #388e3c;
        }
        .error {
            text-align: center;
            margin-top: 20px;
            font-weight: bold;
            color: #d32f2f;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: #1976d2;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }

        /* Responsive styles for phones */
        @media (max-width: 480px) {
            body {
                padding: 20px;
            }
            .container {
                padding: 20px;
                border-radius: 10px;
            }
            h2 {
                font-size: 20px;
            }
            label {
                font-size: 14px;
            }
            input {
                width: 90%;
                font-size: 14px;
                padding: 10px;
            }
            button {
                font-size: 15px;
                padding: 10px;
            }
            .back-link {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Add New Budget</h2>
    <form method="POST">
        <label for="title">Budget Title</label>
        <input type="text" name="title" placeholder="e.g. January Budget" required />

        <label for="amount">Amount</label>
        <input type="number" step="0.01" name="amount" placeholder="e.g. 5000" required />

        <label for="start_date">Start Date</label>
        <input type="date" name="start_date" required />

        <label for="end_date">End Date</label>
        <input type="date" name="end_date" required />

        <button type="submit">Add Budget</button>

        <?php if ($message): ?>
            <div class="<?= strpos($message, '✅') !== false ? 'message' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
    </form>
    <a class="back-link" href="dashboard.php">← Back to Dashboard</a>
</div>

</body>
</html>
