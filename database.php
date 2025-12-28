<?php
class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "result_management";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            // If using MySQL driver, enable client-side buffering so multiple queries can run
            if (defined('PDO::MYSQL_ATTR_USE_BUFFERED_QUERY')) {
                $options[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;
            }

            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->database,
                $this->username,
                $this->password,
                $options
            );
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}
?>