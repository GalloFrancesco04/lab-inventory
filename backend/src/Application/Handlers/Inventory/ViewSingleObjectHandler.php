<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ItemRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../Commands/Inventory/ViewSingleObjectCommand.php';

class ViewSingleObjectHandler {
    private $itemRepository;

    public function __construct() {
        $db = new Database();

        $this->itemRepository = new ItemRepository($db->getConnection());
    }

    public function handle(ViewSingleObjectCommand $command) {

    try{

        if(empty($command->id)) {
            throw new Exception("L'id dell'Oggetto non puo' essere vuoto");
        }
            
        $obj = $this->itemRepository->findById($command->id);
            
        if (empty($obj)){
            return null;
        }
                
        return $obj;

        }catch(Exception $ex) {
            throw new Exception("Si e' verificato un errore durante il recupero del Prestito: " . $ex->getMessage(), 0, $ex);
        }
    }
}