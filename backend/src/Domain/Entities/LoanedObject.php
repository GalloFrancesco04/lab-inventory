<?php

class LoanedObject {
    private string $objectTypeCode;
    private int $objectId;
    private int $loanId;
    private bool $damaged;

    public function __construct(
        string $objectTypeCode,
        int $objectId,
        int $loanId,
        bool $damaged
    ) {
        $this->objectTypeCode = $objectTypeCode;
        $this->objectId = $objectId;
        $this->loanId = $loanId;
        $this->damaged = $damaged;
    }

    public function getObjectTypeCode(): string {
        return $this->objectTypeCode;
    }

    public function setObjectTypeCode(string $objectTypeCode): void {
        $this->objectTypeCode = $objectTypeCode;
    }

    public function getObjectId(): int {
        return $this->objectId;
    }

    public function setObjectId(int $objectId): void {
        $this->objectId = $objectId;
    }

    public function getLoanId(): int {
        return $this->loanId;
    }

    public function setLoanId(int $loanId): void {
        $this->loanId = $loanId;
    }

    public function isDamaged(): bool {
        return $this->damaged;
    }

    public function setDamaged(bool $damaged): void {
        $this->damaged = $damaged;
    }
}