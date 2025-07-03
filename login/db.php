<?php
$host = "localhost";
$dbname = "combatives_hub";
$username = "root";
$password = "IQ2CuOF8R!iQ";
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Enable exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, // Use real prepared statements
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // You can log errors here or show a user-friendly message
    die("Database connection failed: " . $e->getMessage());
}
