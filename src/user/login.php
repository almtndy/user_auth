<?php
use Firebase\JWT\JWT;

class Login {
    private $db;
    private $email;
    private $password;

    public function __construct($db, $data) {
        $this->db = $db;
        $this->email = $data['email'];
        $this->password = $data['password'];
    }

    public function loginUser() {
        try {
            // Prepare SQL statement to fetch user details
            $query = 'SELECT user_id, first_name, last_name, password, role, is_verified FROM users WHERE email = :email';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $this->email);
            $stmt->execute();

            // Fetch user data
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if user exists
            if ($user) {
                // Check if email is verified
                if ($user['is_verified'] == 0) {
                    return ['success' => false, 'message' => 'Email not verified.'];
                }

                // Verify password
                if (password_verify($this->password, $user['password'])) {
                    // Generate JWT token
                    $secret_key = $_ENV['JWT_SECRET'];
                    $issuer = "http://localhost";
                    $issuedAt = time();
                    $expirationTime = $issuedAt + 3600; // Token valid for 1 hour
                    $payload = [
                        'iss' => $issuer,
                        'iat' => $issuedAt,
                        'exp' => $expirationTime,
                        'data' => [
                            'id' => $user['user_id'],
                            'first_name' => $user['first_name'],
                            'last_name' => $user['last_name'],
                            'role' => $user['role'],
                        ]
                    ];

                    // Encode the token
                    $jwt = JWT::encode($payload, $secret_key, 'HS256');
                    return ['success' => true, 'message' => 'Login successful', 'token' => $jwt];
                } else {
                    return ['success' => false, 'message' => 'Invalid credentials'];
                }
            } else {
                return ['success' => false, 'message' => 'User not found'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
?>
