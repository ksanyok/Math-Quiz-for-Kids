<?php
// PHP 8.1 or higher as per your requirement
$data = json_decode(file_get_contents('php://input'), true);
if ($data) {
    $date = date('Y-m-d');
    $time = date('H-i-s');
    $folderPath = __DIR__ . "/Test/$date";
    if (!file_exists($folderPath)) {
        mkdir($folderPath, 0777, true);
    }
    $filePath = "$folderPath/log_$time.json";
    file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}
?>
