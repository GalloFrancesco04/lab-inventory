<?php

use PDO;
use Application\Domain\Entities\LoanedObject;
use Application\Domain\Entities\InventoryItem;


class LoanedObjectRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function create(int $loanId, InventoryItem $object): void
    {
        $stmt = $this->connection->prepare("
            INSERT INTO loanedobjects (objectTypeCode, objectId, loanId, damaged)
            VALUES (:objectTypeCode, :objectId, :loanId, false)
            ");

        $stmt->execute([
            'objectTypeCode' => $object->getObjectTypeCode(),
            'objectId' => $object->getObjectId(),
            'loanId' => $loanId
        ]); 
    }

    public function markAsDamaged(int $loanId, int $objectId): void
    {
        $stmt = $this->connection->prepare("
            UPDATE loanedobjects 
            SET damaged = true
            WHERE loanId = :loanId AND objectId = :objectId
        ");

        $stmt->execute([
            'loanId' => $loanId,
            'objectId' => $objectId
        ]);
    }

    public function isDamaged(int $loanId, int $objectId): bool
    {
        $stmt = $this->connection->prepare("
            SELECT damaged FROM loanedobjects 
            WHERE loanId = :loanId AND objectId = :objectId
        ");

        $stmt->execute([
            'loanId' => $loanId,
            'objectId' => $objectId
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function getLoanedObject(int $loanId, int $objectId): ?LoanedObject
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM loanedobjects 
            WHERE loanId = :loanId AND objectId = :objectId
        ");

        $stmt->execute([
            'loanId' => $loanId,
            'objectId' => $objectId
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new LoanedObject(
                $row['objectTypeCode'],
                $row['objectId'],
                $row['loanId'],
                (bool)$row['damaged']
            );
        }

        return null;
    }

    public function getObjectsByLoanId(int $loanId): array
    {
        $stmt = this->connection->prepare("
            SELECT o.id, o.objectTypeCode, o.state, o.location
            FROM loanedobjects lo JOIN object_ o ON lo.objectId = o.id
            WHERE lo.loanId = :loanId
        ");

        $stmt->execute(['loanId' => $loanId]);

        $objects = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $objects[] = new InventoryItem(
                $row['id'],
                $row['objectTypeCode'],
                $row['state'],
                $row['location']
            );
        }

        return $objects;
    }

}


?>