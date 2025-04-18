<?php
$reader1File = 'reader_1_log.txt';
$reader2File = 'reader_2_log.txt';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    if (isset($data['rfid_tag']) && isset($data['reader_id'])) {
        $tag = trim($data['rfid_tag']) . "\n";

        if ($data['reader_id'] === 'reader_1') {
            file_put_contents($reader1File, $tag);
        } elseif ($data['reader_id'] === 'reader_2') {
            file_put_contents($reader2File, $tag);
        }

        echo json_encode(["status" => "success", "message" => "RFID tag saved"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Missing RFID tag or reader ID"]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $reader1Tag = file_exists($reader1File) ? trim(file_get_contents($reader1File)) : "";
    $reader2Tag = file_exists($reader2File) ? trim(file_get_contents($reader2File)) : "";

    echo json_encode([
        "reader_1" => $reader1Tag,
        "reader_2" => $reader2Tag
    ]);
}
?>
