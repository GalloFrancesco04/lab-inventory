<?php

class Maintenance {
    private ?int $id;
    private int $objectId;
    private string $description;
    private DateTime $startDate;
    private ?DateTime $endDate;
    private string $type;
    private int $technicianId;
    private ?string $phoneNumber;
    private float $cost;

    public function __construct(
        int $id,
        int $objectId,
        string $description,
        DateTime $startDate,
        ?DateTime $endDate,
        string $type,
        int $technicianId,
        ?string $phoneNumber,
        float $cost
    ) {
        $this->id = $id;
        $this->objectId = $objectId;
        $this->description = $description;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->type = $type;
        $this->technicianId = $technicianId;
        $this->phoneNumber = $phoneNumber;
        $this->cost = $cost;
    }


    public function getObjectId(): int {
        return $this->objectId;
    }

    public function getTechnicianId(): int {
        return $this->technicianId;
    }


    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

    public function getStartDate(): DateTime {
        return $this->startDate;
    }

    public function setStartDate(DateTime $startDate): void {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?DateTime {
        return $this->endDate;
    }

    public function setEndDate(?DateTime $endDate): void {
        $this->endDate = $endDate;
    }

    public function getType(): string {
        return $this->type;
    }

    public function setType(string $type): void {
        $this->type = $type;
    }

    public function getPhoneNumber(): ?string {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): void {
        $this->phoneNumber = $phoneNumber;
    }

    public function getCost(): float {
        return $this->cost;
    }

    public function setCost(float $cost): void {
        $this->cost = $cost;
    }
}