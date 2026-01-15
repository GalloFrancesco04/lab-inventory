<?php
// this file handles the database connection using PDO


class Database {
    public $conn;

    public function getConnection() {
        $this->conn=null;

        //Retrieve database configuration from Docker environment variables
        $host= getenv('DB_HOST') ?: 'db';
        $db_name = getenv('DB_NAME') ?: 'inventory_db';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: 'password';

        try {

            $dsn= "mysql:host=" . $host . ";dbname=" . $db_name . ";charset=utf8mb4";

            
            $this->conn = new PDO($dsn, $username, $password);
            
            // Set error mode to exception 
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Set default fetch mode to associative array
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch(PDOException $exception) {
            
            error_log("Connection error: " . $exception->getMessage());
            
            // Use the JsonResponse class to return a structured error
            require_once __DIR__ . '/../utils/JsonResponse.php';
            JsonResponse::error("Database connection failed", "DB_CONNECTION_ERROR", 500);
        }


        


        return $this->conn;
    }
}