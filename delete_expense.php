<?php
session_start();
require_once 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: view_expenses.php");
    exit;
}

$expense_id = intval($_GET['id']);

// Delete only if expense belongs to user
$sql = "DELETE FROM expenses WHERE id = ? AND user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$expense_id, $user_id]);

header("Location: view_expenses.php");
exit;
