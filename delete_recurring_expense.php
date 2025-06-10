<?php
session_start();
require_once 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid ID.");
}

$id = (int)$_GET['id'];

// Verify that this expense belongs to user before deleting
$stmt = $pdo->prepare("SELECT id FROM recurring_expense WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$expense = $stmt->fetch();

if (!$expense) {
    die("Recurring expense not found or access denied.");
}

// Delete
$stmt = $pdo->prepare("DELETE FROM recurring_expense WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);

header("Location: view_recurring_expenses.php");
exit;
