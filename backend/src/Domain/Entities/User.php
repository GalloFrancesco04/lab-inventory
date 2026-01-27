<?php

class User {
    private ?int $id;
    private string $email;
    private string $pass; // 'pass' nel database, ma 'pass' è più chiaro nel codice
    private int $role;

    public function __construct(?int $id, string $email, string $pass, int $role) {
        $this->id = $id;
        $this->email = $email;
        $this->pass = $pass;
        $this->role = $role;
    }

    // --- GETTER ---

    public function getId(): ?int {
        return $this->id;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getPass(): string {
        return $this->pass;
    }

    public function getRole(): int {
        return $this->role;
    }


    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function setPass(string $pass): void {
        $this->pass = $pass;
    }

    public function setRole(int $role): void {
        $this->role = $role;
    }
}