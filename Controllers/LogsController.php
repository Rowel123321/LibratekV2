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

    $allowedActions = ['normal', 'taken_outside', 'misplaced', 'unreturned', 'unauthorized', 'reshelved', 'removed'];
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
    try {
        $actionType = $_GET['action_type'] ?? null;
        $date = $_GET['date'] ?? null;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;

        $sql = "SELECT * FROM rfid_logs";
        $countSql = "SELECT COUNT(*) FROM rfid_logs";
        $where = [];
        $params = [];

        if (!empty($actionType)) {
            $allowedActions = ['normal', 'taken_outside', 'misplaced', 'unreturned', 'unauthorized', 'reshelved', 'removed'];
            if (!in_array($actionType, $allowedActions)) {
                echo json_encode(["status" => "error", "message" => "Invalid action_type"]);
                exit;
            }
            $where[] = "action_type = :action_type";
            $params[':action_type'] = $actionType;
        }

        if (!empty($date)) {
            $where[] = "DATE(created_at) = :log_date";
            $params[':log_date'] = $date;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
            $countSql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count
        $countStmt = $pdo->prepare($countSql);
        foreach ($params as $key => $val) {
            $countStmt->bindValue($key, $val);
        }
        $countStmt->execute();
        $totalRows = $countStmt->fetchColumn();

        echo json_encode([
            "logs" => $logs,
            "total" => $totalRows,
            "page" => $page,
            "limit" => $limit
        ]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    // Reject other HTTP methods
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
