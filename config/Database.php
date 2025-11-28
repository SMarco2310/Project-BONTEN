<?php

class Database
{
    // Your School Server Credentials
    private $host = "169.239.251.102";
    private $port = "3306"; // Separated port for better compatibility
    private $db_name = "webtech_2025A_marc_sossou";
    private $username = "marc.sossou";
    private $password = "Marco2310#";
    public $conn;

    // Get the database connection
    public function connect()
    {
        $this->conn = null;

        // Using mysqli as requested
        $this->conn = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->db_name,
            $this->port,
        );

        // Check connection
        if ($this->conn->connect_error) {
            die(
                json_encode([
                    "status" => false,
                    "message" =>
                        "Connection failed: " . $this->conn->connect_error,
                ])
            );
        }

        echo "Database connection established successfully.";

        return $this->conn;
    }
}
?>
