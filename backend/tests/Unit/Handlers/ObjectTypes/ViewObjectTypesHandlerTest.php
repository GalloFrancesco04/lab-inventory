<?php
// 1. Inclusione delle dipendenze necessarie
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
// Nota: Assicurati che il percorso del Command sia corretto (es. ViewObjectTypesCommand)
require_once __DIR__ . '/../../../../src/Application/Commands/ObjectTypes/ViewObjectTypesCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/ObjectTypes/ViewObjectTypesHandler.php';

// 2. Inizializzazione Ambiente
$mediator = new Mediator();

// Registrazione dell'Handler (usando il nuovo nome)
$mediator->register('ViewObjectTypesCommand', 'ViewObjectTypesHandler');

echo "========== UNIT TEST: VIEW OBJECT TYPES LIST ==========\n\n";

/**
 * TEST 1: Recupero Lista Completa
 */
try {
    echo "Recupero lista in corso... ";
    
    $result = $mediator->send(new ViewObjectTypesCommand());

    echo "✅ OK!\n";
    echo "Record trovati: " . count($result) . "\n\n";

    foreach ($result as $type) {
        // 1. Se sono OGGETTI con getter (Metodo più probabile e corretto)
        if (is_object($type)) {
            $code = method_exists($type, 'getCode') ? $type->getCode() : ($type->code ?? 'N/D');
            $name = method_exists($type, 'getName') ? $type->getName() : ($type->name ?? 'N/D');
        } 
        // 2. Se è un ARRAY associativo (diretto da PDO)
        else {
            $code = $type['code'] ?? 'N/D';
            $name = $type['name'] ?? 'N/D';
        }

        echo "- [$code] $name\n";
    }

} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}

echo "\n================ FINE TEST ==========================\n";