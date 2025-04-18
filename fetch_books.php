<?php
// Include the database connection
include 'db.php';  // Make sure db.php is in the 'includes' folder

// Query to get books and tags from the database
$sql = "SELECT title, year, tags FROM books ORDER BY year"; // Ensure tags column is included
$stmt = $pdo->prepare($sql);
$stmt->execute();

// Fetch and return books with their tags as JSON
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set the response header as JSON
header('Content-Type: application/json');

// Return the books as a JSON response
echo json_encode($books);
?>
