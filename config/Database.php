<?php


class Database
{
    
    private $host = "localhost";
    private $port = "3306";

    private $db_name = "webtech_2025A_marc_sossou";

    private $username = "marc.sossou";
    private $password = "Marco2310#";


    public $conn;


    public function connect()
    {
        $this->conn = null;

        try {
            
            $this->conn = new mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->db_name,
                $this->port
            );

            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }

            
            $this->conn->set_charset("utf8mb4");

            return $this->conn;

        } catch (Exception $e) {
            
            error_log("Database Error: " . $e->getMessage());
            die("We were not able to connect to the database. Please try again later.");
        }
    }



    public function close()
    {
        if ($this->conn !== null) {

            $this->conn->close();
            $this->conn = null;
        }

    }
}

?>
