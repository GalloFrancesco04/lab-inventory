<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ObjectTypeRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';
require_once __DIR__ . '/../../Commands/ObjectTypes/ViewObjectTypesCommand.php';

class ViewObjectTypesHandler {
    private $objectTypeRepository;

    public function __construct() {
        $db = new Database();
        $this->objectTypeRepository = new ObjectTypeRepository($db->getConnection());
    }

    public function handle(ViewObjectTypesCommand $command) {
        try {

            $objectTypes = $this->objectTypeRepository->getAll();
            
            if (empty($objectTypes)) {
                return []; 
            }
                
            return $objectTypes;

        } catch (Exception $ex) {
            throw new Exception("Si e' verificato un errore durante il recupero dei Prestiti: " . $ex->getMessage(), 0, $ex);
        }
    }
}