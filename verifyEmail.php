<?php
require_once __DIR__ . '/verify.php';  
require_once __DIR__ . '/src/Database/database.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->connect();

if (!isset($_GET['token']) || empty($_GET['token'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Token is required.']);
    exit;
}

$token = $_GET['token'];
$emailVerification = new EmailVerification($db, $token);

try {
    $response = $emailVerification->verifyEmail();

    if ($response['message'] === 'Email verification successful!') {
        http_response_code(200);  // OK
    } else if ($response['message'] === 'Email is already verified.') {
        http_response_code(200);  // OK
    } else {
        http_response_code(400);  // Bad Request
    }

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);  // Internal Server Error
    echo json_encode(['message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
