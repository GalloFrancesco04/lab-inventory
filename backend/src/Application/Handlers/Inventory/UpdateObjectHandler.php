<?php
require_once __DIR__ . '/../../../../config/Database.php';
require_once __DIR__ . '/../../Commands/Inventory/UpdateObjectCommand.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ItemRepository.php';


class UpdateObjectHandler {
    private $itemRepository;

    // Constructor to set up database connection
    public function __construct() {
        $db = new Database();
        $connection = $db->getConnection();

        $this->itemRepository = new ItemRepository($connection);
    }

    // Method to handle the command and update the object in the database
    public function handle(UpdateObjectCommand $command) {
       try{

            if(empty($command->id)) {
                throw new Exception("L'id e' un campo obbligatorio");
            }

            $objectToUpdate = $this->itemRepository->findById($command->id);

            if(empty($objectToUpdate)) {
                throw new Exception("Non esiste un Oggetto con quell'id");
            }

            // for now I only update the location
            $this->itemRepository->updateLocation($command->id, $command->location);

       }catch(Exception $ex) {
            throw new Exception("Si e' verificato un errore durante l'aggiornamento dell'Oggetto: " . $ex->getMessage(), 0, $ex);
       }
    }
}