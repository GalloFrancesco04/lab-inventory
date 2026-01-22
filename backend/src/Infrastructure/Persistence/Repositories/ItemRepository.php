<?php

use PDO;
use Application\Domain\Entities\InventoryItem;

class ItemRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function create(InventoryItem $item): void
    {
        $stmt = $this->connection->prepare("
            INSERT INTO object_ (object_type_code, state, location)
            VALUES (:object_type_code, :state, :location)
        ");

        $stmt->execute([
            'object_type_code' => $item->getObjectTypeCode(),
            'state'  => $item->getState(),
            'location'  => $item->getLocation()
        ]);

        //Set the generated ID to the Item entity
        $item->setId((int) $this->connection->lastInsertId())
    }

    public function updateState(int $id, string $newState): void
    {
        $stmt = $this->connection->prepare("
            UPDATE object_ 
            SET state = :state
            WHERE id = :id
        ");

        $stmt->execute([
            'state'  => $newState,
            'id'    => $id
        ]);
    }

    public function updateLocation(int $id, string $newLocation): void
    {
        $stmt = $this->connection->prepare("
            UPDATE object_ 
            SET location = :location
            WHERE id = :id
        ");

        $stmt->execute([
            'location'  => $newLocation,
            'id'    => $id
        ]);
    }

    public function findById(int $id): ?InventoryItem 
    {
        $stmt = $this->connection->prepare("SELECT * FROM object_ WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC); 

        if ($row) {
            return $this->mapRowToItem($row);
        }

        return null; // Item not found
    }

    public function findAll(): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM object_");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); 

        $items = [];
        foreach ($rows as $row) {
            $items[] = $this->mapRowToItem($row);
        }

        return $items;
    }

    // //input: array = [state,name,quantity,location]
    // public function search(array $filters): array
    // {
    //     $query = "SELECT o.id, ot.code, ot.name, ot.description, ot.quantity, o.state, o.location
    //         FROM object_ o JOIN objecttype ot ON o.object_type_code = ot.code
    //         WHERE 1=1";

    //     $params = [];

    //     ...
    // }

    private function mapRowToItem(array $row): InventoryItem
    {
        $item = new InventoryItem(
            $row['object_type_code'],
            $row['state'],
            $row['location']
        );

        $item->setId((int) $row['id']);

        return $item;
    }
}
?>