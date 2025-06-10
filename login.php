<?php
session_start();
require_once 'db/config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    } else {
        $message = "❌ Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
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

        form {
            background: white;
            padding: 13px 25px 25px 25px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: #1976d2;
            margin-bottom: 20px;
        }

        input {
            width: 95%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
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
            text-align: center;
            color: red;
            margin-top: 10px;
        }

        .link {
            text-align: center;
            margin-top: 15px;
        }

        .link a {
            color: #1976d2;
            text-decoration: none;
        }

        .link a:hover {
            text-decoration: underline;
        }

        /* Responsive Design for Mobile */
        @media screen and (max-width: 480px) {
            body {
                padding: 15px;
                align-items: flex-start;
                background-size: cover;
            }

            form {
                margin-top: 50px;
                padding: 20px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                width: 100%;
            }

            input {
                width: 90%;
                font-size: 16px;
            }

            button {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<form method="POST">
    <h2>Login</h2>
    <input type="email" name="email" placeholder="Email" required />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit">Login</button>
    <div class="msg"><?= $message ?></div>
    <div class="link">
        Don't have an account? <a href="register.php">Register here</a>
    </div>
</form>

</body>
</html>
