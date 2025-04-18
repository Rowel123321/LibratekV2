<?php
// add_book.php
include 'db.php';  // Include the database connection

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $title = $_POST['title'];
    $year = $_POST['year'];
    $tags = $_POST['tags']; // New field for tags
    $status = $_POST['status']; // New field for status

    // Insert the new book into the database
    try {
        $stmt = $pdo->prepare("INSERT INTO books (title, year, tags, status) VALUES (:title, :year, :tags, :status)");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':tags', $tags);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        
        echo "Book added successfully!";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
