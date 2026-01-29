<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ItemRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ObjectTypeRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../Commands/Inventory/ViewObjectsCommand.php';


class ViewObjectsHandler {
    private $itemRepository;
    private $objectTypeRepository;

    public function __construct() {
        $db = new Database();
        $connection = $db->getConnection();

        $this->itemRepository = new ItemRepository($connection);
        $this->objectTypeRepository = new ObjectTypeRepository($connection);
    }

    public function handle(ViewObjectsCommand $command) {

        try {

            $objects = $this->itemRepository->getAll();
            
            if(empty($objects)) {
                return [];
            }

            foreach($objects as $object) {
                $objectType = $this->objectTypeRepository->findByCode($object->getObjectTypeCode());
                $object->objectType = $objectType;
            }

            return $objects;

        }catch(Exception $ex) {
             throw new Exception("Si e' verificato un errore durante il recupero degli oggetti: " . $ex->getMessage(), 0, $ex);
        }
    }
}