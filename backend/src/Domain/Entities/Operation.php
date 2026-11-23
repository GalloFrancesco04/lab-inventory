<?php

class Operation {
    private ?int $id;
    private int $maintenance_id;
    private string $description;
    private DateTime $start_date;
    private ?DateTime $end_date;
    private float $cost;

    public function __construct(
        ?int $id,
        int $maintenance_id,
        DateTime $start_date,
        ?DateTime $end_date,
        string $description,
        float $cost
    ) {
        $this->id = $id;
        $this->maintenance_id = $maintenance_id;
        $this->description = $description;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->cost = $cost;
    }

    // --- GETTERS ---

    public function getId(): ?int {
        return $this->id;
    }

    public function getMaintenanceId(): int {
        return $this->maintenance_id;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getStartDate(): DateTime {
        return $this->start_date;
    }

    public function getEndDate(): ?DateTime {
        return $this->end_date;
    }

    public function getCost(): float {
        return $this->cost;
    }

    // --- SETTERS ---

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function setMaintenanceId(int $maintenance_id): void {
        $this->maintenance_id = $maintenance_id;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

    public function setStartDate(DateTime $start_date): void {
        $this->start_date = $start_date;
    }

    public function setEndDate(?DateTime $end_date): void {
        $this->end_date = $end_date;
    }

    public function setCost(float $cost): void {
        $this->cost = $cost;
    }
}