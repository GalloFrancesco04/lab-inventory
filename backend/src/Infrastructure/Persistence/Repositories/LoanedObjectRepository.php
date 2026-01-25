<?php

use PDO;
require_once __DIR__ . '/../../../Domain/Entities/LoanedObject.php';
use Application\Domain\Entities\Loan;
use Application\Domain\Entities\LoanedObject;
use Application\Domain\Entities\InventoryItem;


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
            'object_id' => $object->getId(),
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

    public function getObjectsByLoanId(int $loan_id): array
    {
        $stmt = $this->connection->prepare("
            SELECT o.id, o.object_type_code, o.state, o.location
            FROM loanedobjects lo JOIN object_ o ON lo.object_id = o.id
            WHERE lo.loan_id = :loan_id
        ");

        $stmt->execute(['loan_id' => $loan_id]);

        $objects = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $item = new InventoryItem(
                $row['object_type_code'],
                $row['state'],
                $row['location']
            );
            $item->setId((int) $row['id']);

            $objects[] = $item;
        }

        return $objects;
    }

}
?>