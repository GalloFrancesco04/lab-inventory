<?php
// Handler class to process UpdateClientCommand
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../Commands/Clients/UpdateClientCommand.php';

class UpdateClientHandler {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }
}