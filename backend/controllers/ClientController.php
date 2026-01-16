<?php

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../utils/JsonResponse.php';

class ClientController {

    // Retrieve all clients
    public function getAll (){
        $db=new Database();
        $conn=$db->getConnection();

        // Fetch all clients from the database
        try {

            $stmt = $conn->prepare("SELECT id, name, email FROM clients");
            $stmt->execute();  
            $clients = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all clients as associative array
        
        } catch (Exception $e) {
            JsonResponse::error("Errore nel recupero dei clienti", "DB_ERROR", 500);
        }
    }

}