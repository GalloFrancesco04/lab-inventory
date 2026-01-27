<?php

use PDO;
use Application\Domain\Entities\InventoryItem;

require_once __DIR__ . '/../../../Domain/Entities/InventoryItem.php';

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
        $item->setId((int) $this->connection->lastInsertId());
    }
    
    public function MarkObjectAsAvailable(int $id): void
    {
        $stmt = $this->connection->prepare("
            UPDATE object_ 
            SET state = 1
            WHERE id = :id
        ");

        $stmt->execute([
            'id'    => $id
        ]);
    }

    public function MarkObjectAsLoaned(int $id): void
    {
        $stmt = $this->connection->prepare("
            UPDATE object_ 
            SET state = 2
            WHERE id = :id
        ");

        $stmt->execute([
            'id'    => $id
        ]);
    }


     public function MarkObjectAsDamaged(int $id): void
    {
        $stmt = $this->connection->prepare("
            UPDATE object_ 
            SET state = 4
            WHERE id = :id
        ");

        $stmt->execute([
            'id'    => $id
        ]);
    }
        
    public function SendObjectToMaintenance(int $id): void
    {
        $stmt = $this->connection->prepare("
            UPDATE object_ 
            SET state = 3
            WHERE id = :id
        ");

        $stmt->execute([
            'id'    => $id
        ]);
    }

    public function MarkObjectAsOutOfService(int $id): void
    {
        $stmt = $this->connection->prepare("
            UPDATE object_ 
            SET state = 5
            WHERE id = :id
        ");

        $stmt->execute([
            'id'    => $id
        ]);
    }


    public function updateState(int $id, int $newState): void
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

    public function getAll(): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM object_");
        $stmt->execute();

        $items = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = $this->mapRowToItem($row);
        }

        return $items;
    }

    public function getAllItemsByTypeCode(string $typeCode): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM object_ WHERE object_type_code = :type_code");
        $stmt->execute(['type_code' => $typeCode]);

        $items = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = $this->mapRowToItem($row);
        }

        return $items;
    }


//Input array of search function : 
    //     $filters = [
    //                                         // key => value (User input) 
    //     'state'  (int)   =>    ,       
    //     'object_type_code'   (string) =>    ,
    //     'location' (string) =>    ,         
    // ];
    public function searchByStateTypeLocation(array $filters): array
    {
        $query = "SELECT o.id, ot.code, ot.name, ot.description, o.state, o.location
            FROM object_ o JOIN objecttype ot ON o.object_type_code = ot.code
            WHERE 1=1";

        $params = [];

        //state could be '0' so we use isset to check if the key exists
        if(isset($filters['state']) && $filters['state'] != ' '){
            $query .= " AND o.state = :state";
            $params['state'] = $filters['state'];
        }

        if(!empty($filters['object_type_code'])){
            $query .= " AND ot.code LIKE :object_type_code";
            $params['object_type_code'] = $filters['object_type_code'];     
        }

        if(!empty($filters['location'])){
            $query .= " AND o.location LIKE :location";
            $params['location'] = $filters['location'];         
        }
 
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);

       return $stmt->fetchAll(PDO::FETCH_ASSOC); //return multidimensional array [[id, code, name, description, state, location], [...], ...]
    }


    public function searchQuantityAvailable(string $code): ? array
    {
        $query = "SELECT  ot.code, COUNT(o.id) AS available_quantity
            FROM object_ o JOIN objecttype ot ON o.object_type_code = ot.code
            WHERE ot.code = :code AND o.state = 1 GROUP BY ot.code"; 

    
        $stmt = $this->connection->prepare($query);
        $stmt->execute(['code' => $code]);
    
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            return $row; //return array [code, quantity]
        }

        return null;
    }

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