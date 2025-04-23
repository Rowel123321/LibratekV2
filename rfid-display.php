<?php
require_once 'db.php'; // Make sure this defines $pdo

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM reader_book_status");
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($books);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    if (!isset($data['reader_id'])) {
        echo json_encode(["status" => "error", "message" => "Missing reader_id"]);
        exit;
    }

    $readerId = trim($data['reader_id']);

    if (isset($data['state']) && $data['state'] === 'removed') {
        // ✅ Only clear scanned_tag — keep last_scanned_at
        $stmt = $pdo->prepare("UPDATE reader_book_status SET scanned_tag = '' WHERE reader_id = ?");
        $stmt->execute([$readerId]);
        echo json_encode(["status" => "success", "message" => "scanned_tag cleared for reader_id $readerId"]);
    } elseif (isset($data['rfid_tag'])) {
        $scannedTag = trim($data['rfid_tag']);

        // ✅ Set scanned_tag and update timestamp
        $stmt = $pdo->prepare("UPDATE reader_book_status SET scanned_tag = ?, last_scanned_at = NOW() WHERE reader_id = ?");
        $stmt->execute([$scannedTag, $readerId]);

        echo json_encode(["status" => "success", "message" => "scanned_tag set and timestamp updated for reader_id $readerId"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Missing RFID tag or invalid state"]);
    }
}
?>
