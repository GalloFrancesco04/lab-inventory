<?php

use PDO;
require_once __DIR__ . '/../../../Domain/Entities/ObjectType.php';


class ObjectTypeRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    //Create a new ObjectType in the database
    public function create(ObjectType $objectType): void
    {
        $stmt = $this->connection->prepare("
            INSERT INTO objecttype (code, name, description, model, brand, quantity)
            VALUES (:code, :name, :description, :model, :brand, :quantity)
        ");

        $stmt->execute([
            'code' => $objectType->getCode(),
            'name'  => $objectType->getName(),
            'description' => $objectType->getDescription(),
            'model' => $objectType->getModel(),
            'brand' => $objectType->getBrand(),
            'quantity' => $objectType->getQuantity()
        ]);
    }

    //Update ObjectType's information
    public function update(ObjectType $objectType) :void
    {
        $stmt = $this->connection->prepare("
            UPDATE objecttype SET 
                name = :name, 
                description = :description, 
                model = :model, 
                brand = :brand
            WHERE code = :code
            ");

        $stmt->execute([
            'name'  => $objectType->getName(),
            'description' => $objectType->getDescription(),
            'model' => $objectType->getModel(),
            'brand' => $objectType->getBrand(),
            'code'    => $objectType->getCode()
        ]);
    }

    public function updateQuantity(string $code, int $quantity) :void
    {
        $stmt = $this->connection->prepare("
            UPDATE objecttype SET 
                quantity = :quantity
            WHERE code = :code
            ");

        $stmt->execute([
            'quantity' => $quantity,
            'code'    => $code
        ]);
    }

    // //logic delete
    // public function delete(string $code) :void
    // {
    //     $stmt = $this->connection->prepare("
    //     UPDATE objecttype 
    //     SET state = 0
    //     WHERE code = :code");

    //     $stmt->execute([
    //         'code'    => $code
    //     ]);
    // }

    public function findByCode(string $code): ?ObjectType
    {
        $stmt = $this->connection->prepare("SELECT * FROM objecttype WHERE code = :code");
        $stmt->execute(['code' => $code]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $this->mapRowToObjectType($row);
        }

        return null;
    }

    public function findByName(string $name): ?ObjectType
    {
        $stmt = $this->connection->prepare("SELECT * FROM objecttype WHERE name = :name");
        $stmt->execute(['name' => $name]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $this->mapRowToObjectType($row);
        }

        return null;
    }

    public function findByModel(string $model): ?ObjectType
    {
        $stmt = $this->connection->prepare("SELECT * FROM objecttype WHERE model = :model");
        $stmt->execute(['model' => $model]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $this->mapRowToObjectType($row);
        }

        return null;
    }

    public function findByBrand(string $brand): ?ObjectType
    {
        $stmt = $this->connection->prepare("SELECT * FROM objecttype WHERE brand = :brand");
        $stmt->execute(['brand' => $brand]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $this->mapRowToObjectType($row);
        }

        return null;
    }

    //Get all types of objects, not the single objects
    public function getAll(): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM objecttype");
        $stmt->execute();

        $types = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $types[] = $this->mapRowToObjectType($row);
        }
        return $types;
    }

    private function mapRowToObjectType(array $row): ObjectType
    {
        $objectType = new ObjectType($row['code'],$row['name'], $row['quantity'], $row['description'], $row['brand'], $row['model']);

        return $objectType;
    }




}
?>