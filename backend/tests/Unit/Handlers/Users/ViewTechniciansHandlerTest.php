<?php
require_once __DIR__ . '/../../../../src/Application/Mediator/Mediator.php';
require_once __DIR__ . '/../../../../src/Application/Commands/Users/ViewTechniciansCommand.php';
require_once __DIR__ . '/../../../../src/Application/Handlers/Users/ViewTechniciansHandler.php';

$mediator = new Mediator();
$mediator->register('ViewTechniciansCommand', 'ViewTechniciansHandler');

echo "========== UNIT TEST: VIEW ALL TECHNICIANS ==========\n\n";

try {
    $result = $mediator->send(new ViewTechniciansCommand());

    if (is_array($result)) {
        echo "✅ Lista recuperata. Totale: " . count($result) . "\n\n";

        /**
         * Funzione di estrazione compatibile con i Getter della classe User
         */
        $extract = function($user, $field) {
            if (is_array($user)) return $user[$field] ?? 'N/D';

            // Mappa i campi ai nomi dei tuoi Getter
            $mapping = [
                'id'    => 'getId',
                'email' => 'getEmail',
                'role'  => 'getRole'
            ];

            $method = $mapping[$field] ?? null;
            if ($method && method_exists($user, $method)) {
                return $user->$method();
            }

            return 'N/D';
        };

        echo sprintf("%-5s | %-30s | %-10s\n", "ID", "EMAIL", "RUOLO");
        echo str_repeat("-", 50) . "\n";

        foreach ($result as $user) {
            echo sprintf(
                "%-5s | %-30s | %-10s\n",
                $extract($user, 'id'),
                $extract($user, 'email'),
                $extract($user, 'role')
            );
        }
    }
} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
}
echo "\n================ FINE TEST ==========================\n";