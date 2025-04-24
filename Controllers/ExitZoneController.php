<?php
require_once 'DbController.php'; // Make sure this connects to your DB
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle POST: Record RFID exit log
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['rfid_tag']) || !isset($data['reader_id'])) {
        echo json_encode(["status" => "error", "message" => "Missing rfid_tag or reader_id"]);
        exit;
    }

    $rfidTag = trim($data['rfid_tag']);
    $readerId = trim($data['reader_id']);

    // Optional: Look up the book title
    $stmt = $pdo->prepare("SELECT book_title FROM reader_book_status WHERE assigned_tag = ?");
    $stmt->execute([$rfidTag]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    $bookTitle = $book ? $book['book_title'] : 'Unknown';

    try {
        // 1. Log to rfid_exit_logs
        $exitLog = $pdo->prepare("INSERT INTO rfid_exit_logs (rfid_tag, book_title, reader_id) VALUES (?, ?, ?)");
        $exitLog->execute([$rfidTag, $bookTitle, $readerId]);

        // 2. Log to rfid_logs (main log)
        $mainLog = $pdo->prepare("INSERT INTO rfid_logs (rfid_tag, book_title, reader_id, action_type) VALUES (?, ?, ?, 'taken_outside')");
        $mainLog->execute([$rfidTag, $bookTitle, $readerId]);

        echo json_encode([
            "status" => "success",
            "message" => "Exit scan recorded and logged",
            "book_title" => $bookTitle
        ]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET: Fetch latest exit log
    try {
        $stmt = $pdo->query("SELECT * FROM rfid_exit_logs ORDER BY scanned_at DESC LIMIT 1");
        $latest = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($latest);
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }

} else {
    // Invalid request method
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
