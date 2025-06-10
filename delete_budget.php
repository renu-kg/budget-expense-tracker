<?php
session_start();
require_once 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request.");
}

$budget_id = intval($_GET['id']);

// Make sure the budget belongs to the logged-in user
$stmt = $pdo->prepare("DELETE FROM budget WHERE id = ? AND user_id = ?");
$success = $stmt->execute([$budget_id, $user_id]);

if ($success) {
    header("Location: view_budgets.php?deleted=1");
    exit;
} else {
    die("Error deleting the budget.");
}
