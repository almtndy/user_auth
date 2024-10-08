<?php
require_once 'emailservice.php';
require_once 'src/Database/database.php';
require_once 'src/user/profileupdate.php';
require_once __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
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

// Get the JWT token from the Authorization header
$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    http_response_code(401);  // Unauthorized
    echo json_encode(['message' => 'Authorization token not found']);
    exit;
}

$jwt = str_replace('Bearer ', '', $headers['Authorization']);

try {
    // Decode the JWT
    $decoded = JWT::decode($jwt, new Key($_ENV['JWT_SECRET'], 'HS256'));
    $userId = $decoded->data->id;
} catch (Exception $e) {
    http_response_code(401);  // Unauthorized
    echo json_encode(['message' => 'Invalid token: ' . $e->getMessage()]);
    exit;
}

// Get the input data
$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    http_response_code(400);  // Bad Request
    echo json_encode(['message' => 'No input data provided']);
    exit;
}

// Instantiate the ProfileUpdate class and attempt to update the user's profile
$profileUpdate = new ProfileUpdate($db, $userId, $data, $jwt);
$result = $profileUpdate->updateProfile();

if ($result['success']) {
    http_response_code(200);  // OK
    echo json_encode(['message' => $result['message']]);
} else {
    http_response_code(400);  // Bad Request
    echo json_encode(['message' => $result['message']]);
}

// Disable error display for production
ini_set('display_errors', '0');
error_reporting(0);
