<?php
class Database {
    private $host = "127.0.0.1"; 
    private $username = "root"; 
    private $password = "";     
    private $db_name = "user-auth";  
    public $conn;

    public function connect() {
        $this->conn = null;

        try {
          
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->db_name", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "Connected successfully";  // Add a message to confirm connection success
        } catch(PDOException $e) {
            // Capture and display error messages
            echo "Connection error: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>