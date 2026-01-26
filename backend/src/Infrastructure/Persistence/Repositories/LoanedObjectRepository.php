<?php

use PDO;
require_once __DIR__ . '/../../../Domain/Entities/LoanedObject.php';


class LoanedObjectRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function create(int $loan_id, LoanedObject $object): void
    {
        $stmt = $this->connection->prepare("
            INSERT INTO loanedobjects (object_type_code, object_id, loan_id, damaged)
            VALUES (:object_type_code, :object_id, :loan_id, false)
            ");

        $stmt->execute([
            'object_type_code' => $object->getObjectTypeCode(),
            'object_id' => $object->getObjectId(),
            'loan_id' => $loan_id
        ]); 
    }

    public function markAsDamaged(int $loan_id, int $object_id): void
    {
        $stmt = $this->connection->prepare("
            UPDATE loanedobjects 
            SET damaged = true
            WHERE loan_id = :loan_id AND object_id = :object_id
        ");

        $stmt->execute([
            'loan_id' => $loan_id,
            'object_id' => $object_id
        ]);
    }

    public function isDamaged(int $loan_id, int $object_id): bool
    {
        $stmt = $this->connection->prepare("
            SELECT damaged FROM loanedobjects 
            WHERE loan_id = :loan_id AND object_id = :object_id
        ");

        $stmt->execute([
            'loan_id' => $loan_id,
            'object_id' => $object_id
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function getLoanedObject(int $loan_id, int $object_id): ?LoanedObject
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM loanedobjects 
            WHERE loan_id = :loan_id AND object_id = :object_id
        ");

        $stmt->execute([
            'loan_id' => $loan_id,
            'object_id' => $object_id
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new LoanedObject(
                $row['object_type_code'],
                $row['object_id'],
                $row['loan_id'],
                (bool)$row['damaged']
            );
        }

        return null;
    }

    public function getLoanedObjectsByLoanId(int $loan_id): array
    {
        $stmt = $this->connection->prepare("
            SELECT *
            FROM loanedobjects 
            WHERE loan_id = :loan_id
        ");

        $stmt->execute(['loan_id' => $loan_id]);

        $objects = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $item = new LoanedObject(
                $row['object_type_code'],
                $row['object_id'],
                $row['loan_id'],
                (bool)$row['damaged']
            );

            $objects[] = $item;
        }

        return $objects;
    }

}
?>