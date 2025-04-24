<?php
require_once 'DbController.php'; // Make sure this connects to your DB

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle log insertion
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['rfid_tag'], $data['book_title'], $data['reader_id'], $data['action_type'])) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit;
    }

    $rfidTag = trim($data['rfid_tag']);
    $bookTitle = trim($data['book_title']);
    $readerId = trim($data['reader_id']);
    $actionType = trim($data['action_type']);

    $allowedActions = ['normal', 'taken_outside', 'misplaced', 'unreturned', 'unauthorized', 'reshelved'];
    if (!in_array($actionType, $allowedActions)) {
        echo json_encode(["status" => "error", "message" => "Invalid action_type"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO rfid_logs (rfid_tag, book_title, reader_id, action_type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$rfidTag, $bookTitle, $readerId, $actionType]);

        echo json_encode([
            "status" => "success",
            "message" => "Log entry recorded",
            "data" => [
                "rfid_tag" => $rfidTag,
                "book_title" => $bookTitle,
                "reader_id" => $readerId,
                "action_type" => $actionType
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle fetching logs
    try {
        $stmt = $pdo->query("SELECT * FROM rfid_logs ORDER BY created_at DESC");
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($logs);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }

} else {
    // Reject other HTTP methods
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
