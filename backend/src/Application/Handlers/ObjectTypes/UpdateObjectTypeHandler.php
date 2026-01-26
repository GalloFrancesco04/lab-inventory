<?php
require_once __DIR__ . '/../../../../config/Database.php';
require_once __DIR__ . '/../../Commands/ObjectTypes/UpdateObjectTypeCommand.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ObjectTypeRepository.php';

require_once __DIR__ . '/../../../Domain/Entities/ObjectType.php';


class UpdateObjectTypeHandler {
    private $objectTypeRepository;

    public function __construct() {
        $db = new Database();
        $connection = $db->getConnection();
        $this->objectTypeRepository = new ObjectTypeRepository($connection);

    }
    public function handle(UpdateObjectTypeCommand $command) {
     try{

        if(empty($command->code) || empty($command->name)) {
            throw new Exception("L'il Codice dell'ObjectType e il Nome sono campi obbligatori");
        }

        $objectType = $this->objectTypeRepository->findByCode($command->code);

        if(empty($objectType)) {
            throw new Exception("Non esiste alcun Object Type con quel codice");
        }

        $objectTypeToUpdate = new ObjectType($command->code, $command->name, 0, $command->description, $command->brand, $command->model);
        $this->objectTypeRepository->update($objectTypeToUpdate);

        }catch(Exception $ex) {
            throw new Exception("Si e' verificato un errore durante l'aggiornamento dell'Objecttype: " . $ex->getMessage(), 0, $ex);
        }
    }
}