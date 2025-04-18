<?php
// store-rfid.php

// Database connection settings
$host = 'localhost';
$dbname = 'esp32_db';  // Database name
$username = 'root';  // Default username for XAMPP MySQL
$password = '';  // Default password for XAMPP MySQL is empty

// Create a new PDO instance for MySQL connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $jsonData = file_get_contents('php://input');
    
    // Decode the JSON data
    $data = json_decode($jsonData, true);
    
    // Check if RFID tag is provided in the request
    if (isset($data['rfid_tag'])) {
        $rfidTag = $data['rfid_tag'];
        
        // Insert the RFID tag into the database
        try {
            $stmt = $pdo->prepare("INSERT INTO rfid_tags (rfid_tag) VALUES (:rfid_tag)");
            $stmt->bindParam(':rfid_tag', $rfidTag);
            $stmt->execute();
            
            echo json_encode(["status" => "success", "message" => "RFID tag stored successfully!"]);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Missing RFID tag."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
