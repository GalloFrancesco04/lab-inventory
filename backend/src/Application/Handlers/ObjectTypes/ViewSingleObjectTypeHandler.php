<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ObjectTypeRepository.php';
require_once __DIR__ . '/../../../../config/Database.php';

require_once __DIR__ . '/../../Commands/ObjectTypes/ViewSingleObjectTypeCommand.php';

class ViewSingleObjectTypeHandler {
    private $objectTypeRepository;

    public function __construct() {
        $db = new Database();

        $this->objectTypeRepository = new ObjectTypeRepository($db->getConnection());
    }

    public function handle(ViewSingleObjectTypeCommand $command) {

    try{

        if(empty($command->code)) {
            throw new Exception("Il Codice dell'ObjectType non puo' essere vuoto");
        }
            
        $objectType = $this->objectTypeRepository->findByCode($command->code);
            
        if (empty($objectType)){
            return null;
        }
                
        return $objectType;

        }catch(Exception $ex) {
            throw new Exception("Si e' verificato un errore durante il recupero dell'ObjectType: " . $ex->getMessage(), 0, $ex);
        }
    }
}