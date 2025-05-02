<?php
require_once 'DbController.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Set last_scanned_at = NULL for all books with empty scanned_tag
        $stmt = $pdo->prepare("UPDATE reader_book_status SET last_scanned_at = NULL WHERE scanned_tag IS NULL OR scanned_tag = ''");
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
