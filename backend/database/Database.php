<?php

class Database {
    public $conn;

    public function getConnection() {
        $this->conn=null;

        //Retrieve database configuration from Docker environment variables
        $host= getenv('DB_HOST') ?: 'db';
        $db_name = getenv('DB_NAME') ?: 'inventory_db';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: 'password';

        return $this->conn;
    }
}