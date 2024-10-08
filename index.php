<?php

require_once 'src/Database/database.php';

$database = new Database();
$conn = $database->connect();

header('Content-Type: application/json');

if ($conn) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Database connection established!'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to connect to the database.'
    ]);
}
?>