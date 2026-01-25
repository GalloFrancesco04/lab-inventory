<?php

require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/LoanRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ItemRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/ClientRepository.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Repositories/LoanedObjectRepository.php';

require_once __DIR__ . '/../../../Domain/Entities/Loan.php';
require_once __DIR__ . '/../../../Domain/Entities/LoanedObject.php';

require_once __DIR__ . '/../../../../config/Database.php';
require_once __DIR__ . '/../../../Infrastructure/Persistence/Connection/TransactionManager.php';

require_once __DIR__ . '/../../Commands/Loans/CreateLoanCommand.php';

class CreateLoanHandler {
    private $loanRepository;
    private $itemRepository;
    private $clientRepository;
    private $transaction;
    private $loanedObjectRepository;


    public function __construct() {
        $db = new Database();
        $connection = $db->getConnection();

        $this->loanRepository = new LoanRepository($connection);
        $this->itemRepository = new ItemRepository($connection);
        $this->clientRepository = new ClientRepository($connection);
        $this->loanedObjectRepository = new LoanedObjectRepository($connection);

        $this->transaction = new TransactionManager($connection);
    }

    // Handle the CreateLoanCommand
    public function handle(CreateLoanCommand $command) {
        
        try{

            if (empty($command->start_date) || empty($command->end_date) || empty($command->client_id)) {
                throw new Exception("Data di inizio, Data di fine e Id Cliente sono obbligatori");
            }

            if(count($command->objects) > 5) {
                throw new Exception("Non e' possibile prendere in prestito piu' di 5 oggetti alla volta");
            }

            $start = new DateTime($command->start_date);
            $end = new DateTime($command->end_date);
                
            if($start > $end) {
                throw new Exception("La Data di inizio non puo' essere successiva a quella di fine");
            }
                    
            $interval = $start->diff($end);
                    
            // i check if end_date - start_date <= 30d
            if ($interval->days > 30) {
                throw new Exception("La Data di fine non puo' essere successiva alla Data di inizio di piu' di 30 giorni");
            }

            // loan fields are valid here

            $this->transaction->begin();
            $client = $this->clientRepository->findById($command->client_id);

            if(empty($client)) {
                throw new Exception("Il Cliente specificato non esiste");
            }

            if($client->getState() == 0) {
                throw new Exception("Non e' possibile creare Prestiti per Clienti bloccati");
            }

            $loanToCreate = new Loan(
                null,
                $start,
                $end,
                null,
                $command->client_id,
                $command->notes,
                0,
                $command->state
            );
                    
            $createdLoanId = $this->loanRepository->create($loanToCreate);
                        
            $quantity = 0;

            // for each object of the loan I have to insert a new loanedObject
            foreach($command->objects as $object){

                $objectToLoan = new LoanedObject($object->object_type_code, $object->id, $createdLoanId, 0);

                $createdLoanedItem = $this->loanedObjectRepository->create($createdLoanId, $object);

                if (empty($createdLoanedItem)){
                    // loaned object not created
                    continue;
                }
                        
                // i put the state of the objects as loaned
                $item = $this->itemRepository->findById($object->id);
                    
                if(empty($item)) {
                    // item not found 
                    continue;
                }
                        
                // i use the state pattern to update the item state and then i also update it on db
                $item->loaned();

                $this->itemRepository->updateState($object->id, $item->getStateCode());

                // the object has been successfully loaned so I increase the quantity
                $quantity++;
            }

            // after I'm sure of the number of loanedObject successfully created I update the loan with the quantity
            $quantityUpdateResult = $this->loanRepository->updateQuantity($createdLoanId, $quantity);

            $this->transaction->commit();
            
            return $createdLoanId;

        }catch(Exception $ex) {
            $this->transaction->rollback();
            throw new Exception("Si e' verificato un errore durante la creazione del Prestito: " . $ex->getMessage(), 0, $ex);

        }

    }
}