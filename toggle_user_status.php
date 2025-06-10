<?php
session_start();
require_once 'db/config.php';

// Ensure only admins can perform this action
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Prevent admin from deactivating themselves
    if ($user_id == $_SESSION['user_id']) {
        header("Location: manage_users.php?error=self");
        exit;
    }

    // Fetch current status
    $stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user) {
        $new_status = $user['is_active'] ? 0 : 1;
        $update = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        $update->execute([$new_status, $user_id]);
    }
}

header("Location: manage_users.php");
exit;
