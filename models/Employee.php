<?php
class Employee
{
    private $conn;
    private $table = "employees";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function findByUsername($username)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE username = :user LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}