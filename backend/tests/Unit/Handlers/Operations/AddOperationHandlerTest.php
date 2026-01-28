<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Operations/AddOperationCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Operations/AddOperationHandler.php';

$mediator = new Mediator();
$mediator->register('AddOperationCommand', 'AddOperationHandler');

echo "========== TEST: AGGIUNTA NUOVA OPERAZIONE DI MANUTENZIONE ==========\n\n";

try {
    // 1. Definizione dei parametri dell'operazione
    $maintenance_id = 1;                     // ID della manutenzione a cui collegare l'operazione
    $description    = "Sostituzione pasta termica e pulizia ventole";
    $start_date     = new DateTime();        // Data di inizio (ora attuale)
    $cost           = 45.50;                 // Costo dell'operazione

    echo "Aggiunta operazione alla manutenzione ID: $maintenance_id...\n";

    // 2. Creazione del comando
    // Assicurati che il costruttore del tuo AddOperationCommand accetti questi parametri
    $command = new AddOperationCommand(
        $maintenance_id, 
        $description, 
        $start_date, 
        $cost
    );

    // 3. Invio tramite il Mediator
    // Se l'handler è di tipo "void", non ci aspettiamo un ritorno, 
    // altrimenti potresti ricevere l'oggetto Operation creato.
    $result = $mediator->send($command);

    echo "✅ SUCCESSO: Operazione registrata correttamente.\n";
    
    // Visualizzazione feedback opzionale se l'handler restituisce l'oggetto
    if (is_object($result) && method_exists($result, 'getId')) {
        echo "ID Operazione assegnato: " . $result->getId() . "\n";
    }

} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
    echo "Verifica che l'ID Manutenzione $maintenance_id esista nel database.\n";
}

echo "\n================ FINE TEST ==========================\n";