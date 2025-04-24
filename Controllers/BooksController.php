<?php
// Include the database connection
include 'DbController.php'; // This must define $pdo (your PDO connection object)

header('Content-Type: application/json');

try {
    // Fetch everything from the table
    $sql = "SELECT * FROM reader_book_status";
    $stmt = $pdo->query($sql);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($books);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
