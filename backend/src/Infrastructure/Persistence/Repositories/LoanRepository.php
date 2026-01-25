<?php 

require_once __DIR__ . '/../../../Domain/Entities/Loan.php';

use DateTime;

class LoanRepository{

    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function create(Loan $loan): int
    {
        $stmt = $this->connection->prepare("
            INSERT INTO loans (start_date, end_date, return_date, client_id, notes, quantity, state)
            VALUES (:start_date, :end_date, :return_date, :client_id, :notes, :quantity, :state)
        ");

        $stmt->execute([
            'start_date' => $loan->getStartDate()->format('Y-m-d'),
            'end_date' => $loan->getEndDate()->format('Y-m-d'),
            'return_date' => $loan->getReturnDate() ? $loan->getReturnDate()->format('Y-m-d') : null,
            'client_id' => $loan->getClientId(),
            'notes' => $loan->getNotes(),
            'quantity' => $loan->getQuantity(),
            'state' => $loan->getState()
        ]);

        //Set the generated ID to the Loan entity
        $loan->setId((int) $this->connection->lastInsertId());
        return $loan->getId();
    }

    public function closeLoan(int $loanId, DateTime $return_date): void
    {
        $stmt = $this->connection->prepare("
            UPDATE loans 
            SET state = false, return_date = :return_date
            WHERE id = :loanId
        ");

        $stmt->execute([
            'return_date' => $return_date->format('Y-m-d'),
            'loanId' => $loanId
        ]);
    }

    public function updateNotes(int $loanId, string $notes): void
    {
        $stmt = $this->connection->prepare("
            UPDATE loans 
            SET notes = :notes
            WHERE id = :loanId
        ");

        $stmt->execute([
            'notes' => $notes,
            'id' => $loanId
        ]);
    }

        public function update(Loan $loan): void
    {
        $stmt = $this->connection->prepare("
            UPDATE loans 
            SET notes = :notes, quantity = :quantity
            WHERE id = :loanId
        ");

        $stmt->execute([
            'notes' => $loan->getNotes(),
            'quantity' => $loan->getQuantity(),
            'id' => $loan->getId()
        ]);
    }

    public function updateQuantity(int $id, int $quantity): void
    {
        $stmt = $this->connection->prepare("
            UPDATE loans 
            SET quantity = :quantity
            WHERE id = :id
        ");

        $stmt->execute([
            'quantity' => $quantity,
            'id' => $id
        ]);
    }

    public function getLoanById(int $loanId): ?Loan
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM loans WHERE id = :loanId
        ");

        $stmt->execute([
            'loanId' => $loanId
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $this->mapRowToLoan($row);
        }

        return null;
    }

        //loans history of an object
    public function getLoansHistoryOfObject(int $object_id): array
    {
        $stmt = $this->connection->prepare("
            SELECT l.id, l.start_date, l.end_date,l.return_date, l.client_id, l.notes, l.quantity, l.state
            FROM loans l JOIN loanedobjects lo ON l.id = lo.loan_id
            WHERE lo.object_id = :object_id
        ");

        $stmt->execute(['object_id' => $object_id]);

        $history = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $history[] = $this->mapRowToLoan($row);
        }

        return $history;
    }

    public function getTotalTimeOfObjectOnLoan(int $object_id): int
    {
        $stmt = $this->connection->prepare("
            SELECT SUM(DATEDIFF(COALESCE(l.return_date, NOW()), l.start_date)) as total_time 
                FROM loans l
                JOIN loanedobjects lo ON l.id = lo.loan_id
                WHERE lo.object_id = :object_id
        ");

        $stmt->execute(['object_id' => $object_id]);

        return (int) $stmt->fetchColumn();        
    }


    public function getTotalNumberOfLoans(): int 
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as total_loans 
            FROM loans
            ORDER BY id
        ");

        $stmt->execute();

        return (int) $stmt->fetchColumn();        
    }

    public function getAllLoansOfClient(int $client_id): array
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM loans WHERE client_id = :client_id
        ");

        $stmt->execute([
            'client_id' => $client_id
        ]);

        $loans = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $loans[] = $this->mapRowToLoan($row);
        }

        return $loans;
    }


    public function getAllLoansOfUser(int $userId): array
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM loans WHERE userId = :userId
        ");

        $stmt->execute([
            'userId' => $userId
        ]);

        $loans = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $loans[] = $this->mapRowToLoan($row);
        }

        return $loans;
    }

    public function getAll(): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM loans");
        $stmt->execute();

        $loans = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $loans[] = $this->mapRowToLoan($row);
        }

        return $loans;
    }

    private function mapRowToLoan(array $row): Loan
    {
        $start_date = new DateTime($row['start_date']);
        $end_date = new DateTime($row['end_date']);
        $return_date =(!empty($row['return_date'])) ? new DateTime($row['return_date']) : null;

        $Loan = new Loan(
            $row['id'],
            $start_date,
            $end_date,
            $return_date,
            $row['client_id'],
            $row['notes'],
            (int)$row['quantity'],
            (bool)$row['state'],
        );

        return $Loan;
    }
}
?>