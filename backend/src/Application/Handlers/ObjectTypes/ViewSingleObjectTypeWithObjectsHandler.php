<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ObjectTypeRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ItemRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';
require_once __DIR__ . '/../../Commands/ObjectTypes/ViewObjectTypesCommand.php';

class ViewSingleObjectTypeWithObjectsHandler {
    private $objectTypeRepository;
    private $itemRepository;

    public function __construct() {
        $db = new Database();
        $connection = $db->getConnection();

        $this->objectTypeRepository = new ObjectTypeRepository($connection);
        $this->itemRepository = new ItemRepository($connection);
    }

    public function handle(ViewSingleObjectTypeWithObjectsCommand $command) {
        try {

            if(empty($command->code)) {
                throw new Exception("Il Codice dell'ObjectType non puo' essere vuoto");
            }

            $objectType = $this->objectTypeRepository->findByCode($command->code);
            
            if (empty($objectType)) {
                return null; 
            }
                
            $objects = $this->itemRepository->getAllItemsByTypeCode($objectType->getCode());
            $objectType->objects = $objects;

            return $objectType;

        } catch (Exception $ex) {
            throw new Exception("Si e' verificato un errore durante il recupero degli ObjectType: " . $ex->getMessage(), 0, $ex);
        }
    }
}