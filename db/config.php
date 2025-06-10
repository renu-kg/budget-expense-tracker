<?php
$host = 'sql207.infinityfree.com';
$db   = 'if0_39185476_expense_tracker';
$user = 'if0_39185476'; // default XAMPP username
$pass = 'MVQb3b8bgSpcXt';     // default XAMPP password is empty
$charset = 'utf8mb4';

// DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Options for better error handling
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // echo "✅ Connected successfully"; // Uncomment this to test
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
