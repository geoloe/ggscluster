<?php
session_start();

// Check if user is logged in and has the required role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], [1, 2])) {
    header('Location: unauthorized.php'); // Redirect to a custom page
    exit();
}

// Define the plain text password
$password = 'secureViewerPassword123';

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Output the hashed password
echo "Hashed Password: " . $hashedPassword;
?>