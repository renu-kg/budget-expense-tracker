<?php
session_start();
require_once 'db/config.php';

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = trim($_POST['site_name']);
    $currency = trim($_POST['currency']);
    $theme = trim($_POST['theme']);

    $sql = "UPDATE system_info SET site_name = ?, currency = ?, theme = ? WHERE id = 1";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$site_name, $currency, $theme])) {
        $success = "Settings updated successfully.";
    } else {
        $error = "Failed to update settings.";
    }
}

// Fetch current settings
$stmt = $pdo->query("SELECT * FROM system_info WHERE id = 1");
$settings = $stmt->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <title>System Settings</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 40px; }
        form { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        label { display: block; margin-top: 10px; }
        input[type="text"], select { width: 100%; padding: 10px; margin-top: 5px; }
        button { margin-top: 20px; padding: 10px 20px; background-color: #007bff; border: none; color: white; cursor: pointer; }
        .alert { margin-top: 15px; padding: 10px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<h2 style="text-align:center;">System Settings</h2>

<form method="POST">
    <label for="site_name">Site Name:</label>
    <input type="text" name="site_name" id="site_name" value="<?= htmlspecialchars($settings['site_name']) ?>" required>

    <label for="currency">Currency (e.g., USD, INR):</label>
    <input type="text" name="currency" id="currency" value="<?= htmlspecialchars($settings['currency']) ?>" required>

    <label for="theme">Theme:</label>
    <select name="theme" id="theme">
        <option value="light" <?= $settings['theme'] === 'light' ? 'selected' : '' ?>>Light</option>
        <option value="dark" <?= $settings['theme'] === 'dark' ? 'selected' : '' ?>>Dark</option>
    </select>

    <button type="submit">Update Settings</button>

    <?php if ($success): ?>
        <div class="alert success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>
</form>

</body>
</html>
