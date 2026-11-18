<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/ObjectTypes/ViewSingleObjectTypeCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/ObjectTypes/ViewSingleObjectTypeHandler.php';

$mediator = new Mediator();
$mediator->register('ViewSingleObjectTypeCommand', 'ViewSingleObjectTypeHandler');

echo "=== TEST: VIEW SINGLE OBJECT TYPE ===\n\n";

// ID di un tipo di oggetto che sai essere presente nel DB
$targetId = 'NB-DELL-XPS15'; 

try {
    echo "Recupero dettagli per ID $targetId... ";
    
    $result = $mediator->send(new ViewSingleObjectTypeCommand($targetId));

    if ($result) {
        echo "✅ TROVATO!\n\n";

        // Funzione helper per estrarre i dati sia da oggetti (con getter) che da array
        $getData = function($obj, $prop) {
            if (is_array($obj)) return $obj[$prop] ?? 'N/D';
            
            $getter = 'get' . ucfirst($prop);
            if (method_exists($obj, $getter)) return $obj->$getter();
            if (isset($obj->$prop)) return $obj->$prop;
            
            return 'N/D';
        };

        echo "--- SCHEDA OGGETTO ---\n";
        echo "Codice:    " . $getData($result, 'code') . "\n";
        echo "Nome:      " . $getData($result, 'name') . "\n";
        echo "Modello:   " . $getData($result, 'model') . "\n";
        echo "Brand:     " . $getData($result, 'brand') . "\n";
        echo "----------------------\n";
        
    } else {
        echo "❌ ERRORE: Nessun risultato restituito.\n";
    }

} catch (Exception $e) {
    echo "❌ FALLITO: " . $e->getMessage() . "\n";
}

echo "\n=== FINE TEST ===\n";