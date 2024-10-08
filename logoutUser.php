<?php
require_once 'src/Database/database.php';
require_once 'src/user/logout.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

header('Content-Type: application/json');

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $database = new Database();
    $db = $database->connect();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Extract the Bearer token from the Authorization header
$headers = apache_request_headers();
if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if ($jwt) {
        // Instantiate the Logout class and log the user out
        $logout = new Logout($db, $jwt);
        $result = $logout->logoutUser();

        if ($result['success']) {
            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Token not provided.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['message' => 'Authorization header not found.']);
}
