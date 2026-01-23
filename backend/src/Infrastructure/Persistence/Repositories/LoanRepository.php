<?php 

use PDO;
use Application\Domain\Entities\Loan;

class LoanRepository{

    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function create(Loan $loan): void
    {
        $stmt = $this->connection->prepare("
            INSERT INTO loans (startDate, endDate, returnDate, clientId, notes, quantity, state)
            VALUES (:startDate, :endDate, :returnDate, :clientId, :notes, :quantity, :state)
        ");

        $stmt->execute([
            'startDate' => $loan->getStartDate()->format('Y-m-d'),
            'endDate' => $loan->getEndDate()->format('Y-m-d'),
            'returnDate' => $loan->getReturnDate()->format('Y-m-d'),
            'clientId' => $loan->getClientId(),
            'notes' => $loan->getNotes(),
            'quantity' => $loan->getQuantity(),
            'state' => $loan->getState()
        ]);

        //Set the generated ID to the Loan entity
        $loan->setId((int) $this->connection->lastInsertId());
    }

    public function closeLoan(int $loanId, DateTime $returnDate): void
    {
        $stmt = $this->connection->prepare("
            UPDATE loans 
            SET state = false, returnDate = :returnDate
            WHERE id = :loanId
        ");

        $stmt->execute([
            'returnDate' => $returnDate->format('Y-m-d'),
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
            'loanId' => $loanId
        ]);
    }

    public function updateQuantity(int $loanId, int $quantity): void
    {
        $stmt = $this->connection->prepare("
            UPDATE loans 
            SET quantity = :quantity
            WHERE id = :loanId
        ");

        $stmt->execute([
            'quantity' => $quantity,
            'loanId' => $loanId
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

    public function getAllLoansOfClient(int $clientId): array
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM loans WHERE clientId = :clientId
        ");

        $stmt->execute([
            'clientId' => $clientId
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
        $Loan = new Loan(
            $row['startDate'],
            $row['endDate'],
            $row['returnDate'],
            $row['clientId'],
            $row['notes'],
            (int)$row['quantity'],
            (bool)$row['state'],
        );

        $Loan->setId((int) $row['id']);
        return $Loan;
    }
}
?>