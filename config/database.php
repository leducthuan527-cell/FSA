<?php
class Database {
    private $host = 'sql103.infinityfree.com'; 
    private $db_name = 'if0_39916428_personal_blog'; 
    private $username = 'if0_39916428'; 
    private $password = 'xEILBxS9Wyo96w'; 
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8mb4");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
