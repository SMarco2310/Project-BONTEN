<?php
/**
 * Database Configuration Class
 * BONTEN Event Management System
 *
 * Simple and beginner-friendly database connection
 */

class Database
{
    // Database Configuration
    private $host = "169.239.251.102";
    private $port = "3306";
    private $db_name = "webtech_2025A_marc_sossou";
    private $username = "marc.sossou";
    private $password = "Marco2310#";

    public $conn;

    /**
     * Connect to database
     * Returns mysqli connection object
     */
    public function connect()
    {
        $this->conn = null;

        try {
            // Create connection
            $this->conn = new mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->db_name,
                $this->port
            );

            // Check if connection failed
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }

            // Set charset for proper character support
            $this->conn->set_charset("utf8mb4");

            return $this->conn;

        } catch (Exception $e) {
            // Log error and show user-friendly message
            error_log("Database Error: " . $e->getMessage());
            die("Unable to connect to database. Please try again later.");
        }
    }

    /**
     * Close database connection
     */
    public function close()
    {
        if ($this->conn !== null) {
            $this->conn->close();
            $this->conn = null;
        }
    }
}
?>
