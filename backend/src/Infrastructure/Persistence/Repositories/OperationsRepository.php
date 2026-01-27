<?php

use PDO;
use Application\Domain\Entities\Operation;
use DateTime;

require_once __DIR__ . '/../../../Domain/Entities/Operation.php';

class OperationRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function create(Operation $operation): void 
    {
        $stmt = $this->connection->prepare("
            INSERT INTO operations (maintenance_id, description, start_date, end_date, cost)
            VALUES (:maintenance_id, :description, :start_date, :end_date, :cost)
        ");

        $stmt->execute([
            'maintenance_id' => $operation->getMaintenanceId(),
            'description' => $operation->getDescription(),
            'start_date' => $operation->getStartDate()->format('Y-m-d'),
            'end_date' => $operation->getEndDate()->format('Y-m-d'),
            'cost' => $operation->getCost()
        ]);

        //Set the generated ID to the Operation entity
        $operation->setId((int) $this->connection->lastInsertId());
    }

    public function closeOperation(int $operationId, DateTime $endDate): void 
    {
        $stmt = $this->connection->prepare("
            UPDATE operations 
            SET end_date = :end_date
            WHERE id = :operationId
        ");

        $stmt->execute([
            'end_date' => $endDate->format('Y-m-d'),
            'operationId' => $operationId
        ]);
    }

    public function update(Operation $operation): void 
    {
        $stmt = $this->connection->prepare("
            UPDATE operations SET 
                description = :description,
                start_date = :start_date,
                end_date = :end_date,
                cost = :cost
            WHERE id = :id
        ");

        $stmt->execute([
            'description' => $operation->getDescription(),
            'start_date' => $operation->getStartDate()->format('Y-m-d'),
            'end_date' => $operation->getEndDate()->format('Y-m-d'),
            'cost' => $operation->getCost(),
            'id' => $operation->getId()
        ]);
    }

    public function getById(int $id): ?Operation 
    {
        $stmt = $this->connection->prepare("SELECT * FROM operations WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $this->mapRowToOperation($row);
        }
        
        return null; // Operation not found
    }

    public function getAllMaintenanceOperations(int $maintenanceId): array 
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM operations WHERE maintenance_id = :maintenance_id
        ");

        $stmt->execute(['maintenance_id' => $maintenanceId]);

        $operations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $operations[] = $this->mapRowToOperation($row);
        }

        return $operations;
    }

    public function getAll(): array 
    {
        $stmt = $this->connection->prepare("SELECT * FROM operations");
        $stmt->execute();

        $operations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $operations[] = $this->mapRowToOperation($row);
        }

        return $operations;
    }

    public function getAllObjectOperations(int $objectId): array 
    {
        $stmt = $this->connection->prepare("
            SELECT o.* FROM operations o
            JOIN maintenance m ON o.maintenance_id = m.id
            WHERE m.object_id = :object_id
        ");

        $stmt->execute(['object_id' => $objectId]);

        $operations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $operations[] = $this->mapRowToOperation($row);
        }

        return $operations;
    }

    private function mapRowToOperation(array $row): Operation 
    {
        $startDate = new DateTime($row['start_date']);
        $endDate = $row['end_date'] ? new DateTime($row['end_date']) : null;
        $operation = new Operation(
            $row['maintenance_id'],
            $row['description'],
            $startDate,
            $endDate,
            (float)$row['cost'],
        );
        $operation->setId((int)$row['id']);

        return $operation;
    }
}
?>