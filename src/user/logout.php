<?php

use Firebase\JWT\JWT;

class Logout
{
    private $db;
    private $token;

    public function __construct($db, $token)
    {
        $this->db = $db;
        $this->token = $token;
    }

    public function logoutUser()
    {
        try {
            // Decode the JWT token to extract the user ID and other details
            $secret_key = $_ENV['JWT_SECRET'];
            $decoded = JWT::decode($this->token, new \Firebase\JWT\Key($secret_key, 'HS256'));
            $userId = $decoded->data->id;

            // Store the token in the jwt_tokens table to invalidate it
            $query = 'INSERT INTO jwt_tokens (user_id, token_id, expires_at) VALUES (:user_id, :token_id, :expires_at)';
            $stmt = $this->db->prepare($query);

            // Prepare variables for binding
            $tokenId = $this->token;
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // Set expiry time to match JWT expiration

            // Bind parameters
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':token_id', $tokenId);
            $stmt->bindParam(':expires_at', $expiresAt);

            $stmt->execute(); // Execute the insertion

            return ['success' => true, 'message' => 'Logout successful. Token invalidated.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Token error: ' . $e->getMessage()];
        }
    }
}
