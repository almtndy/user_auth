<?php

class ProfileUpdate
{
    private $db;
    private $userId;
    private $data;

    public function __construct($db, $userId, $data)
    {
        $this->db = $db;
        $this->userId = $userId;
        $this->data = $data;
    }

    public function updateProfile()
    {
        try {
            // Validate that at least one field is provided for the update
            if (empty($this->data->first_name) && empty($this->data->last_name) && empty($this->data->email) && empty($this->data->date_of_birth)) {
                return ['success' => false, 'message' => 'No data provided for update'];
            }

            // Prepare the SQL update query dynamically
            $fields = [];
            $params = [];
            $emailChanged = false;

            // Check current email in the database
            $currentEmailQuery = "SELECT email FROM users WHERE user_id = :user_id";
            $currentEmailStmt = $this->db->prepare($currentEmailQuery);
            $currentEmailStmt->bindParam(':user_id', $this->userId);
            $currentEmailStmt->execute();
            $currentEmail = $currentEmailStmt->fetchColumn();

            if (!empty($this->data->first_name)) {
                $fields[] = "first_name = :first_name";
                $params[':first_name'] = $this->data->first_name;
            }

            if (!empty($this->data->last_name)) {
                $fields[] = "last_name = :last_name";
                $params[':last_name'] = $this->data->last_name;
            }

            if (!empty($this->data->email)) {
                // Ensure email format is valid
                if (!filter_var($this->data->email, FILTER_VALIDATE_EMAIL)) {
                    return ['success' => false, 'message' => 'Invalid email format'];
                }
                // Check if the email has changed
                if ($this->data->email !== $currentEmail) {
                    $fields[] = "email = :email";
                    $params[':email'] = $this->data->email;
                    $emailChanged = true; // Mark that the email is changed
                }
            }

            if (!empty($this->data->date_of_birth)) {
                $fields[] = "date_of_birth = :date_of_birth";
                $params[':date_of_birth'] = $this->data->date_of_birth;
            }

            // Generate dynamic query
            $query = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = :user_id";
            $params[':user_id'] = $this->userId;

            // Prepare and execute the query
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);

            if ($stmt->rowCount() > 0) {
                // Check if email was changed
                if ($emailChanged) {
                    // Generate a new verification token
                    $tokenId = bin2hex(random_bytes(16));
                    $emailService = new EmailService();

                    if ($emailService->sendVerificationEmail($this->data->email, $tokenId)) {
                        // Insert token into user_tokens table
                        $tokenQuery = 'INSERT INTO user_tokens (user_id, token_id, issued_at, expired_at) 
                                       VALUES (:user_id, :token_id, NOW(), DATE_ADD(NOW(), INTERVAL 1 HOUR))';
                        $tokenStmt = $this->db->prepare($tokenQuery);
                        $tokenStmt->bindParam(':user_id', $this->userId);
                        $tokenStmt->bindParam(':token_id', $tokenId);
                        $tokenStmt->execute();

                        return ['success' => true, 'message' => 'Profile updated successfully. Verification email sent.'];
                    } else {
                        return ['success' => false, 'message' => 'Verification email could not be sent.'];
                    }
                }

                return ['success' => true, 'message' => 'Profile updated successfully'];
            } else {
                return ['success' => false, 'message' => 'No changes made to the profile'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
