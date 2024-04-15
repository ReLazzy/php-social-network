<?php

class Database
{
    private static ?Database $instance = null;

    private string $host = "127.0.0.1";
    private string $port = "3306";
    private string $db_name = "vk";
    private string $username = "root";
    private string $password = "";
    private ?PDO $conn = null;

    private function __construct()
    {
        try {
            $this->conn = new PDO("mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4", $this->username, $this->password);
            $this->conn->exec("set names utf8mb4");
        } catch (PDOException $exception) {
            die("Error connection with Database " . $exception->getMessage());
        }
    }

    public static function getInstance(): Database
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): ?PDO
    {
        return $this->conn;
    }
}
