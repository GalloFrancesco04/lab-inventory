<?php

#[AllowDynamicProperties]
class Loan {
    private ?int $id;
    private DateTime $startDate;
    private DateTime $endDate;
    private ?DateTime $returnDate;
    private int $clientId;
    private ?string $notes;
    private int $quantity;
    private bool $state;

    public function __construct(
        ?int $id,
        DateTime $startDate,
        DateTime $endDate,
        ?DateTime $returnDate,
        int $clientId,
        ?string $notes,
        int $quantity,
        bool $state
    ) {
        $this->id = $id;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->returnDate = $returnDate;
        $this->clientId = $clientId;
        $this->notes = $notes;
        $this->quantity = $quantity;
        $this->state = $state;
    }

    public function getClientId(): int {
        return $this->clientId;
    }

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getStartDate(): DateTime {
        return $this->startDate;
    }

    public function setStartDate(DateTime $startDate): void {
        $this->startDate = $startDate;
    }

    public function getEndDate(): DateTime {
        return $this->endDate;
    }

    public function setEndDate(DateTime $endDate): void {
        $this->endDate = $endDate;
    }

    public function getReturnDate(): ?DateTime {
        return $this->returnDate;
    }

    public function setReturnDate(?DateTime $returnDate): void {
        $this->returnDate = $returnDate;
    }

    public function getNotes(): ?string {
        return $this->notes;
    }

    public function setNotes(?string $notes): void {
        $this->notes = $notes;
    }

    public function getQuantity(): int {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void {
        $this->quantity = $quantity;
    }

    public function getState(): bool {
        return $this->state;
    }

    public function setState(bool $state): void {
        $this->state = $state;
    }
}