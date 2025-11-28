<?php
/**
 * Database Configuration and Connection Class
 * BONTEN Event Management System
 *
 * This class handles database connections and provides helper methods
 * for database operations throughout the application.
 */

class Database
{
    // Server Credentials
    private $host = "169.239.251.102";
    private $port = "3306";
    private $db_name = "webtech_2025A_marc_sossou";
    private $username = "marc.sossou";
    private $password = "Marco2310#";
    public $conn;

    /**
     * Get the database connection
     * @return mysqli Database connection object
     */
    public function connect()
    {
        $this->conn = null;

        try {
            // Using mysqli as requested
            $this->conn = new mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->db_name,
                $this->port
            );

            // Check connection
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }

            // Set charset to utf8mb4 for full Unicode support
            $this->conn->set_charset("utf8mb4");

            return $this->conn;

        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            die(json_encode([
                "status" => false,
                "message" => "Database connection failed. Please try again later."
            ]));
        }
    }

    /**
     * Test database connection
     * @return array Status array with success flag and message
     */
    public function testConnection()
    {
        try {
            if ($this->conn === null) {
                $this->connect();
            }

            $result = $this->conn->query("SELECT 1");

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Database connection successful',
                    'database' => $this->db_name,
                    'host' => $this->host
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Connection test failed'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Execute a prepared statement with parameters
     * @param string $sql SQL query with placeholders
     * @param string $types Parameter types (e.g., 'ssi' for string, string, int)
     * @param array $params Parameters to bind to the query
     * @return mysqli_stmt Prepared statement object
     */
    public function executeQuery($sql, $types = '', $params = [])
    {
        try {
            if ($this->conn === null) {
                $this->connect();
            }

            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Statement preparation failed: " . $this->conn->error);
            }

            if (!empty($params) && !empty($types)) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();

            return $stmt;

        } catch (Exception $e) {
            error_log("Query execution failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if a table exists in the database
     * @param string $tableName Name of the table to check
     * @return bool True if table exists, false otherwise
     */
    public function tableExists($tableName)
    {
        try {
            if ($this->conn === null) {
                $this->connect();
            }

            $result = $this->conn->query("SHOW TABLES LIKE '$tableName'");
            return $result && $result->num_rows > 0;

        } catch (Exception $e) {
            error_log("Table check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Begin a database transaction
     * @return bool True on success, false on failure
     */
    public function beginTransaction()
    {
        if ($this->conn === null) {
            $this->connect();
        }
        return $this->conn->begin_transaction();
    }

    /**
     * Commit a database transaction
     * @return bool True on success, false on failure
     */
    public function commit()
    {
        if ($this->conn === null) {
            return false;
        }
        return $this->conn->commit();
    }

    /**
     * Rollback a database transaction
     * @return bool True on success, false on failure
     */
    public function rollback()
    {
        if ($this->conn === null) {
            return false;
        }
        return $this->conn->rollback();
    }

    /**
     * Get the last inserted ID
     * @return int Last insert ID
     */
    public function getLastInsertId()
    {
        if ($this->conn === null) {
            return 0;
        }
        return $this->conn->insert_id;
    }

    /**
     * Sanitize input for database operations
     * @param string $input Input to sanitize
     * @return string Sanitized input
     */
    public function sanitize($input)
    {
        if ($this->conn === null) {
            $this->connect();
        }
        return $this->conn->real_escape_string(htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8'));
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

    /**
     * Get connection information (for debugging)
     * @return array Connection information
     */
    public function getConnectionInfo()
    {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'database' => $this->db_name,
            'username' => $this->username
        ];
    }

    /**
     * Destructor - close connection when object is destroyed
     */
    public function __destruct()
    {
        $this->close();
    }
}
?>
