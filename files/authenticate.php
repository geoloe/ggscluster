<?php
session_start();

require 'db.php';  // Your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch the user from the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Use password_verify to check the password
        if (password_verify($password, $user['password'])) {
            // Login success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role_id'];
            header("Location: index.php");  // Redirect to main page
            exit();
        } else {
            // Invalid password
            header("Location: login.php?error=1");  // Add an error parameter to show error message
            exit();
        }
    } else {
        // No user found
        header("Location: login.php?error=1");
        exit();
    }
}
?>