<?php
require_once 'DbController.php'; // Make sure this defines $pdo

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

    // 🟥 If tag is removed
    if (isset($data['state']) && $data['state'] === 'removed') {
        // Get current scanned_tag and book title for logging
        $fetch = $pdo->prepare("SELECT scanned_tag, book_title FROM reader_book_status WHERE reader_id = ?");
        $fetch->execute([$readerId]);
        $row = $fetch->fetch(PDO::FETCH_ASSOC);

        $scannedTag = $row['scanned_tag'] ?? '';
        $bookTitle = $row['book_title'] ?? 'Unknown';

        // Clear scanned_tag and update time
        $stmt = $pdo->prepare("
            UPDATE reader_book_status 
            SET scanned_tag = '', last_scanned_at = NOW() 
            WHERE reader_id = ?
        ");
        $stmt->execute([$readerId]);

        // ✅ Log as removed (only if a tag was there)
        if (!empty($scannedTag)) {
            $log = $pdo->prepare("
                INSERT INTO rfid_logs (rfid_tag, book_title, reader_id, action_type) 
                VALUES (?, ?, ?, 'removed')
            ");
            $log->execute([$scannedTag, $bookTitle, $readerId]);
        }

        echo json_encode([
            "status" => "success",
            "message" => "scanned_tag cleared, time updated, and removed logged"
        ]);
        exit;
    }

    // 🟢 If a tag is scanned
    if (isset($data['rfid_tag'])) {
        $scannedTag = trim($data['rfid_tag']);

        // Update scanned_tag and timestamp
        $stmt = $pdo->prepare("
            UPDATE reader_book_status 
            SET scanned_tag = ?, last_scanned_at = NOW() 
            WHERE reader_id = ?
        ");
        $stmt->execute([$scannedTag, $readerId]);

        // Fetch book info
        $bookCheck = $pdo->prepare("SELECT book_title, assigned_tag FROM reader_book_status WHERE reader_id = ?");
        $bookCheck->execute([$readerId]);
        $book = $bookCheck->fetch(PDO::FETCH_ASSOC);

        if ($book) {
            $assignedTag = trim($book['assigned_tag']);
            $bookTitle = $book['book_title'];

            if ($assignedTag === $scannedTag) {
                $log = $pdo->prepare("
                    INSERT INTO rfid_logs (rfid_tag, book_title, reader_id, action_type) 
                    VALUES (?, ?, ?, 'normal')
                ");
                $log->execute([$scannedTag, $bookTitle, $readerId]);
            } else {
                $log = $pdo->prepare("
                    INSERT INTO rfid_logs (rfid_tag, book_title, reader_id, action_type) 
                    VALUES (?, ?, ?, 'misplaced')
                ");
                $log->execute([$scannedTag, $bookTitle, $readerId]);
            }
        }

        echo json_encode([
            "status" => "success",
            "message" => "scanned_tag set, time updated, and logged accordingly"
        ]);
        exit;
    }

    // Invalid input
    echo json_encode([
        "status" => "error",
        "message" => "Missing RFID tag or invalid state"
    ]);
}
?>
