<?php
// config.php

// Database connection settings
$host = "localhost";
$username = "root";
$password = "";
$database = "ledger_system";

try {
    // Establish a connection using PDO
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    // Set error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to check user role
function checkUserRole($requiredRole) {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $requiredRole) {
        header('Location: login.php');
        exit;
    }
}
?>
