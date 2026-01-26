<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Inventory/ViewObjectsCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Inventory/ViewObjectsHandler.php';

$mediator = new Mediator();
$mediator->register('ViewObjectsCommand', 'ViewObjectsHandler');

echo "========== UNIT TEST: VIEW ALL PHYSICAL OBJECTS ==========\n\n";

try {
    echo "Recupero lista esemplari in corso... ";
    
    // Invio il comando (solitamente senza parametri per vedere tutto)
    $result = $mediator->send(new ViewObjectsCommand());

    if (is_array($result)) {
        echo "✅ SUCCESSO!\n";
        echo "Totale esemplari trovati: " . count($result) . "\n\n";

        // Funzione helper per estrarre i valori (inclusa la gestione ItemState)
        $extractValue = function($source, $prop) {
            if (is_array($source)) return $source[$prop] ?? 'N/D';
            
            if (is_object($source)) {
                $getter = 'get' . str_replace('_', '', ucwords($prop, '_'));
                $value = method_exists($source, $getter) ? $source->$getter() : ($source->$prop ?? null);
                
                // Gestione specifica se il valore restituito è l'oggetto ItemState
                if (is_object($value)) {
                    if (method_exists($value, 'getName')) return $value->getName();
                    if (method_exists($value, 'getValue')) return $value->getValue();
                    if (method_exists($value, '__toString')) return (string)$value;
                    return get_class($value);
                }
                return $value ?? 'N/D';
            }
            return 'N/D';
        };

        echo sprintf("%-5s | %-15s | %-20s | %-15s\n", "ID", "TIPO", "POSIZIONE", "STATO");
        echo str_repeat("-", 65) . "\n";

        foreach ($result as $object) {
            echo sprintf(
                "%-5s | %-15s | %-20s | %-15s\n",
                $extractValue($object, 'id'),
                $extractValue($object, 'object_type_code'),
                $extractValue($object, 'location'),
                $extractValue($object, 'state') // Estratto tramite la logica ItemState dell'helper
            );
        }
    } else {
        echo "❌ ERRORE: L'Handler non ha restituito un array.\n";
    }

} catch (Exception $e) {
    echo "❌ FALLITO: " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ==========================\n";