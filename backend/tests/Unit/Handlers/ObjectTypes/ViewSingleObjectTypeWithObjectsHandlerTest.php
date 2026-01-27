<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/ObjectTypes/ViewSingleObjectTypeWithObjectsCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/ObjectTypes/ViewSingleObjectTypeWithObjectsHandler.php';
require_once __DIR__ . '/../../../../src/Domain/Entities/ObjectType.php';

$mediator = new Mediator();
$mediator->register('ViewSingleObjectTypeWithObjectsCommand', 'ViewSingleObjectTypeWithObjectsHandler');

echo "========== TEST: VIEW OBJECT TYPE & ASSOCIATED PHYSICAL OBJECTS ==========\n\n";

try {
    $targetCode = "LAPTOP"; // Sostituisci con un codice esistente nel tuo DB
    echo "Recupero dettagli per il tipo: [$targetCode]... ";
    
    $command = new ViewSingleObjectTypeWithObjectsCommand($targetCode);
    $type = $mediator->send($command);
    
    if ($type instanceof ObjectType) {
        echo "✅ TROVATO\n\n";

        // --- 1. DETTAGLI DELL'OBJECT TYPE ---
        echo "=== DEFINIZIONE TIPO ===\n";
        echo "Codice:      " . $type->getCode() . "\n";
        echo "Nome:        " . $type->getName() . "\n";
        echo "Brand:       " . ($type->getBrand() ?? '-') . "\n";
        echo "Modello:     " . ($type->getModel() ?? '-') . "\n";
        echo "Quantità:    " . $type->getQuantity() . "\n";
        echo "Descrizione: " . ($type->getDescription() ?? 'Nessuna') . "\n\n";

        // --- 2. RECUPERO ESEMPLARI FISICI (Proprietà dinamica) ---
        // Assumiamo che l'handler carichi gli esemplari in 'objects'
        $physicalItems = $type->objects ?? [];

        echo "=== ESEMPLARI FISICI IN INVENTARIO ===\n";
        if (is_array($physicalItems) && count($physicalItems) > 0) {
            
            echo sprintf("%-5s | %-20s | %-15s\n", "ID", "POSIZIONE", "STATO");
            echo str_repeat("-", 45) . "\n";

            foreach ($physicalItems as $item) {
                // Estrazione ID
                $id = method_exists($item, 'getId') ? $item->getId() : 'N/D';
                
                // Estrazione Posizione
                $loc = method_exists($item, 'getLocation') ? $item->getLocation() : 'N/D';
                
                // Estrazione Stato (gestendo ItemState -> getStateCode)
                $stateStr = 'N/D';
                $stateObj = method_exists($item, 'getState') ? $item->getState() : ($item->state ?? null);
                if (is_object($stateObj) && method_exists($stateObj, 'getStateCode')) {
                    $stateStr = $stateObj->getStateCode();
                }

                echo sprintf("%-5s | %-20s | %-15s\n", $id, $loc, 0, 20, $stateStr);
            }
            echo "\nTotale esemplari mappati: " . count($physicalItems) . "\n";
        } else {
            echo "⚠️ Nessun esemplare fisico registrato per questo tipo.\n";
        }

    } else {
        throw new Exception("L'handler non ha restituito un'istanza di ObjectType.");
    }

} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ==========================\n";