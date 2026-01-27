<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Inventory/ViewSingleObjectCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Inventory/ViewSingleObjectHandler.php';

$mediator = new Mediator();
$mediator->register('ViewSingleObjectCommand', 'ViewSingleObjectHandler');

echo "=== TEST: VIEW SINGLE PHYSICAL OBJECT (WITH ITEMSTATE) ===\n\n";

$targetObjectId = 1; 

try {
    $result = $mediator->send(new ViewSingleObjectCommand($targetObjectId));

    if ($result) {
        echo "✅ OGGETTO TROVATO\n\n";

        // Funzione helper avanzata per gestire anche oggetti annidati (come ItemState)
        $extractValue = function($source, $prop) {
            // Se è un array
            if (is_array($source)) return $source[$prop] ?? 'N/D';
            
            // Se è un oggetto
            if (is_object($source)) {
                $getter = 'get' . ucfirst($prop);
                $value = method_exists($source, $getter) ? $source->$getter() : ($source->$prop ?? null);
                
                // --- LOGICA SPECIALE PER ITEMSTATE ---
                if (is_object($value)) {
                    // Prova a chiamare metodi comuni come getName(), getValue() o __toString()
                    if (method_exists($value, 'getName')) return $value->getName();
                    if (method_exists($value, 'getValue')) return $value->getValue();
                    if (method_exists($value, '__toString')) return (string)$value;
                    return get_class($value); // Ritorna il nome della classe se non sa come stamparlo
                }
                
                return $value ?? 'N/D';
            }
            return 'N/D';
        };

        echo "--- DETTAGLI ESEMPLARE ---\n";
        echo "ID:             " . $extractValue($result, 'id') . "\n";
        echo "Codice Tipo:    " . $extractValue($result, 'objectTypeCode') . "\n";
        echo "Posizione:      " . $extractValue($result, 'location') . "\n";
        
        // Qui estraiamo lo stato che ora passerà per la logica ItemState
        echo "Stato attuale:  " . $extractValue($result, 'state') . "\n"; 
        echo "--------------------------\n";
        
    } else {
        echo "❌ ERRORE: Nessun record per ID $targetObjectId\n";
    }
} catch (Exception $e) {
    echo "❌ FALLITO: " . $e->getMessage() . "\n";
}