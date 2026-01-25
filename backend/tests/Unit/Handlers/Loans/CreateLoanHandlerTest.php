<?php
// 1. Carica le classi (o usa il tuo autoloader se ne hai uno)
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Loans/CreateLoanHandler.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Loans/CreateLoanCommand.php';
require_once __DIR__ . '/../../../../src/Domain/Entities/InventoryItem.php';


// 2. Inizializza il Mediator 
// (Se il tuo Mediator crea internamente DB e Repo, basta un 'new')
$mediator = new Mediator();

$mediator->register('CreateLoanCommand', 'CreateLoanHandler');

echo "=== AVVIO TEST DI SISTEMA ===\n\n";

/**
 * TEST 1: Creazione Prestito (Caso Successo)
 */
try {
    echo "Esecuzione Test Loan... ";
    $loanCmd = new CreateLoanCommand("2026-01-24", "2026-02-10", 1, "test automatico");

    $result = $mediator->send($loanCmd);
    echo "✅ OK! Creato record ID: $result\n";
} catch (Exception $e) {
    echo "❌ FALLITO: " . $e->getMessage() . "\n";
}

/**
 * TEST 2: Validazione Date (Caso Errore)
 */
try {
    echo "Esecuzione Test Validazione Date... ";
    $failCmd = new CreateLoanCommand("2026-01-24", "2026-03-15", 1);

    $mediator->send($failCmd);
    echo "❌ FALLITO: Il sistema doveva bloccare l'intervallo superiore a 30gg\n";
} catch (Exception $e) {
    echo "✅ OK! Bloccato correttamente: " . $e->getMessage() . "\n";
}

/**
 * TEST 3: Cliente Inesistente
 * Verifica se l'handler controlla l'esistenza del cliente prima di procedere
 */
try {
    echo "Test 3: Creazione prestito per Cliente inesistente (ID 99999)... ";
    $invalidClientCmd = new CreateLoanCommand("2026-01-25", "2026-02-05", 99999, "Test ID errato");
    
    $mediator->send($invalidClientCmd);
    echo "❌ FALLITO: Il sistema ha permesso un prestito a un cliente fantasma\n";
} catch (Exception $e) {
    echo "✅ OK! Errore catturato: " . $e->getMessage() . "\n";
}

/**
 * TEST 4: Date al contrario
 * Verifica se la data di fine è precedente alla data di inizio
 */
try {
    echo "Test 4: Data fine precedente a data inizio... ";
    $backwardDatesCmd = new CreateLoanCommand("2026-02-10", "2026-01-25", 1, "Test date invertite");
    
    $mediator->send($backwardDatesCmd);
    echo "❌ FALLITO: Il sistema ha accettato date temporalmente impossibili\n";
} catch (Exception $e) {
    echo "✅ OK! Bloccato correttamente: " . $e->getMessage() . "\n";
}

/**
 * TEST 5: Prestito nel passato
 * Verifica se è possibile creare prestiti con data inizio antecedente ad oggi
 */
try {
    echo "Test 5: Tentativo di prestito nel passato... ";
    $pastDateCmd = new CreateLoanCommand("2020-01-01", "2020-01-10", 1, "Test passato");
    
    $mediator->send($pastDateCmd);
    echo "⚠️ NOTA: Il sistema ha accettato una data nel passato -> E' permesso.\n";
} catch (Exception $e) {
    echo "✅ OK! Bloccato correttamente: " . $e->getMessage() . "\n";
}

/**
 * TEST 6: Formato Data Invalido
 * Verifica la robustezza dell'Handler contro stringhe sporche
 */
try {
    echo "Test 6: Formato data non valido... ";
    $badFormatCmd = new CreateLoanCommand("data-sbagliata", "2026-02-10", 1, "Test formato");
    
    $mediator->send($badFormatCmd);
    echo "❌ FALLITO: Il sistema non ha validato il formato della stringa data\n";
} catch (Exception $e) {
    echo "✅ OK! Errore catturato: " . $e->getMessage() . "\n";
}

/**
 * TEST 7: Note troppo lunghe
 * Verifica i limiti del campo VARCHAR nel database
 */
try {
    echo "Test 7: Note eccessivamente lunghe (> 255 caratteri)... ";
    $longNotes = str_repeat("Bla ", 100);
    $longNotesCmd = new CreateLoanCommand("2026-01-25", "2026-02-10", 1, $longNotes);
    
    $mediator->send($longNotesCmd);
    echo "⚠️ NOTA: Stringa lunga accettata -> il db fa il troncamento.\n";
} catch (Exception $e) {
    echo "✅ OK! Bloccato correttamente: " . $e->getMessage() . "\n";
}

/**
 * TEST 8: Oggetto Non Trovato
 * Verifica che il sistema dia errore se l'ID dell'oggetto non esiste a catalogo
 */
try {
    echo "Test 8: Tentativo prestito con object_id inesistente... ";

    $item = new InventoryItem(99999, 'SERVER', 1, 'location');

    $items = [
        $item
    ];
    $notFoundItemCmd = new CreateLoanCommand("2026-01-25", "2026-02-10", 1, "Test ID errato", $items);
    
    $mediator->send($notFoundItemCmd);
    echo "❌ FALLITO: Il sistema ha accettato un object_id fantasma\n";
} catch (Exception $e) {
    echo "✅ OK! Errore catturato: " . $e->getMessage() . "\n";
}

/**
 * TEST 9: Oggetto Già Impegnato (Double Booking)
 * Indipendente: Crea un prestito e tenta di crearne un secondo per lo stesso oggetto.
 */
try {
    echo "Test 9: Verifica Double Booking (Indipendente)...\n";
    
    $item = new InventoryItem(1, 'LAPTOP', 1, 'location');

    $objectId = 1; // ID di un oggetto esistente (es. un Laptop)
    $typeCode = 'LAPTOP';

    // 1. Creiamo il primo prestito per il Cliente A
    echo "   - Creazione primo prestito (Cliente 1)... ";
    $items1 = [$item];
    $cmd1 = new CreateLoanCommand("2026-01-25", "2026-02-10", 1, "Primo prestito", $items1);
    $mediator->send($cmd1);
    echo "✅ Creato.\n";

    // 2. Tentiamo il secondo prestito per lo stesso oggetto ma per il Cliente B
    echo "   - Tentativo secondo prestito (Cliente 2) per lo stesso oggetto... ";
    $items2 = [$item];
    $cmd2 = new CreateLoanCommand("2026-01-26", "2026-02-11", 2, "Secondo prestito (conflitto)", $items2);
    
    $mediator->send($cmd2);
    
    // Se arriviamo qui, il test è FALLITO perché il sistema ha permesso il doppio prestito
    echo "❌ FALLITO: Il sistema ha permesso di prestare lo stesso oggetto a due persone!\n";

} catch (Exception $e) {
    // Se l'handler lancia un'eccezione (es. "Oggetto non disponibile"), il test è SUPERATO
    echo "✅ OK! Bloccato correttamente: " . $e->getMessage() . "\n";
}

/**
 * TEST 10: Incoerenza tra Tipo e ID
 * Verifica che l'ID appartenga effettivamente a quel Type Code
 */
// try {
//     echo "Test 10: Verifica coerenza Type Code / Object ID... ";
//     // Esempio: Dico che è un LIBRO ma l'ID punta a un MONITOR
//     $item = new InventoryItem(1, 'LAPTOP', 1, 'location');

//     $items = [
//         ['object_type_code' => 'BOOK', 'object_id' => 55] 
//     ];
//     $mismatchCmd = new CreateLoanCommand("2026-01-25", "2026-02-10", 1, "Test mismatch", $items);
    
//     $mediator->send($mismatchCmd);
//     echo "⚠️ NOTA: Il sistema ha accettato il mismatch (Verifica se l'Handler deve controllare).\n";
// } catch (Exception $e) {
//     echo "✅ OK! Errore di coerenza catturato: " . $e->getMessage() . "\n";
// }

/**
 * TEST 11: Prestito Multiplo (Verifica Atomicità)
 * Se uno degli oggetti fallisce, la transazione deve annullare anche gli altri.
 */
try {
    echo "Test 11: Verifica atomicità su lista oggetti... ";
    $item = new InventoryItem(2, 'SERVER', 1, 'location');

    $item2 = new InventoryItem(-3, 'MONITOR', 1, 'location');

    $items = [
        $item, // Valido
        $item2   // Chiaramente invalido
    ];
    $transactionCmd = new CreateLoanCommand("2026-01-25", "2026-02-10", 1, "Test atomicità", $item, $item2);
    
    $mediator->send($transactionCmd);
    echo "❌ FALLITO: La transazione ha salvato parzialmente i dati\n";
} catch (Exception $e) {
    echo "✅ OK! Transazione annullata con successo: " . $e->getMessage() . "\n";
}

echo "\n=== FINE TEST ===\n";