<?php

use PDO;
use Exception;
use Throwable;

class TransactionManager
{
    private PDO $pdo;

    // Constructor to initialize with a PDO instance
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Begin a transaction
    public function begin(): void
    {
        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        }
    }

    // Commit the current transaction
    public function commit(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    }

    // Rollback the current transaction
    public function rollback(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    // Execute a callable within a transaction
    public function transaction(callable $function)
    {
        $this->begin(); 

        try {
            $result = $function($this); // Pass the TransactionManager instance

            $this->commit();

            return $result; 

        } catch (Throwable $e) {
            //In case of error, rollback the transaction
            $this->rollback(); 
            throw $e; 
        }
    }
}