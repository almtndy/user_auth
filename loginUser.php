<?php

require_once 'src/Database/database.php';
require_once 'src/user/login.php';
require_once __DIR__ . '/vendor/autoload.php';  

use Dotenv\Dotenv;  

header('Content-Type: application/json');

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    // Establish a connection to the database
    $database = new Database();
    $db = $database->connect();
} catch (Exception $e) {
    http_response_code(500);  // Internal Server Error
    echo json_encode(['message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Get the input data
$data = json_decode(file_get_contents("php://input"));

if (empty($data->email) || empty($data->password)) {
    http_response_code(400);  // Bad Request
    echo json_encode(['message' => 'Incomplete data']);
    exit;
}

// Instantiate the Login class and attempt to login the user
$login = new Login($db, ['email' => $data->email, 'password' => $data->password]);
$result = $login->loginUser();

if ($result['success']) {
    http_response_code(200);  // OK
    echo json_encode($result);
} else {
    http_response_code(401);  // Unauthorized
    echo json_encode($result);
}
