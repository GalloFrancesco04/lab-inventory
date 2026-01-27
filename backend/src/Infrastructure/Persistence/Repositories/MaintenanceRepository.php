<?php

use PDO;
use Application\Domain\Entities\Maintenance;
use DateTime;

require_once __DIR__ . '/../../../Domain/Entities/Maintenance.php';

class MaintenanceRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function create(Maintenance $maintenance): void 
    {
        $stmt = $this->connection->prepare("
            INSERT INTO maintenance (object_id, description, start_date, end_date,
                                        type, technician_id, phone_number, cost)   
            VALUES (:object_id, :description, :start_date, :end_date, 
                    :type, :technician_id, :phone_number, :cost)
                    ");

            $stmt->execute([
                'object_id' => $maintenance->getObjectId(),
                'description' => $maintenance->getDescription(),
                'start_date' => $maintenance->getStartDate()->format('Y-m-d'),
                'end_date' => $maintenance->getEndDate() ? $maintenance->getEndDate()->format('Y-m-d') : null,
                'type' => $maintenance->getType(),
                'technician_id' => $maintenance->getTechnicianId(),
                'phone_number' => $maintenance->getPhoneNumber(),
                'cost' => $maintenance->getCost()
            ]);

            //Set the generated ID to the Maintenance entity
            $maintenance->setId((int) $this->connection->lastInsertId());

    }

    public function update(Maintenance $maintenance): void 
    {
        $stmt = $this->connection->prepare("
            UPDATE maintenance SET 
                description = :description,
                start_date = :start_date,
                end_date = :end_date,
                type = :type,
                technician_id = :technician_id,
                phone_number = :phone_number,
                cost = :cost
            WHERE id = :id
        ");

        $stmt->execute([
            'description' => $maintenance->getDescription(),
            'start_date' => $maintenance->getStartDate()->format('Y-m-d'),
            'end_date' => $maintenance->getEndDate() ? $maintenance->getEndDate()->format('Y-m-d') : null,
            'type' => $maintenance->getType(),
            'technician_id' => $maintenance->getTechnicianId(),
            'phone_number' => $maintenance->getPhoneNumber(),
            'cost' => $maintenance->getCost(),
            'id' => $maintenance->getId()
        ]);
    }

    //active maintenances
    public function getActiveMaintenances(): array 
    {
        $stmt = $this->connection->prepare("SELECT * FROM maintenance WHERE end_date IS NULL");
        $stmt->execute();

        $maintenances = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maintenances[] = $this->mapRowToMaintenance($row);
        }

        return $maintenances;
    }

    //history of maintenances of an object
    public function getObjectMaintenances(int $objectId): array 
    {
        $stmt = $this->connection->prepare("SELECT * FROM maintenance WHERE object_id = :object_id");
        $stmt->execute(['object_id' => $objectId]);

        $maintenances = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maintenances[] = $this->mapRowToMaintenance($row);
        }

        return $maintenances;
    }

    public function getAll(): array 
    {
        $stmt = $this->connection->prepare("SELECT * FROM maintenance");
        $stmt->execute();

        $maintenances = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maintenances[] = $this->mapRowToMaintenance($row);
        }

        return $maintenances;
    }

    public function updateCost(int $id, float $newCost): void
    {
        $stmt = $this->connection->prepare("
            UPDATE maintenance 
            SET cost = :cost
            WHERE id = :id
        ");

        $stmt->execute([
            'cost'  => $newCost,
            'id'    => $id
        ]);
    }

    public function findById(int $id): ?Maintenance 
    {
        $stmt = $this->connection->prepare("SELECT * FROM maintenance WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC); 

        if ($row) {
            return $this->mapRowToMaintenance($row);
        }

        return null; // Maintenance not found
    }

    public function CloseMaintenance(int $id, DateTime $endDate): void
    {
        $stmt = $this->connection->prepare("
            UPDATE maintenance 
            SET end_date = :end_date
            WHERE id = :id
        ");

        $stmt->execute([
            'end_date'  => $endDate->format('Y-m-d'),
            'id'    => $id
        ]);
    }

    //calculate total cost of maintenances of an object
    public function getCostObjectMaintenance(int $object_id): float
    {
        $stmt = $this->connection->prepare("
            SELECT SUM(cost) as total_cost FROM maintenance 
            WHERE object_id = :object_id
        ");

        $stmt->execute([
            'object_id' => $object_id
        ]);

        return (float) $stmt->fetchColumn();
    }

    public function searchByDate(DateTime $date): array
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM maintenance 
            WHERE start_date <= :date AND (end_date IS NULL OR end_date >= :date)
        ");

        $stmt->execute([
            'date' => $date->format('Y-m-d')
        ]);

        $maintenances = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maintenances[] = $this->mapRowToMaintenance($row);
        }

        return $maintenances;
    }

    public function searchByType(string $type): array
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM maintenance 
            WHERE type = :type
        ");

        $stmt->execute([
            'type' => $type
        ]);

        $maintenances = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maintenances[] = $this->mapRowToMaintenance($row);
        }

        return $maintenances;
    }
    
    private function mapRowToMaintenance(array $row): Maintenance 
    {
        $startDate = new DateTime($row['start_date']);
        $endDate = (!empty($row['end_date'])) ? new DateTime($row['end_date']) : null;

        $maintenance =  new Maintenance(
            (int)$row['object_id'],
            $row['description'],
            $startDate,
            $endDate,
            $row['type'],
            (int)$row['technician_id'],
            $row['phone_number'],
            (float)$row['cost'],
        );
        $maintenance->setId((int)$row['id']);

        return $maintenance;
    }
}
?>