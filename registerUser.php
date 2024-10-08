<?php
require_once 'src/Database/database.php';
require_once 'src/user/register.php';
require_once 'emailservice.php';
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

$data = json_decode(file_get_contents("php://input"));

if (
    empty($data->first_name) || 
    empty($data->last_name) || 
    empty($data->email) || 
    empty($data->password) || 
    empty($data->date_of_birth) || 
    empty($data->role)
) {
    http_response_code(400);
    echo json_encode(['message' => 'Incomplete data']);
    exit;
}

if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid email format']);
    exit;
}

$user = new Register($db);
$result = $user->registerUser($data->first_name, $data->last_name, $data->email, $data->password, $data->date_of_birth, $data->role);

if ($result['success']) {
    $tokenId = $result['token_id'];
    $emailService = new EmailService();
    if ($emailService->sendVerificationEmail($data->email, $tokenId)) {
        http_response_code(201);
        echo json_encode(['message' => 'User registered successfully. Verification email sent.']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Verification email could not be sent.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['message' => $result['message']]);
}

ini_set('display_errors', '0');
error_reporting(0);
?>
