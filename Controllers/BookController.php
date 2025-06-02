<?php
include 'DbController.php'; // This must define $pdo
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    // Fetch paginated records
    $stmt = $pdo->prepare("SELECT * FROM reader_book_status ORDER BY id ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count for pagination
    $countStmt = $pdo->query("SELECT COUNT(*) as total FROM reader_book_status");
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        "books" => $books,
        "total" => (int)$total
    ]);
    exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        // ✅ Field validation
        $required = ['reader_id', 'book_title', 'complete_book_title', 'author', 'assigned_tag', 'year', 'course'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                echo json_encode(["status" => "error", "message" => "Missing field: $field"]);
                exit;
            }
        }

        if (!is_numeric($data['reader_id'])) {
            echo json_encode(["status" => "error", "message" => "Reader ID must be numeric."]);
            exit;
        }

        if (!empty($data['id'])) {
            // ✅ UPDATE existing book
            try {
                $stmt = $pdo->prepare("UPDATE reader_book_status SET 
                    reader_id = ?, 
                    book_title = ?, 
                    complete_book_title = ?, 
                    author = ?, 
                    assigned_tag = ?, 
                    year = ?, 
                    course = ?
                    WHERE id = ?");

                $stmt->execute([
                    $data['reader_id'],
                    $data['book_title'],
                    $data['complete_book_title'],
                    $data['author'],
                    $data['assigned_tag'],
                    $data['year'],
                    $data['course'],
                    $data['id']
                ]);

                echo json_encode(["status" => "success", "message" => "Book updated successfully."]);
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) {
                    // Duplicate key error: figure out if it's reader_id or assigned_tag
                    if (strpos($e->getMessage(), 'reader_id') !== false) {
                        echo json_encode(["status" => "error", "message" => "Reader ID already exists. It must be unique."]);
                    } elseif (strpos($e->getMessage(), 'assigned_tag') !== false) {
                        echo json_encode(["status" => "error", "message" => "Assigned Tag already exists. It must be unique."]);
                    } else {
                        echo json_encode(["status" => "error", "message" => "Duplicate entry error."]);
                    }
                } else {
                    throw $e;
                }
            }
        } else {
            // ✅ INSERT new book
            try {
                $stmt = $pdo->prepare("INSERT INTO reader_book_status 
                    (reader_id, book_title, complete_book_title, author, assigned_tag, year, course) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)");

                $stmt->execute([
                    $data['reader_id'],
                    $data['book_title'],
                    $data['complete_book_title'],
                    $data['author'],
                    $data['assigned_tag'],
                    $data['year'],
                    $data['course']
                ]);

                echo json_encode(["status" => "success", "message" => "Book added successfully."]);
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) {
                    if (strpos($e->getMessage(), 'reader_id') !== false) {
                        echo json_encode(["status" => "error", "message" => "Reader ID already exists. It must be unique."]);
                    } elseif (strpos($e->getMessage(), 'assigned_tag') !== false) {
                        echo json_encode(["status" => "error", "message" => "Assigned Tag already exists. It must be unique."]);
                    } else {
                        echo json_encode(["status" => "error", "message" => "Duplicate entry error."]);
                    }
                } else {
                    throw $e;
                }
            }
        }

        exit;
    }

    echo json_encode(["status" => "error", "message" => "Unsupported request method."]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
