<?php
try {
    $pdo = new PDO('mysql:host=mysql-db;dbname=file_server', 'fileuser', 'filepassword');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}
?>