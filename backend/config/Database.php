<?php
// this file handles the database connection using PDO


class Database {
    public $conn;

    public function getConnection() {
        $this->conn=null;

        //Retrieve database configuration from Docker environment variables
        $host= getenv('DB_HOST') ?: 'db';
        $db_name = getenv('DB_NAME') ?: 'laboratorio_inventario';
        $username = getenv('DB_USER') ?: 'app_user';
        $password = getenv('DB_PASSWORD') ?: 'app_password';

        try {

            $dsn= "mysql:host=" . $host . ";dbname=" . $db_name . ";charset=utf8mb4";

            
            $this->conn = new PDO($dsn, $username, $password);
            
            // Set error mode to exception 
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Set default fetch mode to associative array
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch(PDOException $exception) {
            
            error_log("Connection error: " . $exception->getMessage());
            
            // Use the Response class to return a structured error
            require_once __DIR__ . '/../src/Http/Response.php';
            Response::error("Database connection failed", "DB_CONNECTION_ERROR", 500);
        }


        


        return $this->conn;
    }
}