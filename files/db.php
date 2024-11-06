<?php
session_start();

// Check if user is logged in and has the required role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], [1, 2])) {
    header('Location: unauthorized.php'); // Redirect to a custom page
    exit();
}

try {
    $pdo = new PDO('mysql:host=mysql-db;dbname=file_server', 'fileuser', 'filepassword');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}
?>