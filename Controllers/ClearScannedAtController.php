<?php
require_once 'DbController.php';
header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Step 1: Get only rows with empty scanned_tag AND non-null last_scanned_at
        $stmt = $pdo->query("
            SELECT reader_id, complete_book_title, assigned_tag 
            FROM reader_book_status 
            WHERE (scanned_tag IS NULL OR scanned_tag = '') AND last_scanned_at IS NOT NULL
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            echo json_encode(['success' => true, 'message' => 'No eligible rows to update.']);
            exit;
        }

        // Step 2: Prepare once for performance
        $update = $pdo->prepare("UPDATE reader_book_status SET last_scanned_at = NULL WHERE reader_id = ?");
        $insertLog = $pdo->prepare("
            INSERT INTO rfid_logs (rfid_tag, book_title, reader_id, action_type)
            VALUES (?, ?, ?, 'unreturned')
        ");

        $logCount = 0;
        foreach ($rows as $row) {
            $readerId = $row['reader_id'];
            $rfidTag = $row['assigned_tag'];
            $bookTitle = $row['complete_book_title']; // using complete_book_title now

            // Step 3: Update and Log
            $update->execute([$readerId]);
            $insertLog->execute([$rfidTag, $bookTitle, $readerId]);
            $logCount++;
        }

        echo json_encode([
            'success' => true,
            'message' => "$logCount unreturned logs created and last_scanned_at cleared."
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
