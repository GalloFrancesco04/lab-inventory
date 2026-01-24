<?php

// require_once __DIR__ . '/../../../Infrastructure/LoanRepository.php';
// require_once __DIR__ . '/../../../Infrastructure/ItemRepository.php';
// require_once __DIR__ . '/../../../Infrastructure/ClientRepository.php';
// require_once __DIR__ . '/../../../Infrastructure/LoanedItemRepository.php';
// import Transaction
require_once __DIR__ . '/../../Commands/Loans/CreateLoanCommand.php';
class CreateLoanHandler {
    // private $loanRepository;
    // private $itemRepository;
    // private $clientRepository;
    // private $loanedItemRepository;


    public function __construct() {
        // $loanRepository = new LoanRepository();
        // $itemRepository = new ItemRepository();
        // $clientRepository = new ClientRepository();
        // $loanedItemRepository = new LoanedItemRepository();
    }

    // Handle the CreateLoanCommand
    public function handle(CreateLoanCommand $command) {
       
        if (empty($command->start_date) || empty($command->end_date) empty($command->client_id)) {
             throw new Exception("Data di inizio, Data di fine e Id Cliente sono obbligatori");
        }

        if(count($comman->objects) > 5) {
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
                    throw new Exception("La Data di fine non puo' essere successiva alla Data di inizio di piu' di 30 giorni")
                    }

        // loan fields are valid here

        // $transaction = new Transaction();
        // $transaction->start();
        try{
            // $client = $this->clientRepository->GetClient($command->client_id);
            // if(empty($client)) {
            //     throw new Exception("Il Cliente specificato non esiste");
            // }
                
            // $createdLoan = $this->loanRepository->CreateLoan($command);
                
            // if(empty($createdLoan))  {
            //     throw new Exception("Si e' verificato qualche problema nella creazione del Prestito");
            // }
                    
            //$quantity = 0;

            // for each object of the loan I have to insert a new loanedObject
            // for($command->objects as $object){

                // $createdLoanedObject = $this->loanedItemRepository->create($createdLoan->id, $object->id, $object->);

                // if (empty($createdLoanedObject)){
                //     continue;
                //}
                    
                // $quantity++;

                // i put the state of the objects as loaned
                // $item = $this->itemRepository->getItem($object->id);
                // $item->loaned();
                // $this->itemRepository->changeState($object->id, new LoanedState()->getCode());
            //}

            // after I'm sure of the number of loanedObject successfully created I update the loan with the quantity
            //$quantityUpdateResult = $this->loanRepository->UpdateLoanQuantity($quantity);

            // if(empty($quantityUpdateResult)){
            //     throw new Exception("C'e' stato un errore durante la creazione del Prestito");
            // }

           // $transaction->commit();

        }catch(Exception ex) {
            // $transaction->rollback();
            throw new Exception("Si e' verificato qualche problema nella creazione del Prestito");
        }

        // return $createdLoan->Id;
    }
}