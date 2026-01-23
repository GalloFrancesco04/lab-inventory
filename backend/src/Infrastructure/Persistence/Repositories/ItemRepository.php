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
        $item->setId((int) $this->connection->lastInsertId());
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


//Input array of search function : 
    //     $filters = [
    //                                         // key => value (User input) 
    //     'state'  (int)   =>    ,       
    //     'name'   (string) =>    ,
    //     'location' (string) =>    ,         
    // ];
    public function searchByStateNameLocation(array $filters): array
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

        if(!empty($filters['name'])){
            $query .= " AND ot.name LIKE :name";
            $params['name'] = $filters['name'];         //match
            // $params['name'] = $filters['name'] . '%'  ; //starts with
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