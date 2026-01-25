<?php 

use PDO;
use Application\Domain\Entities\Loan;
use DateTime;

require_once __DIR__ . '/../../../Domain/Entities/Loan.php';

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

    public function getActiveLoans(): array
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM loans WHERE state = true
        ");

        $stmt->execute();

        $loans = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $loans[] = $this->mapRowToLoan($row);
        }

        return $loans;
    }

    //Input array of the function : 
    //     $filters = [
    //                                         // key => value (User input) 
    //     start_date (date)   =>    , 
    //     end_date   (date)   =>    ,  
    //      client_id (int)    =>    ,     
    //     object_type_code   (string) =>    ,
    //     state (bool) =>    ,         
    // ];
    public function combinableFilters(array $filters): array
    {
        $query = "SELECT l.id,lo.id,lo.object_type_code, l.startDate, l.endDate, l.returnDate, l.clientId, l.notes, l.quantity, l.state
            FROM loans l JOIN loanedobjects lo ON l.id = lo.loan_id
            WHERE 1=1"; 

        $params = [];
        if(!empty($filters['start_date'])){
            $query .= " AND l.startDate = :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if(!empty($filters['end_date'])){
            $query .= " AND l.endDate = :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        if(!empty($filters['client_id'])){
            $query .= " AND l.clientId = :client_id";
            $params['client_id'] = $filters['client_id'];     
        }

        if(!empty($filters['object_type_code'])){
            $query .= " AND lo.object_type_code LIKE :object_type_code";
            $params['object_type_code'] = $filters['object_type_code'];     
        }

        if(isset($filters['state'])){
            $query .= " AND l.state = :state";
            $params['state'] = $filters['state'];     
        }

        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);; //return multidimensional array [[loan_id,object_id, object_type_code, startDate, endDate, returnDate, clientId, notes, quantity, state], [...], [...]]
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
        $startDate = new DateTime($row['startDate']);
        $endDate = new DateTime($row['endDate']);
        $returnDate =(!empty($row['returnDate'])) ? new DateTime($row['returnDate']) : null;

        $Loan = new Loan(
            $startDate,
            $endDate,
            $returnDate,
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