<?php
class Register {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function registerUser($firstName, $lastName, $email, $password, $dateOfBirth, $role) {
        try {
            // Generate a unique user ID and token
            $userId = bin2hex(random_bytes(16)); // Unique user ID
            $tokenId = bin2hex(random_bytes(16)); // Verification token

            // Insert user into the users table
            $query = 'INSERT INTO users (user_id, first_name, last_name, email, password, reg_date, date_of_birth, role, is_verified) 
                      VALUES (:user_id, :first_name, :last_name, :email, :password, CURDATE(), :date_of_birth, :role, 0)';
            $stmt = $this->db->prepare($query);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Bind parameters
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':first_name', $firstName);
            $stmt->bindParam(':last_name', $lastName);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':date_of_birth', $dateOfBirth);
            $stmt->bindParam(':role', $role);
            
            if ($stmt->execute()) {
                // Insert token into user_tokens table
                $tokenQuery = 'INSERT INTO user_tokens (user_id, token_id, issued_at, expired_at) 
                               VALUES (:user_id, :token_id, NOW(), DATE_ADD(NOW(), INTERVAL 1 HOUR))';
                $tokenStmt = $this->db->prepare($tokenQuery);
                $tokenStmt->bindParam(':user_id', $userId);
                $tokenStmt->bindParam(':token_id', $tokenId);
                
                if ($tokenStmt->execute()) {
                    return [
                        'success' => true,
                        'token_id' => $tokenId,
                        'user_id' => $userId
                    ];
                } else {
                    return ['success' => false, 'message' => 'Failed to insert user token.'];
                }
            } else {
                return ['success' => false, 'message' => 'User registration failed.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
?>
