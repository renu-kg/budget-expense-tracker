<?php
require_once 'db/config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $message = "❌ Email is already registered!";
    } else {
        $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$name, $email, $password])) {
            $user_id = $pdo->lastInsertId();

            $default_categories = ['Food', 'Transport', 'Electricity', 'Water', 'Groceries', 'Internet', 'Entertainment', 'Rent', 'Medical', 'Savings'];
            $cat_stmt = $pdo->prepare("INSERT INTO categories (user_id, name) VALUES (?, ?)");
            foreach ($default_categories as $cat) {
                $cat_stmt->execute([$user_id, $cat]);
            }

            header("Location: login.php?registered=1");
            exit;
        } else {
            $message = "❌ Registration failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('assets/bg-register.png') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px 35px;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.1);
            max-width: 450px;
            width: 90%;
        }

        .header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }

        .logo {
            width: 50px;
            height: 50px;
            margin-right: 15px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #1976d2;
        }

        h2 {
            font-size: 20px;
            color: #1976d2;
            text-align: left;
            margin-bottom: 15px;
        }

        form {
            text-align: left;
        }

        input {
            width: 95%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #1976d2;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background-color: #125ea2;
        }

        .msg {
            color: red;
            margin-top: 10px;
            font-size: 14px;
        }

        .link {
            margin-top: 15px;
            font-size: 17px;
            text-align: left;
        }

        .link a {
            color: #1976d2;
            text-decoration: none;
        }

        .link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            body {
                padding: 20px;
                align-items: flex-start;
            }

            .container {
                padding: 25px 20px;
                margin-top: 50px;
                box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            }

            .header {
                flex-direction: row;
                align-items: center;
                justify-content: flex-start;
            }

            .logo {
                width: 40px;
                height: 40px;
                margin-right: 10px;
            }

            .title {
                font-size: 20px;
            }

            input {
                width: 90%;
                font-size: 16px;
            }

            button {
                font-size: 16px;
            }

            .link {
                font-size: 15px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <img src="assets/logo.png" alt="Logo" class="logo">
        <div class="title">Expense and Budget Tracker</div>
    </div>

    <form method="POST">
        <h2>Register</h2>
        <input type="text" name="name" placeholder="Full Name" required />
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Register</button>
        <div class="msg"><?= $message ?></div>
        <div class="link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </form>
</div>

</body>
</html>
