<?php
// db.php - for connecting to your existing database
$host = 'localhost';    // Replace with your host (e.g., 'localhost')
$db = 'esp32_db';   // Replace with your existing database name
$user = 'root'; // Replace with your MySQL username
$pass = ''; // Replace with your MySQL password

// Create a new PDO instance to connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // Set error mode
} catch (PDOException $e) {
    die("Could not connect to the database $db :" . $e->getMessage());  // Handle connection errors
}
?>
