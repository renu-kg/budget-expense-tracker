<?php
session_start();
require_once '../db/config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = $_GET['id'];

    // Prevent admin from deleting themselves
    if ($user_id == $_SESSION['user_id']) {
        header("Location: view_users.php?error=cannot_delete_self");
        exit;
    }

    // Delete user
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$user_id])) {
        header("Location: view_users.php?deleted=1");
    } else {
        header("Location: view_users.php?error=delete_failed");
    }
} else {
    header("Location: view_users.php?error=invalid_id");
}
exit;
