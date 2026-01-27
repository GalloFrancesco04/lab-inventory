<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ItemRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../Commands/Inventory/ViewObjectsCommand.php';


class ViewObjectsHandler {
    private $itemRepository;

    public function __construct() {
        $db = new Database();

        $this->itemRepository = new ItemRepository($db->getConnection());
    }

    public function handle(ViewObjectsCommand $command) {

        try {

            $objects = $this->itemRepository->getAll();
            
            if(empty($objects)) {
                return [];
            }

            return $objects;

        }catch(Exception $ex) {
             throw new Exception("Si e' verificato un errore durante il recupero degli oggetti: " . $ex->getMessage(), 0, $ex);
        }
    }
}