<?php
require_once 'DbController.php';
date_default_timezone_set('Asia/Manila');

// Get all records where scanned_tag is already empty
$stmt = $pdo->query("SELECT reader_id FROM reader_book_status WHERE scanned_tag = ''");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For each, set last_scanned_at to NULL
foreach ($rows as $row) {
    $readerId = $row['reader_id'];

    $update = $pdo->prepare("
        UPDATE reader_book_status
        SET last_scanned_at = NULL
        WHERE reader_id = ?
    ");
    $update->execute([$readerId]);
}

echo "last_scanned_at set to NULL where scanned_tag is empty.\n";
