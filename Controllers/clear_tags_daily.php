<?php
require_once 'DbController.php';
date_default_timezone_set('Asia/Manila');

// Only rows that are truly "just emptied"
$stmt = $pdo->query("
    SELECT reader_id, assigned_tag, complete_book_title 
    FROM reader_book_status 
    WHERE scanned_tag = '' AND last_scanned_at IS NOT NULL
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare statements
$update = $pdo->prepare("UPDATE reader_book_status SET last_scanned_at = NULL WHERE reader_id = ?");
$log = $pdo->prepare("
    INSERT INTO rfid_logs (rfid_tag, book_title, reader_id, action_type)
    VALUES (?, ?, ?, 'unreturned')
");

$logCount = 0;

foreach ($rows as $row) {
    $readerId = $row['reader_id'];
    $rfidTag = $row['assigned_tag'];
    $bookTitle = $row['complete_book_title']; // now using complete_book_title

    // 1. Set last_scanned_at to NULL
    $update->execute([$readerId]);

    // 2. Log as unreturned
    $log->execute([$rfidTag, $bookTitle, $readerId]);
    $logCount++;
}

echo "✅ $logCount logs created for newly unreturned books using complete_book_title.";
