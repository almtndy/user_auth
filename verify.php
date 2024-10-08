<?php
class EmailVerification {
    private $db;
    private $token;

    public function __construct($db, $token) {
        $this->db = $db;
        $this->token = $token;
    }

    public function verifyEmail() {
        // Step 1: Fetch the user_id using the token
        $query = 'SELECT user_id FROM user_tokens WHERE token_id = :token_id AND expired_at > NOW()';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':token_id', $this->token);
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result) {
            $userId = $result['user_id'];

            // Step 2: Check if the email is already verified
            $checkQuery = 'SELECT is_verified FROM users WHERE user_id = :user_id';
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':user_id', $userId);
            $checkStmt->execute();
            $isVerified = $checkStmt->fetchColumn();

            if ($isVerified) {
                return ['message' => 'Email is already verified.'];
            }

            // Step 3: Update the is_verified field
            $updateQuery = 'UPDATE users SET is_verified = 1 WHERE user_id = :user_id';
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':user_id', $userId);
            $updateResult = $updateStmt->execute();

            // Debugging: Check if the update query succeeded
            if ($updateResult) {
                return ['message' => 'Email verification successful!'];
            } else {
                $errorInfo = $updateStmt->errorInfo(); // Get the error message
                return ['message' => 'Failed to update is_verified: ' . $errorInfo[2]];
            }
        } else {
            return ['message' => 'Invalid or expired token.'];
        }
    }
}
?>
