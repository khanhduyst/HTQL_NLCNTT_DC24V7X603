<?php
class Database
{
    private $host = "";
    private $port = "";
    private $db_name = "";
    private $username = "";
    private $password = "";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            header('Content-Type: application/json');
            echo json_encode(["error" => "Lỗi kết nối: " . $exception->getMessage()]);
            exit;
        }
        return $this->conn;
    }
}
