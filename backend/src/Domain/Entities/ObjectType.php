<?php

class ObjectType {
    private string $code;
    private string $name;
    private ?string $description;
    private ?string $brand;
    private ?string $model;
    private int $quantity;

    public function __construct(
        string $code,
        string $name,
        int $quantity = 0,
        ?string $description = null,
        ?string $brand = null,
        ?string $model = null
    ) {
        $this->code = $code;
        $this->name = $name;
        $this->quantity = $quantity;
        $this->description = $description;
        $this->brand = $brand;
        $this->model = $model;
    }

    public function getCode(): string {
        return $this->code;
    }

    public function setCode(string $code): void {
        $this->code = $code;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function getBrand(): ?string {
        return $this->brand;
    }

    public function setBrand(?string $brand): void {
        $this->brand = $brand;
    }

    public function getModel(): ?string {
        return $this->model;
    }

    public function setModel(?string $model): void {
        $this->model = $model;
    }

    public function getQuantity(): int {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void {
        $this->quantity = $quantity;
    }

    public function addQuantity(int $amount): void {
        $this->quantity += $amount;
    }

    public function removeQuantity(int $amount): void {
        if ($this->quantity >= $amount) {
            $this->quantity -= $amount;
        }
    }
}